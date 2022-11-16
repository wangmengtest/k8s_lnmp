<?php

namespace App\Component\room\src\crontabs;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * 房间统计数据同步
 * 直播统计数据
 * 互动统计数据
 * Class StatSyncCrontab
 * @package App\Component\room\src\crontabs
 */
class StatSyncCrontab extends AttendSyncCrontab
{
    public $name = 'crontab:stat-sync';

    public $description = '房间统计数据同步';

    /**
     * 每小时的第 25 分钟执行
     * paas 每小时汇总一次，每次数据延时1小时。
     * 这两个接口依赖 AttendSync 的统计结果
     * @var string
     */
    public $cron = '25 * * * *';

    /**
     * 运行统计的方法列表
     *
     * @var array
     */
    protected $statMethodList = [
        'syncLiveStats',
        'syncInavStats',
    ];

    public function handle()
    {
        $this->init();

        $roomList = $this->getRoomsList();

        foreach ($this->statMethodList as $methodName) {
            $this->infoLog("$methodName start");
            foreach ($roomList as $room) {
                try {
                    call_user_func([$this, $methodName], $room);
                } catch (Exception $e) {
                    $this->exceptionLog($e, "$methodName exception");
                }
            }
            $this->infoLog("$methodName end");
        }

        $this->setBeginDate();
        $this->infoLog('success');
    }

    /**
     * 同步指定互动房间的统计数据信息
     * 互动放间pv|uv|流量|带宽|时长等统计
     *
     * @param array $room
     *
     * @throws BindingResolutionException
     * @author fym
     * @since  2021/6/16
     */
    public function syncInavStats($room)
    {
        if (!$room['inav_id']) {
            return;
        }

        $syncCount = [
            'il_id'        => $room['il_id'],
            'inav_id'      => $room['inav_id'],
            'create_count' => 0,
            'update_count' => 0
        ];

        $pos   = 0;
        $limit = 1000;
        while (true) {
            // 1. 去 paas 拉取数据
            $list = vss_service()->getPaasService()->getInavRoomData(
                $room['inav_id'],
                $this->beginDate,
                $this->endDate,
                $pos,
                $limit
            );

            foreach ($list as $item) {
                // 2. 检查数据是否存在
                $inavStatId = vss_model()->getInavStatsModel()->newQuery()
                    ->where('account_id', $room['account_id'])
                    ->where('il_id', $room['il_id'])
                    ->where('created_time', $item['created_time'])
                    ->value('id');

                // 3. 存在，则修改，不存在，则新增
                if ($inavStatId) {
                    vss_model()->getInavStatsModel()->where('id', $inavStatId)->update([
                        'flow'       => $item['flow'],
                        'pv_num'     => $item['pv_num'],
                        'uv_num'     => $item['uv_num'],
                        'duration'   => $item['tt'],
                    ]);

                    $syncCount['update_count']++;
                } else {
                    vss_model()->getInavStatsModel()->createInavStats(
                        $room['il_id'],
                        $room['account_id'],
                        $item
                    );

                    $syncCount['create_count']++;
                }
            }

            if (count($list) < $limit) {
                break;
            }

            $pos += $limit;
        }

        $this->infoLog(__FUNCTION__ . ' sync count:', $syncCount);
    }

    /**
     * 同步指定直播房间的统计数据信息
     * 直播间pv|uv|流量|带宽|时长等统计
     *
     * @param array $room
     *
     * @throws BindingResolutionException
     * @author fym
     * @since  2021/6/16
     */
    public function syncLiveStats($room)
    {
        $syncCount = [
            'il_id'        => $room['il_id'],
            'create_count' => 0,
            'update_count' => 0
        ];

        $pos   = 0;
        $limit = 1000;
        while (true) {
            // 1. 从 paas 查询数据
            $list = vss_service()->getPaasService()->getRoomUseInfo(
                $room['room_id'],
                $this->beginDate,
                $this->endDate,
                $pos,
                $limit
            );

            foreach ($list as $item) {
                // 2. 检查本地是否存在
                $roomStatId = vss_model()->getRoomStatsModel()->newQuery()
                    ->where('account_id', $room['account_id'])
                    ->where('il_id', $room['il_id'])
                    ->where('created_time', $item['created_time'])
                    ->value('id');

                // 3. 存在则修改， 不存在则增加
                if ($roomStatId) {
                    vss_model()->getRoomStatsModel()->where('id', $roomStatId)->update([
                        'flow'       => $item['flow'],
                        'pv_num'     => $item['pv_num'],
                        'uv_num'     => $item['uv_num'],
                        'duration'   => $item['tt'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    $syncCount['update_count']++;
                } else {
                    vss_model()->getRoomStatsModel()->createLiveStats(
                        $room['il_id'],
                        $room['account_id'],
                        $item
                    );

                    $syncCount['create_count']++;
                }
            }

            if (count($list) < $limit) {
                break;
            }
            $pos += $limit;
        }

        $this->infoLog(__FUNCTION__ . ' sync count:', $syncCount);
    }

    public function getBeginDate($relative = '-2 day'): string
    {
        $datetime = parent::getBeginDate($relative);
        return date('Y-m-d H:00:00', strtotime($datetime));
    }
}
