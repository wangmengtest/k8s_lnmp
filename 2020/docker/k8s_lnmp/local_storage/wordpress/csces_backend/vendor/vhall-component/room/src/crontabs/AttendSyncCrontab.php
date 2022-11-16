<?php

namespace vhallComponent\room\crontabs;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use vhallComponent\decouple\crontabs\BaseCrontab;
use Vss\Exceptions\PaasException;

/**
 * 直播和互动访问数据同步
 * 直播数据: 观众
 * 互动数据: 主持人和上麦的人
 * Class AttendSyncCrontab
 * @package vhallComponent\room\crontabs
 */
class AttendSyncCrontab extends BaseCrontab
{
    public $name = 'crontab:attend-sync';

    public $description = '房间访问记录数据同步';

    public $cron = '*/10 * * * *';

    protected $beginDate;

    protected $endDate;

    protected $beginDateCacheKey;

    protected $lockKey;

    /**
     * 运行统计的方法列表
     *
     * @var array
     */
    protected $statMethodList = [
        'syncLiveAttends',
        'syncInavAttends',
    ];

    //数据来源类型
    const DATA_TYPE_LIVE = 1; // 直播
    const DATA_TYPE_INAV = 2; // 互动

    /**
     * @since  2021/6/15
     * @author fym
     */
    public function init()
    {
        $this->beginDateCacheKey = "$this->name:beginDate";
        $this->lockKey           = "$this->name:lock";

        $this->beginDate = $this->getBeginDate();
        $this->endDate   = $this->getEndDate();

        $this->infoLog('start execution');

        $this->autoLock($this->lockKey, 600);
    }

    /**
     * @return void
     * @throws Exception
     * @author fym
     * @since  2021/6/15
     */
    public function handle()
    {
        $this->init();

        foreach ($this->statMethodList as $methodName) {
            try {
                $this->infoLog("$methodName start");
                call_user_func([$this, $methodName]);
            } catch (Exception $e) {
                $this->exceptionLog($e, "$methodName exception");
            }
        }

        $this->setBeginDate();
        $this->infoLog('success');
    }

    protected function logPrefix(): string
    {
        $message = sprintf('[%s - %s] ', $this->beginDate, $this->endDate);
        return parent::logPrefix() . $message;
    }

    /**
     * 设置开始时间
     *
     * @param string $relative
     *
     * @return string
     * @throws Exception
     * @author ensong.liu@vhall.com
     * @date   2019-01-25 17:18:49
     * @see    https://github.com/briannesbitt/Carbon
     */
    public function getBeginDate($relative = '-2 day'): string
    {
        $datetime = vss_redis()->get($this->beginDateCacheKey);

        return (!empty($datetime) && strtotime($datetime) !== false) ? $datetime : Carbon::parse($relative)
            ->toDateTimeString();
    }

    /**
     * 设置下次的开始时间
     *
     * @return mixed
     * @throws Exception
     * @author ensong.liu@vhall.com
     * @date   2019-01-26 19:23:36
     */
    public function setBeginDate()
    {
        // paas 数据生成有延时，为了防止本次时间周期的数据没有拉到
        // 将结束时间向前推 1小时, 防止数据丢失
        $beginDate = date('Y-m-d H:i:s', strtotime($this->endDate) - 3600);
        $res       = vss_redis()->set($this->beginDateCacheKey, $beginDate, 3600 * 24);
        return $res;
    }

    /**
     * 设置结束时间
     *
     * @return string
     * @author ensong.liu@vhall.com
     * @date   2019-01-25 17:20:40
     * @see    https://github.com/briannesbitt/Carbon
     */
    public function getEndDate(): string
    {
        return $this->endDate ?: Carbon::now()->toDateTimeString();
    }

    /**
     * 设置区间段内的房间列表
     *-
     *
     * @return array
     * @author ensong.liu@vhall.com
     * @date   2019-01-25 18:25:12
     */
    public function getRoomsList(): array
    {
        $roomsList = vss_model()->getRoomsModel()->getInteractiveLivesAll($this->beginDate, $this->endDate);

        return empty($roomsList) ? [] : $roomsList;
    }

    /**
     * 房间直播访问记录同步
     * 该数据不包含主持人观看数据
     * @throws BindingResolutionException
     * @since  2021/6/16
     * @author fym
     */
    public function syncLiveAttends()
    {
        $syncCount = [
            'create_count' => 0,
            'update_count' => 0
        ];

        $pos   = 0;
        $limit = 1000;

        while (true) {
            // 1. 从 paas 拉取数据
            $list = vss_service()->getPaasService()->getRoomJoinInfoBatch(
                $this->beginDate,
                $this->endDate,
                $pos,
                $limit
            );

            if (!is_array($list)) {
                $this->errorLog("paas 接口数据异常", $list);
                break;
            }

            foreach ($list as $item) {
                if (!$item['uid']) {
                    continue;
                }

                // 2. 检查房间是否匹配
                $roomInfo = vss_model()->getRoomsModel()->findByRoomId($item['room_id']);
                if (!$roomInfo) {
                    $this->errorLog("房间不存在, 跳过记录；room_id: " . $item['room_id']);
                    continue;
                }

                // 3. 数据检查， 存在则修改，不存在则新增
                $result = vss_model()->getRoomAttendsModel()->syncData($roomInfo, $item, self::DATA_TYPE_LIVE);

                $syncCount['create_count'] += $result['create_count'];
                $syncCount['update_count'] += $result['update_count'];
            }

            if (count($list) < $limit) {
                break;
            }

            $pos += $limit;
        }

        $this->infoLog(__FUNCTION__ . ' end', $syncCount);
    }

    /**
     * 同步增量互动房间访问记录
     * 上麦的用户数据才是互动记录, 默认情况下只有主持人
     * @throws PaasException
     * @throws BindingResolutionException
     * @author fym
     * @since  2021/6/16
     */
    public function syncInavAttends()
    {
        $syncCount = [
            'create_count' => 0,
            'update_count' => 0
        ];

        $pos   = 0;
        $limit = 1000;

        while (true) {
            // 1. 去 paas 拉取数据
            $list = vss_service()->getPaasService()->getInavAccessDataBatch(
                $this->beginDate,
                $this->endDate,
                $pos,
                $limit
            );

            if (!$list) {
                break;
            }

            $pos += $limit;

            foreach ($list as $item) {
                if (!$item['uid']) {
                    continue;
                }

                // 2. 检查互动房间是否存在
                $roomInfo = vss_model()->getRoomsModel()->findByInavId($item['inav_id']);

                if (!$roomInfo) {
                    $this->errorLog("互动房间不存在，跳过记录；inav_id: " . $item['inav_id']);
                    continue;
                }

                // 3. 数据检查， 存在则修改，不存在则新增
                $result = vss_model()->getRoomAttendsModel()->syncData($roomInfo, $item, self::DATA_TYPE_INAV);

                $syncCount['create_count'] += $result['create_count'];
                $syncCount['update_count'] += $result['update_count'];
            }
        }

        $this->infoLog(__FUNCTION__ . ' end', $syncCount);
    }
}
