<?php

namespace vhallComponent\record\crontabs;

/**
 * 回放统计数据同步
 * Class RecordStatCrontab
 * @package record\src\crontabs
 */
class RecordStatCrontab extends RecordSyncCrontab
{
    public $name = 'crontab:record-stat';

    public $description = '回放统计数据同步';

    // 每小时的第 25 分钟执行
    public $cron = '25 * * * *';

    /**
     * 运行统计的方法列表
     *
     * @var array
     */
    protected $statMethodList = [
        'syncRecordStats',
    ];

    /**
     * @author fym
     * @since  2021/6/16
     */
    protected function syncRecordStats()
    {
        $pos       = 0;
        $limit     = 1000;
        $syncCount = 0; // 记录同步数据的条数

        while (true) {
            // 1. 去 paas 拉取数据
            $list = vss_service()->getPaasService()->getRecordUserInfoBatch(
                $this->beginDate,
                $this->endDate,
                $pos,
                $limit
            );

            foreach ($list as $item) {
                // 2. 检查本地回放记录是否存在
                $recordInfo = vss_model()->getRecordModel()->getInfoByVodId($item['record_id']);
                if (!$recordInfo) {
                    $this->errorLog("回放 ID 不存在，跳过该条记录, vod_id: " . $item['record_id']);
                    continue;
                }

                // 3. 检查数据是否存在 record_stats 表中
                $result = vss_model()->getRecordStatsModel()
                    ->syncData($item, $recordInfo['il_id'], $recordInfo['account_id']);

                if (!$result) {
                    $this->errorLog('数据同步失败, vod_id:' . $item['record_id']);
                    continue;
                }

                // 3.1 记录同步的数据量
                $syncCount++;
            }

            if (count($list) < $limit) {
                break;
            }
            $pos += $limit;
        }

        $this->infoLog(__FUNCTION__ . ' end', [
            'sync_count' => $syncCount
        ]);
    }


    public function getBeginDate($relative = '-2 day'): string
    {
        $datetime = parent::getBeginDate($relative);
        return date('Y-m-d H:00:00', strtotime($datetime));
    }
}
