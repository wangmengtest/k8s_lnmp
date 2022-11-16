<?php

namespace App\Component\room\src\controllers\api;

use App\Component\account\src\constants\AccountConstant;
use App\Component\room\src\constants\InavConstant;
use App\Component\room\src\constants\RspStructConstant;
use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;
use vhallComponent\watchlimit\constants\WatchlimitConstant;

/**
 * RoomController extends BaseController
 *
 * @uses     yangjin
 * @date     2020-08-10
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class RoomController extends BaseController
{
    /**
     * 观看端-房间列表
     */
    public function watchListAction()
    {
        $liveList = vss_service()->getRoomListService()->watchList($this->getParam(), $this->accountInfo);
        $this->success($liveList, RspStructConstant::LIST);
    }

    /**
     * 预告页面 是否能进入观看（1.有无超过500 2.是否被邀请的用户）
     */
    public function checkOnlineCountAction(){
        $roomId = $this->getParam('room_id');
        if($this->accountInfo['user_type'] == AccountConstant::USER_TYPE_CSCES){
            vss_service()->getRoomService()->isWatchLive($roomId, $this->accountInfo);
        }
        $onlineCount = vss_service()->getRoomService()->getOnlineCount($roomId);
        if($onlineCount > InavConstant::MAX_ONLINE_COUNT){
            $this->fail(ResponseCode::CHECK_MAXONLINECOUNT_FAIL);
        }
        $this->success();
    }

    /**
     * 房间-获取记录
     */
    public function getAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'il_id' => 'required',
        ]);

        $params['operateType'] = $params['operateType'] ?? 2;
        $params['account_id']  = $this->accountInfo['account_id'];

        $data = vss_service()->getRoomService()->getByConsole($params);
        $this->success($data);
    }

    public function updateInvitedAudienceAction()
    {
        $params = $this->getParam();
        $rule = [
            'il_id' => 'required',
            'audience_ids' => 'required',
        ];
        $params = vss_validator($params, $rule);

        $roomInfo = vss_model()->getRoomsModel()->getInfoByIlId($params['il_id']);

        if(empty($roomInfo)){
            $this->fail(ResponseCode::EMPTY_ROOM);
        }
        vss_service()->getRoomInvitedService()->createAudienceInvited($roomInfo['il_id'], array_merge(['account_id'=>$this->accountInfo['account_id']], $this->getParam()));
        $this->success();
    }

    public function getRoomInvitedAction(){
        $params = $this->getParam();
        $rule = [
            'il_id' => 'required'
        ];
        $params = vss_validator($params, $rule);

        $roomInvited = vss_service()->getCacheRoomInvitedService()->getInvitedAccountInfoByIlId($params['il_id']);
        $this->success($roomInvited);
    }

    /**
     * 检查用户状态
     */
    public function onlineCheckAction()
    {
        $channelId  = $this->getParam('channel_id');
        $accountIds = $this->getParam('account_ids');
        $ilId = $this->getParam('il_id', 0);
        if (empty($channelId) || empty($accountIds)) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        $roomInfo = vss_model()->getRoomsModel()->getInfoByIlId($ilId);
        if (!$roomInfo) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }
        $accountIds = explode(',', $accountIds);
        $result     = vss_service()->getPaasChannelService()->checkUserOnlineByChannel($channelId, $accountIds);

        if (!empty($result)) {
            $this->success($result);
        }
        $this->fail(ResponseCode::BUSINESS_CHECK_FAILED);
    }
}
