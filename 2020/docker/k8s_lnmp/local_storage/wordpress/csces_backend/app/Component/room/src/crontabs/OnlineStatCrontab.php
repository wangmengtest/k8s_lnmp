<?php

namespace App\Component\room\src\crontabs;

use Exception;
use vhallComponent\decouple\crontabs\BaseCrontab;
use App\Component\room\src\constants\RoomConstant;

/**
 * 获取房间连接数/每分钟
 * 数据统计中的并发趋势图使用
 * Class OnlineStatCrontab
 * @package App\Component\room\src\crontabs
 */
class OnlineStatCrontab extends BaseCrontab
{
    public $name = 'crontab:online-stat';

    public $description = '在线数据统计';

    public $cron = '* * * * *';

    protected $lockKey;

    public function init()
    {
        $this->lockKey = "$this->name:lock";

        $this->infoLog("start execution");

        $this->autoLock($this->lockKey);
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        $this->init();

        $interactiveLiveList = vss_model()->getRoomsModel()->getAllOnlineChannelInfos();
        $appConnectCount     = 0;
        if ($interactiveLiveList) {
            //房间连接数key
            $roomConnectCountKeyPrefix = RoomConstant::CONNECT_COUNT_BY_ROOM;

            $ilIds = array_column($interactiveLiveList, 'il_id');
            $ilIds = implode(',', $ilIds);
            $this->infoLog("sync online room: [$ilIds]");

            $accountRooms = [];
            foreach ($interactiveLiveList as $ilInfo) {
                $count                                 = $this->getTotalCount($ilInfo['channel_id'], $ilInfo);
                $accountRooms[$ilInfo['account_id']][] = $ilInfo['il_id'];
                vss_redis()->set($roomConnectCountKeyPrefix . $ilInfo['il_id'], $count);
                $this->infoLog('online_stat_room_count', ['il_id' => $ilInfo['il_id'], 'count' => $count]);
                $appConnectCount += $count;
            }

            //账户下房间
            $accountConnectCountKeyPrefix = RoomConstant::LIVING_ROOMS_OF_ACCOUNT;
            foreach ($accountRooms as $accountId => $rooms) {
                vss_redis()->set($accountConnectCountKeyPrefix . $accountId, $rooms);
            }
        }
        //应用总连接数
        vss_redis()->set(RoomConstant::CONNECT_COUNT_BY_APP, $appConnectCount);
        $this->infoLog('success');
    }

    /**
     *  获取最大连接数
     *
     * @param $channelId
     * @param $ilInfo
     *
     * @return int
     * @throws Exception
     */
    public function getTotalCount($channelId, $ilInfo): int
    {
        try {
            $streamStatus = vss_service()->getPaasChannelService()->maxConnectionCount($channelId);
            if ($streamStatus['count'] <= 0) {
                return 0;
            }
        } catch (Exception $e) {
            $this->exceptionLog($e);
            return 0;
        }

        //1、获取用户在线连接数
        $roomConnectCountsModel = vss_model()->getRoomConnectCountsModel();
        $roomConnectCountId     = $roomConnectCountsModel::query()
            ->where('il_id', $ilInfo['il_id'])
            ->where('channel', $streamStatus['channel'])
            ->where('create_time', date('Y-m-d H:i'))
            ->value('id');

        // 2. 存在则修改，不存在则新增
        if ($roomConnectCountId) {
            $roomConnectCountsModel->where('id', $roomConnectCountId)->update([
                'count' => $streamStatus['count'],
            ]);
        } else {
            $roomConnectCountsModel->createData([
                'il_id'      => $ilInfo['il_id'],
                'channel'    => $streamStatus['channel'],
                'count'      => $streamStatus['count'],
                'account_id' => $ilInfo['account_id']
            ]);
        }
        return $streamStatus['count'];
    }
}
