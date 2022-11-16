<?php

namespace vhallComponent\filterWord\jobs;

use Vss\Queue\JobStrategy;

/**
 * 敏感词消费
 * Class FilterWordJob
 * @package vhallComponent\filterWord\jobs
 */
class FilterWordJob extends JobStrategy
{
    protected $key = "filter_words:report_list";

    public function __construct(array $data)
    {
        vss_redis()->lpush($this->key, json_encode($data));
    }

    /**
     * 敏感词入库
     * @return mixed|void
     * @since  2021/6/28
     * @author fym
     */
    public function handle()
    {
        try {
            $list = $this->batchData();
            if (empty($list)) {
                return;
            }

            vss_logger()->info('敏感词队列消费开始-insert, 任务数: ' . count($list));
            vss_model()->getFilterWordsLogModel()->inserted($list);
        } catch (\Exception $e) {
            vss_logger()->error('敏感词消费错误', [
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
                'data'    => $list,
            ]);
        }
    }

    /**
     * @param int $count
     *
     * @return array
     * @author fym
     * @since  2021/6/28
     */
    protected function batchData(int $count = 200): array
    {
        $list = [];
        $i    = 0;
        while ($i < $count && $item = vss_redis()->rpop($this->key)) {
            $list[] = json_decode($item, true);
            $i++;
        }
        return $list;
    }
}
