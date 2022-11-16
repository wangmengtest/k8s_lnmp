<?php

namespace App\Component\room\src\crontabs;

use App\Component\room\src\constants\RoomConstant;
use vhallComponent\decouple\crontabs\BaseCrontab;

/**
 * 检查当前正在直播中的房间流状态是否正常，
 * 如果不正常，则自动关闭房间的直播状态
 * 关闭直播失败的兜底机制，关闭直播依赖于 paas 的回调接口
 * 回调接口异常，会导出关闭不了直播
 *
 * Class CheckStreamStatusCrontab
 * @package App\Component\room\src\crontabs
 */
class CheckStreamStatusCrontab extends BaseCrontab
{
    public $name = 'crontab:check-stream-status';

    public $description = '检查房间流状态';

    public $cron = '35 1 * * *';//凌晨1点35分时候执行

    public function handle()
    {
        vss_logger()->info('csces-checkstreamstatus', ['action'=>'handle', 'date'=>date('Y-m-d H:i:s')]); //日志
        $cacheKey = RoomConstant::ROOMS_CHECKSTREAMSTATUS_COUNT_CACHE . date('Y-m-d');
        if(!vss_redis()->exists($cacheKey)){
            $expire = 24 * 3600;
            vss_logger()->info('csces-checkstreamstatus', ['action'=>'handle-set-cache', 'date'=>date('Y-m-d H:i:s')]); //日志
            vss_redis()->set($cacheKey, 1, $expire);
        }
        $handleCount =  vss_redis()->get($cacheKey);
        vss_logger()->info('csces-checkstreamstatus', ['action'=>'handle-get-cache', 'result'=>$handleCount, 'date'=>date('Y-m-d H:i:s')]); //日志
        if($handleCount > 2){
            return;
        }
        if(date('H') != '01'){
            return;
        }
        vss_logger()->info('csces-checkstreamstatus', ['action'=>'handle-start', 'date'=>date('Y-m-d H:i:s')]); //日志
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

        vss_redis()->incr($cacheKey);
        $this->infoLog('success');
    }
}
