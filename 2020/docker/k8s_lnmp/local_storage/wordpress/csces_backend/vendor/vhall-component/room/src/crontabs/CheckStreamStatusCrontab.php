<?php

namespace vhallComponent\room\crontabs;

use vhallComponent\decouple\crontabs\BaseCrontab;

/**
 * 检查当前正在直播中的房间流状态是否正常，
 * 如果不正常，则自动关闭房间的直播状态
 * 关闭直播失败的兜底机制，关闭直播依赖于 paas 的回调接口
 * 回调接口异常，会导出关闭不了直播
 *
 * Class CheckStreamStatusCrontab
 * @package vhallComponent\room\crontabs
 */
class CheckStreamStatusCrontab extends BaseCrontab
{
    public $name = 'crontab:check-stream-status';

    public $description = '检查房间流状态';

    public $cron = '*/10 * * * *';

    public function handle()
    {
        vss_logger()->info('csces-checkstreamstatus', ['action'=>'handle-vhall', 'date'=>date('Y-m-d H:i:s')]); //日志
        return;
        set_time_limit(0);
        $this->infoLog('start execution');

        $ilId = 0;
        while (true) {
            $list = vss_service()->getRoomService()->getPushStreamList($ilId);
            if (!$list || !is_array($list)) {
                break;
            }

            $ilId         = end($list)['il_id'];
            $roomIds      = implode(',', array_column($list, 'room_id'));
            $streamStatus = vss_service()->getPaasService()->getStreamStatus($roomIds);
            if (is_array($streamStatus)) {
                foreach ($streamStatus as $v) {
                    vss_service()->getRoomService()->syncStreamStatus($v);
                }
            }
        }

        $this->infoLog('success');
    }
}
