<?php

namespace App\Component\room\src\services\notify;
use App\Component\room\src\constants\RoomInvitedConstant;
use App\Component\room\src\constants\RoomJoinRoleNameConstant;
use Sms\Sms;
use Vss\Common\Services\WebBaseService;
/**
 * RoomNoticeService
 */
class RoomNoticeService extends WebBaseService
{
    /*
     * 短信通知
     * */
    public function smsNoticeByIlId($ilId){
        $roomInfo = vss_model()->getRoomsModel()->getInfoByIlId($ilId);
        if(empty($roomInfo)){
            return;
        }
        $noticeUsers = vss_service()->getRoomInvitedService()->getNoticeUserInfo($ilId);
        $sms = new Sms();
        foreach ($noticeUsers as $user){
            if(env('APP_ENV') =='local' && !in_array($user['accounts']['phone'], RoomInvitedConstant::RECEIVE_SMS_PHONES)){
                continue;
            }
            $orgNames = vss_service()->getAccountOrgService()->orgNameByOrgId();
            $orgNames = $orgNames[$roomInfo['org']] ?? '';
            $smsBody = "由{$orgNames} {$roomInfo['account_name']} 发起的《{$roomInfo['subject']}》会议在{$roomInfo['start_time']}时即将开始，期待您的参与。";
            if($user['role_name'] == RoomJoinRoleNameConstant::GUEST){
                vss_logger()->info('csces-roomsmsnotice', ['action'=>'smsNotice', 'result' => ['phone'=>$user['accounts']['phone'], 'start_time'=>date('Y-m-d H:i:s')]]);//日志
                //嘉宾
                $result = $sms->sendInviteSms($user['accounts']['phone'], $smsBody);
                vss_logger()->info('csces-roomsmsnotice', ['action'=>'smsNotice', 'result' => ['result'=>$result, 'end_time'=>date('Y-m-d H:i:s')]]);//日志
            }

            if($user['role_name'] == RoomJoinRoleNameConstant::ASSISTANT){
                vss_logger()->info('csces-roomsmsnotice', ['action'=>'smsNotice', 'result' => ['phone'=>$user['accounts']['phone'], 'start_time'=>date('Y-m-d H:i:s')]]);//日志
                //助理
                $result = $sms->sendInviteSms($user['accounts']['phone'], $smsBody);
                vss_logger()->info('csces-roomsmsnotice', ['action'=>'smsNotice', 'result' => ['result'=>$result, 'end_time'=>date('Y-m-d H:i:s')]]);//日志
            }
        }
    }
}
