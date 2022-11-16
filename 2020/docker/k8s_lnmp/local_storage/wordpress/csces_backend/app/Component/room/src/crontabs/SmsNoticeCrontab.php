<?php

namespace App\Component\room\src\crontabs;

use App\Component\room\src\constants\RoomConstant;
use vhallComponent\decouple\crontabs\BaseCrontab;

/**
 * 通知嘉宾 助理
 * Class StatSyncCrontab
 * @package App\Component\room\src\crontabs
 */
class SmsNoticeCrontab extends BaseCrontab
{
    public $name = 'crontab:room-sms-notice';

    public $description = '默认-邀约通知嘉宾和助理';

    public $cron = '* * * * *';

    public function handle()
    {
        if(vss_redis()->exists(RoomConstant::ROOMS_SMS_NOTICE_DEFAULT . date('Y-m-d', strtotime('-1 day')))){
            vss_redis()->del(RoomConstant::ROOMS_SMS_NOTICE_DEFAULT . date('Y-m-d', strtotime('-1 day')));
        }
        $time = time();
        $isWhile = true;
        $firstRun = true;
        while ($isWhile){
            //提前10分钟
            $starTime = time() + 600;
            $reduce = 10;
            if($firstRun){
                $reduce = 10;
            }
            $condition = ['begin_times' => [$starTime - $reduce, $starTime + 60]];
            $lives = vss_service()->getRoomService()->getListByFilter($condition, 1, PHP_INT_MAX)->toArray();

            vss_logger()->info('csces-room-sms-notice', ['condition'=>$condition, 'result' => $lives['data']]); //日志
            if(empty($lives['data'])){
                break;
            }else{
                foreach ($lives['data'] as $live){
                    $addRes = vss_redis()->sadd(RoomConstant::ROOMS_SMS_NOTICE_DEFAULT . date('Y-m-d'), intval($live['il_id']));
                    vss_logger()->info('csces-room-sms-notice', ['action'=>'redis-sadd', 'result' => $addRes, 'members'=>vss_redis()->SMEMBERS(RoomConstant::ROOMS_SMS_NOTICE_DEFAULT . date('Y-m-d'))]); //日志
                    if($addRes){
                        //短信通知
                        vss_service()->getRoomNoticeService()->smsNoticeByIlId($live['il_id']);
                    }
                }
            }
            break;
            $firstRun = false;
            $isWhile = (time() - $time >= 300) ? false : true;
        }

        $this->infoLog('success');
    }
}
