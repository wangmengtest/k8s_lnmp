<?php

namespace vhallComponent\perfctl\services;

use App\Constants\ResponseCode;
use Illuminate\Contracts\Container\BindingResolutionException;
use vhallComponent\perfctl\constants\PerfctlConstants;
use vhallComponent\room\constants\RoomConstant;
use Vss\Common\Services\WebBaseService;
use Vss\Exceptions\JsonResponseException;

class ConnectctlService extends WebBaseService
{
    protected $default_connect_count = 500;

    protected $notify_count = 0;

    public function __construct()
    {
        $this->default_connect_count = vss_config('application.connectCount', $this->default_connect_count);
    }

    /**
     * 判断剩余连接数 超过并发限制用户进入队列
     *
     * @param      $liveInfo
     * @param null $thirdId
     *
     * @return array
     * @throws BindingResolutionException
     * @throws JsonResponseException
     */
    public function connectCtl($liveInfo, $thirdId = null)
    {
        vss_logger()->info('connectCtlliveInfo', [$liveInfo]);
        vss_validator($liveInfo, [
            'account_id' => 'required',
            'il_id'      => 'required',
            'room_id'    => 'required',
            'channel_id' => 'required',
        ]);
        if ($thirdId) {
            vss_validator($liveInfo, [
                'nify_channel' => 'required',
            ]);
        }

        $accountId   = $liveInfo['account_id'];
        $ilId        = $liveInfo['il_id'];
        $channelId   = $liveInfo['channel_id'];
        $nifyChannel = $liveInfo['nify_channel'];

        //是否设置并发限制
        $maxConnectCount        = -1;
        $data['room_max_count'] = $maxConnectCount;
        $isLimit                = $this->isLimit($accountId);
        //未设置并发限制可以通过
        if (!$isLimit) {
            return $data;
        }

        //获取被广播提醒的用户数量 需在连接数计算之前
        $this->notify_count = $this->getNotifyAccountNum($ilId);

        //应用剩余连接数
        $appData        = $this->appRemainCount($ilId);
        $appRemainCount = $appData['remain_count'];
        $appRatio       = $appData['ratio'];
        //账户剩余连接数
        $accountData = $this->accountRemainCount($accountId, $appRemainCount, $ilId);
        vss_logger()->info('perfctl_connect_data', ['app' => $appData, 'account' => $accountData]);
        $accountRemainCount = $accountData['remain_count'];
        $accountRatio       = $accountData['ratio'];

        //保留10秒的广播用户可以通过
        $inTime = $this->inNotifyTime($ilId, $thirdId);

        //账户连接数 或 app连接数到达90%时 请求paas房间连接数进行更新
        if (!$inTime && ($appRatio >= PerfctlConstants::CONNECT_RATIO
                || $accountRatio >= PerfctlConstants::CONNECT_RATIO)) {
            $accountRemainCount = $this->adjustConnectCount($accountRemainCount, $ilId, $channelId);
            $accountRemainCount = $accountRemainCount > 0 ? $accountRemainCount : 0;
            //账户连接数耗尽 推入队列等待
            if ($accountRemainCount === 0 && $thirdId) {
                vss_logger()->info(
                    'perfctl_remain_connect_data2',
                    ['key' => PerfctlConstants::QUEUE_PERFCTLCTL_ILID . $ilId, 'account' => $thirdId]
                );

                vss_redis()->set(PerfctlConstants::CONNECT_COUNT_OF_ACCOUNT_BY_ILID . $ilId, $accountRemainCount);

                $noticeLock = vss_redis()->lock(PerfctlConstants::LOCK_NOTICE_LIMITED . $ilId, 60);
                if (!$noticeLock) {
                    vss_service()->getPaasChannelService()->sendMessageByChannel($channelId, [
                        'type' => 'connect_limited'
                    ]);
                }
                $data                 = [];
                $data['nify_channel'] = $nifyChannel;
                $data['app_id']       = vss_service()->getTokenService()->getAppId();
                $this->success($data, [], ResponseCode::BUSINESS_HOT);
            }
        } elseif ($thirdId) {
            //app连接数 不到90%数据时自增
            vss_redis()->incr(RoomConstant::CONNECT_COUNT_BY_APP);
            //账户连接数 不到90% 时房间连接数自增
            $roomConnectCount = vss_redis()->incr(RoomConstant::CONNECT_COUNT_BY_ROOM . $ilId);
        }

        vss_logger()->info(
            'perfctl_remain_connect_data',
            ['room' => $roomConnectCount, 'account' => $accountRemainCount]
        );

        //账户剩余连接数 房间共享
        $remainCount = $accountRemainCount;
        if ($thirdId && !$inTime) {
            $remainCount = $accountRemainCount - 1;
        }
        vss_redis()->set(PerfctlConstants::CONNECT_COUNT_OF_ACCOUNT_BY_ILID . $ilId, $remainCount);

        vss_logger()->info('perfctl_remain_connect_data1', [
            'room'    => $roomConnectCount,
            'account' => $accountRemainCount,
            'key'     => PerfctlConstants::CONNECT_COUNT_OF_ACCOUNT_BY_ILID . $ilId,
            'value'   => $accountRemainCount - 1
        ]);

        //房间并发连接数
        if (!isset($roomConnectCount)) {
            $roomConnectCount = vss_redis()->get(RoomConstant::CONNECT_COUNT_BY_ROOM . $ilId);
        }

        vss_logger()->info(
            'perfctl_remain_connect_data6',
            ['il_id' => $ilId, 'room' => $remainCount, 'account' => $thirdId]
        );

        //房间当前可达最大连接总数
        $maxConnectCount = $roomConnectCount + $accountRemainCount;
        //广播用户不受限制
        if ($inTime) {
            $maxConnectCount = -1;
        }
        $data['room_max_count'] = $maxConnectCount;
        return $data;
    }

