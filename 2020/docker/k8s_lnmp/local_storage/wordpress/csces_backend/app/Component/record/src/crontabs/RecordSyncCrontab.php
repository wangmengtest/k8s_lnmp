<?php

namespace App\Component\record\src\crontabs;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use vhallComponent\decouple\crontabs\BaseCrontab;

/**
 * 回放数据同步
 * Class StatCrontab
 * @package App\Component\record\src\crontabs
 */
class RecordSyncCrontab extends BaseCrontab
{
    public $name = 'crontab:record-sync';

    public $description = '回放数据统计';

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
        'syncRecords',
        'syncRecordAttends',
    ];

    public function init()
    {
        $this->beginDateCacheKey = "$this->name:beginDate";
        $this->lockKey           = "$this->name:lock";

        $this->beginDate = $this->getBeginDate();
        $this->endDate   = $this->getEndDate();

        $this->infoLog("start execution");

        $this->autoLock($this->lockKey, 600);
    }

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
     * 获取开始时间
     *
     * @param string $relative
     *
     * @return string
     * @author fym
     * @since  2021/6/17
     */
    public function getBeginDate(string $relative = '-2 day'): string
    {
        $datetime = vss_redis()->get($this->beginDateCacheKey);

        return (!empty($datetime) && strtotime($datetime) !== false) ? $datetime : Carbon::parse($relative)
            ->toDateTimeString();
    }

    /**
     * 设置下次的开始时间
     * @return mixed
     * @since  2021/6/17
     * @author fym
     */
    public function setBeginDate()
    {
        // paas 数据生成有延时，为了防止本次时间周期的数据没有拉到
        // 将结束时间向前推一小时，下次拉取的时候会多拉一小时的数据
        $beginDate = date('Y-m-d H:i:s', strtotime($this->endDate) - 3600);
        return vss_redis()->set($this->beginDateCacheKey, $beginDate, 3600 * 24);
    }

    /**
     * 获取结束时间
     * @return string
     * @since  2021/6/17
     * @author fym
     */
    public function getEndDate(): string
    {
        return $this->endDate ?: Carbon::now()->toDateTimeString();
    }

    /**
     * 回放记录同步
     * @throws BindingResolutionException
     * @since  2021/6/15
     * @author fym
     */
    public function syncRecords()
    {
        // 记录每个房间的同步条数
        $syncCount = 0;

        $params['page_num']  = 0;
        $params['page_size'] = 100; // 接口限制最大不能超过 100
        $params['starttime'] = $this->beginDate;
        $params['endtime']   = $this->endDate;

        while (true) {
            // 1. 分页查询所有点播记录
            $result = vss_service()->getPaasService()->getRecordList($params);
            $list   = $result['list'] ?? [];
            if (!$list) {
                break;
            }

            // 2. 检查数据库中是否存在，如果不存在则插入
            foreach ($list as $item) {
                if (vss_model()->getRecordModel()->syncData($item)) {
                    $syncCount++;
                }
            }

            if ($result['page_num'] >= $result['page_total']) {
                break;
            }

            $params['page_num']++;
        }

        $this->infoLog(__FUNCTION__ . ' end', [
            'sync_count' => $syncCount
        ]);
    }

    /**
     * 同步点播访问数据，在同步点播记录之后调用
     * @throws BindingResolutionException
     * @since  2021/6/15
     * @author fym
     */
    public function syncRecordAttends()
    {
        // 记录同步数据
        $syncCount = 0;

        $pos       = 0;
        $limit     = 1000;
        $startTime = $this->beginDate;
        $endTime   = $this->endDate;

        while (true) {
            // 1. 去 paas 查询点播访问记录
            $list = vss_service()->getPaasService()->getRecordJonInfoBatch(
                $startTime,
                $endTime,
                $pos,
                $limit
            );

            // 2. 将数据同步到数据库
            foreach ($list as $item) {
                if (!$item['uid']) {
                    continue;
                }

                // 2.1 检查回放记录是否存在
                $recordInfo = vss_model()->getRecordModel()->getInfoByVodId($item['record_id']);
                if (!$recordInfo) {
                    $this->errorLog("Record 记录不存在, vod_id: " . $item['record_id']);
                    continue;
                }

                // 2.2 将回放访问数据同步到数据中
                $result = vss_model()->getRecordAttendsModel()->syncData($item, $recordInfo['il_id'], $recordInfo['account_id']);
                vss_model()->getRoomAttendsAllModel()->syncRecordData($item, $recordInfo['il_id'], $recordInfo['account_id']);
                if (!$result) {
                    $this->errorLog("记录同步失败, vod_id: " . $item['record_id']);
                    continue;
                }

                // 2.3 记录同步结果
                $syncCount++;
            }

            // 3. 检查是否跳出循环
            if (count($list) < $limit) {
                break;
            }
            $pos += $limit;
        }

        $this->infoLog(__FUNCTION__ . ' end', [
            'sync_count' => $syncCount
        ]);
    }
}
