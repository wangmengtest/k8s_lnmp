<?php
/**
 * Created by PhpStorm.
 * User: liuxiangliang
 * Date: 2020/11/19
 * Time: 13:58
 */
namespace vhallComponent\invitecard\controllers\v2;

use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;

class InvitecardController extends BaseController
{
    public function getInviteCardAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'room_id' => 'required',
            'invite_id' => '',
        ]);
        $roomId = $params['room_id'];
        $status = vss_service()->getInviteCardService()->getStatus($roomId);
        if (empty($status)) {
            $this->fail(ResponseCode::COMP_INVITE_CARD_DISABLE);
        }
        $inviteCard = vss_service()->getInviteCardService()->info($roomId);
        $joinUser = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId($params['invite_id'] ?? 0, $roomId);
        $data = [
            'info' => $inviteCard,
            'nickname' => empty($joinUser->nickname) ? '主办方' : $joinUser->nickname
        ];
        $this->success($data);
    }

    public function joinInviteAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'room_id' => 'required',
            'invite_id' => 'required',
            'be_invite_id' => ''
        ]);
        $this->success(vss_service()->getInviteCardService()->invite($params['room_id'] ?? 0, $params['invite_id'] ?? 0, $params['be_invite_id'] ?? 0));
    }
}
