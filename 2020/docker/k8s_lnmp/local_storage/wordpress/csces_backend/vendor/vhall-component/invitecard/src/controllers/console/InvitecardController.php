<?php
/**
 * Created by PhpStorm.
 * User: liuxiangliang
 * Date: 2020/11/19
 * Time: 13:58
 */
namespace vhallComponent\invitecard\controllers\console;

use vhallComponent\decouple\controllers\BaseController;

class InvitecardController extends BaseController
{
    /**
     * 设置邀请卡
     */
    public function setInviteCardAction()
    {
        $roomId = $this->getPost('room_id');
        $inviteCard = vss_service()->getInviteCardService()->info($roomId);
        if (empty($inviteCard)) {
            $roomInfo = vss_service()->getRoomService()->getRoomInfoByRoomId($roomId);
            $createArr = [
                'room_id' => $roomId,
                'title' => $roomInfo['subject'],
                'company' => '',
                'date' => $roomInfo['start_time'],
                'desciption' => $roomInfo['subject'],
                'show_type' => 1,
                'img_type' => 1,
                'is_show_watermark' => 0,
            ];
            $inviteCard =  vss_service()->getInviteCardService()->create($createArr);
        }
        $host = vss_service()->getRoomService()->getHostUserInfoByRoomId($roomId);
        $data = [
            'status' => vss_service()->getInviteCardService()->getStatus($roomId),
            'info' => $inviteCard,
            'nickname' => empty($host->nickname) ? '主办方' : $host->nickname
        ];
        $this->success($data);
    }

    /**
     * 修改邀请卡
     */
    public function updateInviteCardAction()
    {
        $this->success(vss_service()->getInviteCardService()->update($this->getParam()));
    }

    /**
     * 邀请卡开关
     */
    public function switchInviteCardAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'room_id' => 'required',
            'status' => 'required'
        ]);
        $this->success(vss_service()->getInviteCardService()->setInviteCard($params['room_id'], $params['status']));
    }

    /**
     * 图片上传
     * @return mixed
     */
    public function UploadAction()
    {
        $res = vss_service()->getInviteCardService()->upload($this->getParam());
        $this->success($res);
    }
}