    /**
     * 用户加入排队队列
     *
     * @param $params
     *
     * @return bool
     *
     */
    public function queueAdd($params)
    {
        vss_validator($params, [
            'account_id' => 'required',
            'il_id'      => 'required',
        ]);
        $res = vss_redis()->push(
            PerfctlConstants::QUEUE_PERFCTLCTL_ILID . $params['il_id'],
            $params['account_id']
        );
        if (!$res) {
            vss_logger()->error('perfctl_add_queue_err', ['params' => $params]);
        }
        return true;
    }

    /**
     * 是否设置并发限制
     *
     * @param $accountId
     *
     * @return bool
     *
     */
    public function isLimit($accountId)
    {
        $appMaxcount = $this->default_connect_count;
        //账户当前连接数
        $connectData = $this->getConnectNum(['account_id' => $accountId]);
        $connectNum  = $connectData['connect_num'];
        if ($appMaxcount || $connectNum) {
            return true;
        }
        return false;
    }

    /**
     * 用户是否在广播通知时间窗口内
     *
     * @param $ilId    //房间ID
     * @param $thirdId //用户ID
     *
     * @return bool
     */
    public function inNotifyTime($ilId, $thirdId)
    {
        //用户被广播时间
        $accountListKey = PerfctlConstants::NOTIFY_ACCOUNT_LIST_ILID . $ilId;
        $accountTime    = vss_redis()->zScore($accountListKey, $thirdId);
        //广播时间是否在时间窗口范围内
        if (time() - $accountTime <= PerfctlConstants::NOTIFY_ACCOUNT_EXIST_TIME) {
            return true;
        }
        return false;
    }

    /**
     * 修正该房间并发连接数
     *
     * @param $ilId               //房间ID
     * @param $channelId          //房间ID对应频道ID
     * @param $accountRemainCount //账户剩余并发连接数
     *
     * @return int
     * @throws \Exception
     */
    public function adjustConnectCount($accountRemainCount, $ilId, $channelId)
    {
        //锁状态控制房间并发连接更新频率
        $lock = vss_redis()->lock(PerfctlConstants::LOCK_REQ_CONNECT . $ilId, PerfctlConstants::LOCK_TIME);
        if ($lock) {
            return (int)$accountRemainCount;
        }
        //请求paas房间连接数
        $channelData      = vss_service()->getPaasChannelService()->maxConnectionCount($channelId);
        $roomConnectCount = vss_redis()->get(RoomConstant::CONNECT_COUNT_BY_ROOM . $ilId);
        //房间维持数量比
        $diffCount = $roomConnectCount - $channelData['count'];
        vss_logger()->info('accountRemainCount5', [$ilId, $channelData, $roomConnectCount, $accountRemainCount]);
        //应用和房间连接数进行更新
        if ($diffCount >= 0) {
            $accountRemainCount += $diffCount;
            vss_redis()->decrby(RoomConstant::CONNECT_COUNT_BY_ROOM . $ilId, $diffCount);
            vss_redis()->decrby(RoomConstant::CONNECT_COUNT_BY_APP, $diffCount);
        } else {
            $absDiffCount       = abs($diffCount);
            $accountRemainCount = $accountRemainCount > $absDiffCount ? $accountRemainCount - $absDiffCount : 0;

            vss_redis()->incrby(RoomConstant::CONNECT_COUNT_BY_ROOM . $ilId, $absDiffCount);
            vss_redis()->incrby(RoomConstant::CONNECT_COUNT_BY_APP, $absDiffCount);
        }
        return (int)$accountRemainCount;
    }

