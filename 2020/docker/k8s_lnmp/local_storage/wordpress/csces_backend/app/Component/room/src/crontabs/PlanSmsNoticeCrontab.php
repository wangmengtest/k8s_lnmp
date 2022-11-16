<?php

namespace App\Component\room\src\crontabs;

use App\Component\room\src\constants\RoomConstant;
use vhallComponent\decouple\crontabs\BaseCrontab;

/**
 * 计划时间内 通知嘉宾 助理
 * Class StatSyncCrontab
 * @package App\Component\room\src\crontabs
 */
class PlanSmsNoticeCrontab extends BaseCrontab
{
    public $name = 'crontab:room-plan-sms-notice';

    public $description = '计划时间内-邀约通知嘉宾和助理';

    public $cron = '* * * * *';

    public function handle()
    {
        if(vss_redis()->exists(RoomConstant::ROOMS_PLAN_SMS_NOTICE_DEFAULT . date('Y-m-d', strtotime('-1 day')))){
            vss_redis()->del(RoomConstant::ROOMS_PLAN_SMS_NOTICE_DEFAULT . date('Y-m-d', strtotime('-1 day')));
        }
        $starTime = time();
        $reduce = 10;
        $starDate = date('Y-m-d H:i:s', $starTime - $reduce);
        $endDate = date('Y-m-d H:i:s', $starTime + 60);
        $condition = ['notice_times' => [$starDate, $endDate]];
        $lives = vss_service()->getRoomService()->getListByFilter($condition, 1, PHP_INT_MAX)->toArray();

        vss_logger()->info('csces-planroomsmsnotice', ['condition'=>$condition, 'result' => $lives['data']]); //日志
        if(empty($lives['data'])){
            exit();
        }else{
            foreach ($lives['data'] as $live){
                $addRes = vss_redis()->sadd(RoomConstant::ROOMS_PLAN_SMS_NOTICE_DEFAULT . date('Y-m-d'), intval($live['il_id']));
                if($addRes){
                    //短信通知
                    vss_service()->getRoomNoticeService()->smsNoticeByIlId($live['il_id']);
                }
            }
        }

        $this->infoLog('success');
    }
}