    /**
     * 通知排队用户重新进入房间
     *
     * @param      $ilId
     * @param      $roomId
     * @param      $remainCount
     * @param bool $block
     *
     * @return bool
     */
    public function notifyQueueAccount($ilId, $roomId, $remainCount, $block = false)
    {
        if (empty($remainCount)) {
            return true;
        }
        //多场景触发 确保粒度
        $lock = vss_redis()->lock(PerfctlConstants::LOCK_QUEUE_DEAL . $ilId, PerfctlConstants::LOCK_TIME);
        if ($lock) {
            return true;
        }
        $queueKey       = PerfctlConstants::QUEUE_PERFCTLCTL_ILID . $ilId;
        $accountListKey = PerfctlConstants::NOTIFY_ACCOUNT_LIST_ILID . $ilId;
        $popNum         = PerfctlConstants::QUEUE_BATCH_POP_NUM;
        while (true) {
            $popNum = $remainCount > $popNum ? $popNum : $remainCount;
            //取出队列用户
            $accountArr = vss_redis()->batchPop($queueKey, $popNum, $block);
            vss_logger()->info(
                'perfctl_remain_connect_data7',
                ['key' => $queueKey, 'num' => $popNum, 'account' => $accountArr]
            );
            if (empty($accountArr)) {
                break;
            }
            $accountArr  = array_unique($accountArr);
            $count       = count($accountArr);
            $remainCount -= $count;

            //用户加入缓存 有序集合
            $accountArr && vss_redis()->pipeline(function ($pipe) use ($accountArr, $accountListKey) {
                foreach ($accountArr as $accountId) {
                    if (is_numeric($accountId)) {
                        continue;
                    }
                    $pipe->zadd($accountListKey, time(), $accountId);
                }
            });

            //广播消息
            vss_service()->getPaasChannelService()->sendNotifyMessage($roomId, [
                'type'        => 'cancel_queue_up',
                'account_ids' => json_encode($accountArr),
            ]);

            if ($remainCount <= 0) {
                break;
            }
        }
        //移除超时account_id 数据
        $existTime = PerfctlConstants::NOTIFY_ACCOUNT_EXIST_TIME;
        $delTime   = time() - $existTime;
        vss_redis()->zRemRangeByScore($accountListKey, 0, $delTime);
        return true;
    }

    /**
     * 应用剩余连接数
     * @return array
     */
    public function appRemainCount($ilId)
    {
        $appMaxcount = $this->default_connect_count;
        //应用总连接数
        $appConnectCount = vss_redis()->get(RoomConstant::CONNECT_COUNT_BY_APP);
        //获取被广播提醒的用户数量
        $notifyCount = $this->notify_count;
        //应用剩余连接数
        $remainCount = $appMaxcount - $appConnectCount - $notifyCount;
        vss_logger()->info('appRemainCount', [$appMaxcount, $appConnectCount, $notifyCount]);
        //计算应用连接数使用率
        $ratio = round($appConnectCount / $appMaxcount, 2) * 100;

        $data                 = [];
        $data['remain_count'] = $remainCount;
        $data['ratio']        = $ratio;
        return $data;
    }

    /**
     * 账户剩余连接数
     *
     * @param $accountId
     * @param $appRemainCount //app剩余连接数
     *
     * @return array
     */
    public function accountRemainCount($accountId, $appRemainCount, $ilId)
    {
        //剩余连接数 = 设置的账户连接数 - 当前账户连接数
        $connectData = $this->getConnectNum(['account_id' => $accountId]);
        $connectNum  = $connectData['connect_num'];
        vss_logger()->info('accountRemainCount', [$accountId, $connectNum, $appRemainCount]);
        $connectNum = $connectNum > 0 ? $connectNum : $this->default_connect_count;
        //账户当前连接数
        $connectCount = $this->accountConnectCount($accountId, $ilId);
        vss_logger()->info('accountRemainCount1', [$accountId, $connectCount]);

        $remainCount = $connectNum - $connectCount;
        $remainCount = $remainCount > 0 ? $remainCount : 0;
        vss_logger()->info('accountRemainCount2', [$accountId, $remainCount]);

        //获取被广播提醒的用户数量
        $notifyCount = $this->notify_count;
        //更新剩余连接数
        $remainCount = $remainCount > $notifyCount ? $remainCount - $notifyCount : 0;

        //剩余连接数 : 应用剩余连接数 和 账户剩余连接 取少的那个值
        $remainCount = $appRemainCount > $remainCount ? $remainCount : $appRemainCount;
        vss_logger()->info('accountRemainCount3', [$accountId, $remainCount, $appRemainCount]);

        //计算账户连接数使用率
        $ratio = round(($connectNum - $remainCount) / $connectNum, 2) * 100;
        vss_logger()->info('accountRemainCount4', [$accountId, $remainCount, $notifyCount]);
        $data                 = [];
        $data['remain_count'] = $remainCount;
        $data['ratio']        = $ratio;
        return $data;
    }

    /**
     * 账户下当前连接数
     *
     * @param $accountId
     *
     * @return int
     */
    public function accountConnectCount($accountId, $ilId)
    {
        $ilIds = vss_redis()->get(RoomConstant::LIVING_ROOMS_OF_ACCOUNT . $accountId);
        vss_logger()->info('accountConnectCount', [$ilIds]);
        $count = 0;
        if (empty($ilIds)) {
            $ilIds = [$ilId];
        } elseif (!in_array($ilId, $ilIds)) {
            $ilIds[] = $ilId;
        }
        foreach ($ilIds as $ilId) {
            $connectCount = vss_redis()->get(RoomConstant::CONNECT_COUNT_BY_ROOM . $ilId);
            $count        += $connectCount;
        }
        return (int)$count;
    }

    /**
     * 设置账户连接数
     *
     * @param $params
     *
     * @return array
     *
     */
    public function setConnectNum($params)
    {
        $validator   = vss_validator($params, [
            'account_id'  => 'required',
            'connect_num' => '',
        ]);
        $accountId   = $params['account_id'];
        $appMaxcount = $this->default_connect_count;

        //验证并发设定值
        if ($params['connect_num'] > $appMaxcount) {
            $this->fail(ResponseCode::BUSINESS_CONCURRENT_OVERFLOW);
        }
        $data = [
            'connect_num' => $params['connect_num'] ?? 0,
        ];
        $info = vss_model()->getAnchorExtendsModel()->saveByAccountId($accountId, $data);
        if (!$info) {
            $this->fail(ResponseCode::BUSINESS_SET_FAILED);
        }
        return $info;
    }

    /**
     * 获取账户并发连接数设置
     *
     * @param $params
     *
     * @return array
     *
     */
    public function getConnectNum($params)
    {
        vss_validator($params, [
            'account_id' => 'required',
        ]);
        $accountId               = $params['account_id'];
        $info                    = vss_model()->getAnchorExtendsModel()->getInfoByAccountId($accountId);
        $data['connect_num']     = $info['connect_num'] ?? 0;
        $data['app_connect_num'] = $this->default_connect_count;
        return $data;
    }

    /**
     * 获取房间下广播通知用户的数量
     *
     * @param $ilId
     *
     * @return mixed
     */
    public function getNotifyAccountNum($ilId)
    {
        $maxTime     = time();
        $minTime     = time() - PerfctlConstants::NOTIFY_ACCOUNT_EXIST_TIME;
        $notifyCount = vss_redis()->zCount(PerfctlConstants::NOTIFY_ACCOUNT_LIST_ILID . $ilId, $minTime, $maxTime);
        vss_logger()->info('appRemainCountgetNotifyAccountNum', [$notifyCount, $minTime]);
        return $notifyCount;
    }
}
