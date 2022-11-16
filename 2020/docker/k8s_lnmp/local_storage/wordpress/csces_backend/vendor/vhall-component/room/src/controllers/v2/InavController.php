<?php

namespace vhallComponent\room\controllers\v2;

use App\Constants\ResponseCode;
use Exception;
use vhallComponent\decouple\controllers\BaseController;
use vhallComponent\room\constants\InavGlobalConstant;
use vhallComponent\room\constants\RoomJoinRoleNameConstant;
use vhallComponent\room\models\RoomJoinsModel;

/**
 * InavController extends BaseController
 *
 * @uses     yangjin
 * @date     2020-08-12
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class InavController extends BaseController
{

    #### v2 start

    /**
     * Notes: 设置观看端布局/清晰度
     * Author: michael
     * Date: 2019/9/30
     * Time: 14:21
     *
     * @throws Exception
     */
    public function setStreamAction()
    {
        $params = $this->getParam();

        vss_validator($params, [
            'room_id'    => 'required',
            'layout'     => 'required_without:definition',
            'definition' => 'required_without:layout'
        ]);
        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $join_user->role_name != RoomJoinRoleNameConstant::HOST && $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        if ($params['layout']) {
            vss_service()->getInavService()->setLayout($join_user->room_id, $params['layout']);
        }
        if ($params['definition']) {
            vss_service()->getInavService()->setDefinition($join_user->room_id, $params['definition']);
        }
        $this->success();
    }

    /**
     * Notes: 设置观众申请上麦许可（举手）
     * Author: michael
     * Date: 2019/9/30
     * Time: 14:21
     *
     * @throws Exception
     */
    public function setHandsupAction()
    {
        $params = $this->getParam();

        vss_validator($params, [
            'room_id' => 'required',
            'status'  => 'required'
        ]);
        $this->checkRoomLive($params['room_id']);
        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        if ($join_user->role_name != RoomJoinRoleNameConstant::HOST) {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }
        vss_service()->getInavService()->setHandsup($join_user->room_id, $params['status']);
        $this->success();
    }

    /**
     * Notes:同意用户上麦申请
     * Author: michael
     * Date: 2019/9/30
     * Time: 14:27
     *
     * @throws Exception
     */
    public function agreeApplyAction()
    {
        $params = $this->getParam();

        $validator         = vss_validator($params, [
            'room_id'            => 'required',
            'receive_account_id' => 'required',
        ]);
        $join_user         = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $receive_join_user = $this->getJoinUser(
            $params['receive_account_id'],
            $params['room_id']
        );
        if ($join_user->role_name != RoomJoinRoleNameConstant::HOST) {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }
        vss_service()->getInavService()->replyApply($receive_join_user, 1);
        $this->success();
    }

    /**
     * Notes:拒绝用户上麦申请
     * Author: michael
     * Date: 2019/9/30
     * Time: 14:27
     *
     * @throws Exception
     */
    public function rejectApplyAction()
    {
        $params = $this->getParam();

        $validator         = vss_validator($params, [
            'room_id'            => 'required',
            'receive_account_id' => 'required',
        ]);
        $join_user         = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $receive_join_user = $this->getJoinUser(
            $params['receive_account_id'],
            $params['room_id']
        );
        if ($join_user->role_name != RoomJoinRoleNameConstant::HOST) {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }
        vss_service()->getInavService()->replyApply($receive_join_user, 0);
        $this->success();
    }

    /**
     * Notes:邀请用户上麦
     * Author: michael
     * Date: 2019/9/30
     * Time: 14:27
     *
     * @throws Exception
     */
    public function inviteAction()
    {
        $params = $this->getParam();

        vss_validator($params, [
            'room_id'            => 'required',
            'receive_account_id' => 'required',
        ]);
        $this->checkRoomLive($params['room_id']);
        $join_user         = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $receive_join_user = $this->getJoinUser(
            $params['receive_account_id'],
            $params['room_id']
        );
        if ($join_user->role_name != RoomJoinRoleNameConstant::HOST) {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }
        if (!in_array($receive_join_user->role_name, [
            RoomJoinRoleNameConstant::GUEST,
            RoomJoinRoleNameConstant::ASSISTANT,
            RoomJoinRoleNameConstant::USER
        ])) {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }
        vss_service()->getInavService()->sendInvite($receive_join_user);
        $this->success();
    }

    /**
     * Notes:用户拒绝上麦邀请
     * Author: michael
     * Date: 2019/9/30
     * Time: 14:27
     *
     * @throws Exception
     */
    public function rejectInviteAction()
    {
        $params = $this->getParam();

        vss_validator($params, [
            'room_id'            => 'required',
            'receive_account_id' => '',
        ]);
        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $uids      = vss_service()->getInavService()->getInviteList($join_user->room_id);
        if (empty($uids) || !array_key_exists($join_user->account_id, $uids)) {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }
        vss_service()->getInavService()->replyInvite($join_user, 0);
        $this->success();
    }

    /**
     * Notes:用户同意上麦邀请
     * Author: michael
     * Date: 2019/9/30
     * Time: 14:27
     *
     * @throws Exception
     */
    public function agreeInviteAction()
    {
        $params = $this->getParam();

        vss_validator($params, [
            'room_id'            => 'required',
            'receive_account_id' => 'required',
        ]);
        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $uids      = vss_service()->getInavService()->getInviteList($join_user->room_id);
        if (empty($uids) || !array_key_exists($join_user->account_id, $uids)) {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }
        vss_service()->getInavService()->replyInvite($join_user, 1);
        $this->success();
    }

    /**
     * Notes:用户上麦
     * Author: michael
     * Date: 2019/9/30
     * Time: 14:27
     *
     * @throws Exception
     */
    public function speakAction()
    {
        $params = $this->getParam();

        vss_validator($params, [
            'room_id' => 'required'
        ]);
        $this->checkRoomLive($params['room_id']);
        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        vss_service()->getInavService()->addSpeaker($join_user);

        //上麦时间记录
        $data['room_id']    = $params['room_id'];
        $data['account_id'] = $join_user->account_id;
        vss_service()->getBigDataService()->speakRecords($data);

        $this->success();
    }

    /**
     * @param $room_id
     *
     *
     * @author  jin.yang@vhall.com
     * @date    2020-08-13
     */
    public function checkRoomLive($room_id)
    {
        $room = vss_model()->getRoomsModel()->findByRoomId($room_id);
        if ($room->status != 1) {
            $this->fail(ResponseCode::BUSINESS_NOT_START);
        }
    }

    /**
     * Notes:用户下麦
     * Author: michael
     * Date: 2019/9/30
     * Time: 14:27
     *
     * @throws Exception
     */
    public function nospeakAction()
    {
        $params = $this->getParam();

        $validator         = vss_validator($params, [
            'room_id'            => 'required',
            'receive_account_id' => 'required',
            'reason'             => '',
        ]);
        $join_user         = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $receive_join_user = $this->getJoinUser(
            $params['receive_account_id'],
            $params['room_id']
        );
        if ($join_user->role_name == RoomJoinRoleNameConstant::HOST || $receive_join_user->join_id == $join_user->join_id) {
            vss_service()->getInavService()->notSpeaker($join_user, $receive_join_user, $params['reason']);
        } else {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }
        //下麦上报
        $data['room_id']    = $params['room_id'];
        $data['account_id'] = $join_user->account_id;
        $data['role_name']  = $join_user->role_name;
        $data['vss_token']  = $this->getParam('vss_token');
        vss_service()->getBigDataService()->requestNoSpeakParams($data);

        $this->success();
    }

    /**
     * Notes:用户申请上麦
     * Author: michael
     * Date: 2019/9/30
     * Time: 14:27
     *
     * @throws Exception
     */
    public function applyAction()
    {
        $params = $this->getParam();

        vss_validator($params, [
            'room_id' => 'required',
            'type'    => '',
        ]);
        $this->checkRoomLive($params['room_id']);
        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        if ($join_user->role_name == \vhallComponent\room\constants\RoomJoinRoleNameConstant::USER) {
            if (!vss_service()->getInavService()->getGlobal(
                $join_user->room_id,
                InavGlobalConstant::IS_HANDSUP
            )) {
                $this->fail(ResponseCode::BUSINESS_NOT_SUPPORT_RAISE);
            }
        } elseif (!in_array(
            $join_user->role_name,
            [RoomJoinRoleNameConstant::GUEST, RoomJoinRoleNameConstant::ASSISTANT]
        )) {
            $this->fail(ResponseCode::BUSINESS_ROLE_NOT_MATCH);
        }
        vss_service()->getInavService()->apply($join_user, $params['type'] ?? 1);
        $this->success();
    }

    /**
     * Notes:用户取消上麦申请
     * Author: michael
     * Date: 2019/9/30
     * Time: 14:27
     *
     * @throws Exception
     */
    public function cancelApplyAction()
    {
        $params = $this->getParam();

        vss_validator($params, [
            'room_id' => 'required'
        ]);
        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        vss_service()->getInavService()->apply($join_user, 0);
        $this->success();
    }

    /**
     * 禁言/取消禁言用户
     *
     * @return void
     *
     * @author ensong.liu@vhall.com
     * @date   2019-06-09 14:04:23
     */
    public function setBannedAction()
    {
        $params = $this->getPost();
        vss_validator($this->getPost(), [
            'room_id'            => 'required',
            'receive_account_id' => 'required',
            'status'             => 'required'
        ]);
        $accountIdArr = explode(',', $params['receive_account_id']);
        $res          = vss_service()->getRoomService()->batchBanned(
            $params['room_id'],
            $accountIdArr,
            $params['status']
        );
        if (!$res) {
            $this->fail(ResponseCode::BUSINESS_SWITCH_MUTE_FAILED);
        }
        $bannedNum = vss_service()->getRoomService()->getBannedNum($params['room_id']);
        $this->success($bannedNum);
    }

    /**
     * Notes: 全体禁言
     * Author: michael
     * Date: 2019/10/8
     * Time: 10:41
     *
     */
    public function setAllBannedAction()
    {
        $params = $this->getPost();
        vss_validator($params, [
            'room_id' => 'required',
            'type'    => 'required'
        ]);
        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        if ($join_user->role_name == RoomJoinRoleNameConstant::USER) {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }
        vss_service()->getInavService()->setAllBanned($join_user, $params['type']);
        $this->success();
    }

    /**
     * @param $account_id
     * @param $room_id
     *
     * @return RoomJoinsModel|null
     *
     * @author  jin.yang@vhall.com
     */
    public function getJoinUser($account_id, $room_id)
    {
        $join_user = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId($account_id, $room_id);
        empty($join_user) && $this->fail(ResponseCode::BUSINESS_INVALID_USER);
        return $join_user;
    }

    #####################################################################媒体组件#############################################################################

    /**
     * Notes:设置设备开关状态
     * Author: michael
     * Date: 2019/9/30
     * Time: 14:27
     *
     * @throws Exception
     */
    public function setDeviceStatusAction()
    {
        $params = $this->getParam();

        vss_validator($params, [
            'room_id'            => 'required',
            'receive_account_id' => 'required',
            'device'             => 'required',
            'status'             => 'required'
        ]);
        $this->checkRoomLive($params['room_id']);
        $join_user         = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $receive_join_user = $this->getJoinUser(
            $params['receive_account_id'],
            $params['room_id']
        );
        if ($join_user->role_name == RoomJoinRoleNameConstant::HOST || $receive_join_user->join_id == $join_user->join_id) {
            $device_name = $params['device'] == 1 ? 'audio' : 'video';
            vss_service()->getInavService()->setSpeakerAttr(
                $receive_join_user,
                [$device_name => $params['status']]
            );
        } else {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }
        $this->success();
    }

    /**
     * Notes:设置设备检测结果
     * Author: michael
     * Date: 2019/9/30
     * Time: 14:27
     *
     * @throws Exception
     */
    public function setDeviceAction()
    {
        $params = $this->getParam();

        vss_validator($params, [
            'account_id' => 'required',
            'room_id'    => 'required',
            'status'     => 'required',
            'type'       => 'required'
        ]);
        $join_user = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId(
            $params['account_id'],
            $params['room_id']
        );
        vss_service()->getInavService()->setDevice($join_user, $params['type'], $params['status']);
        $this->success();
    }

    /**
     * Notes:获取用户状态
     * 禁言：1是|0否
     * 踢出：1是|0否
     * Author: michael
     * Date: 2019/9/30
     * Time: 14:27
     *
     */
    public function getUserStatusAction()
    {
        $params = $this->getParam();

        vss_validator($params, [
            'room_id'    => 'required',
            'account_id' => 'required',
        ]);
        $joinUser = $this->getJoinUser($params['account_id'], $params['room_id']);
        $data     = [
            'is_banned' => vss_service()->getRoomService()->getIsBanned($joinUser->room_id, $joinUser->join_id),
            'is_kicked' => vss_service()->getRoomService()->getIsKicked($joinUser->room_id, $joinUser->join_id),
        ];

        # vhallEOF-roomlike-userstatus-start
        
        $data["is_like"] = vss_model()->getRoomLikeModel()->where([
            "room_id"    => $params["room_id"],
            "account_id" => $params["account_id"]
        ])->count();

        # vhallEOF-roomlike-userstatus-end

        $this->success($data);
    }

    /**
     * Notes:设置用户演示状态（主画面）
     * Author: michael
     * Date: 2019/9/30
     * Time: 14:27
     *
     * @throws Exception
     */
    public function setMainScreenAction()
    {
        $params = $this->getParam();

        vss_validator($params, [
            'room_id'            => 'required',
            'receive_account_id' => 'required',
        ]);
        $this->checkRoomLive($params['room_id']);
        $join_user         = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $receive_join_user = $this->getJoinUser(
            $params['receive_account_id'],
            $params['room_id']
        );
        if ($join_user->role_name != \vhallComponent\room\constants\RoomJoinRoleNameConstant::HOST) {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }
        vss_service()->getInavService()->setMainScreen($receive_join_user);
        $this->success();
    }

    /**
     * Notes:桌面演示开关
     * Author: michael
     * Date: 2019/9/30
     * Time: 14:27
     *
     * @throws Exception
     */
    public function setDesktopAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'room_id' => 'required',
            'status'  => 'required'
        ]);
        $this->checkRoomLive($params['room_id']);
        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        if (!in_array(
            $join_user->role_name,
            [
                \vhallComponent\room\constants\RoomJoinRoleNameConstant::GUEST,
                \vhallComponent\room\constants\RoomJoinRoleNameConstant::HOST
            ]
        )) {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }
        vss_service()->getInavService()->setDesktop($join_user->room_id, $params['status']);
        $this->success();
    }

    /**
     * 获取在线用户列表
     *
     * @return void
     * @author ensong.liu@vhall.com
     * @date   2019-06-09 14:01:58
     */
    public function getOnlineListAction()
    {
        $params = $this->getPost();
        vss_validator($params, [
            'room_id' => 'required',
        ]);
        $roomId    = $params['room_id'];
        $page      = $this->getPost('page');
        $pageSize  = $this->getPost('pagesize', 10);
        $condition = [
            'nickname' => $this->getPost('nickname')
        ];

        $data = vss_service()->getRoomService()->getOnlineList($roomId, $page, $pageSize, $condition);
        $this->success([
            'total'    => vss_service()->getRoomService()->getOnlineCount($roomId, $condition),
            'page'     => $page,
            'pagesize' => $pageSize,
            'list'     => $data
        ]);
    }

    /**
     * 踢出/取消踢出用户
     *
     * @return void
     *
     * @author ensong.liu@vhall.com
     * @date   2019-06-09 14:04:23
     */
    public function setKickedAction()
    {
        $params       = $this->getPost();
        $validator    = vss_validator($params, [
            'room_id'            => 'required',
            'receive_account_id' => 'required',
            'status'             => 'required'
        ]);
        $accountIdArr = explode(',', $params['receive_account_id']);
        $res          = vss_service()->getRoomService()->batchKick(
            $params['room_id'],
            $accountIdArr,
            $params['status']
        );
        if (!$res) {
            $this->fail(ResponseCode::BUSINESS_SWITCH_KICK_FAILED);
        }
        $this->success();
    }

    ##########################################################聊天组件###############################################################

    /**
     * 获取禁言用户列表
     *
     * @return void
     * @author ensong.liu@vhall.com
     * @date   2019-06-09 13:59:36
     */
    public function getBannedListAction()
    {
        $params = $this->getPost();
        vss_validator($params, [
            'room_id' => 'required',
        ]);
        $roomId   = $params['room_id'];
        $page     = $this->getPost('page', 1);
        $pageSize = $this->getPost('pagesize', 10);

        $data = vss_service()->getRoomService()->getBannedList($roomId, $page, $pageSize);
        $this->success([
            'total'    => $data->total(),
            'page'     => $data->currentPage(),
            'pagesize' => $data->perPage(),
            'list'     => $data->items()
        ]);
    }

    /**
     * 获取踢出用户列表
     *
     * @return void
     * @author ensong.liu@vhall.com
     * @date   2019-06-09 14:01:58
     */
    public function getKickedListAction()
    {
        $params = $this->getPost();
        vss_validator($params, [
            'room_id' => 'required',
        ]);
        $roomId   = $params['room_id'];
        $page     = $this->getPost('page', 1);
        $pageSize = $this->getPost('pagesize', 10);

        $data = vss_service()->getRoomService()->getKickedList($roomId, $page, $pageSize);
        $this->success([
            'total'    => $data->total(),
            'page'     => $data->currentPage(),
            'pagesize' => $data->perPage(),
            'list'     => $data->items()
        ]);
    }

    /**
     * 获取踢出用户列表和禁言列表
     * @return void
     * @author ensong.liu@vhall.com
     * @date   2019-06-09 14:01:58
     */
    public function getBannedKickedListAction()
    {
        $params = $this->getPost();
        vss_validator($params, [
            'room_id' => 'required',
        ]);
        $roomId   = $params['room_id'];
        $page     = $this->getPost('page', 1);
        $pageSize = $this->getPost('pagesize', 10);
        $data     = vss_service()->getRoomService()->getBannedKickedList($roomId, $page, $pageSize);
        vss_logger()->info('数据信息分页的信息: ', [$data]);
        $this->success([
            'list'     => $data['data'],
            'total'    => $data['total'],
            'page_all' => $data['last_page'],
            'per_page' => $pageSize,
            'page'     => $data['current_page'],
        ]);
    }

    /**
     * 获取互动用户列表
     */
    public function getUserListAction()
    {
        $params = $this->getPost();
        vss_validator($params, [
            'room_id' => 'required',
        ]);
        $roomId = $params['room_id'];
        $data   = vss_service()->getRoomService()->getSpecialList($roomId);
        $this->success($data);
    }

    /**
     * 设置文档白板权限
     *
     * @throws Exception
     */
    public function setDocPermissionAction()
    {
        $params = $this->getParam();

        $validator         = vss_validator($params, [
            'room_id'            => 'required',
            'receive_account_id' => 'required',
        ]);
        $join_user         = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $receive_join_user = $this->getJoinUser(
            $params['receive_account_id'],
            $params['room_id']
        );
        if ($join_user->role_name != \vhallComponent\room\constants\RoomJoinRoleNameConstant::HOST) {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }
        vss_service()->getInavService()->setDocPermission($receive_join_user);
        $this->success();
    }

    #### v2 end

    /**
     * 获取直播间配置信息
     */
    public function getGlobalAction()
    {
        $params = $this->getParam();

        vss_validator($params, [
            'room_id' => 'required',
            'options' => 'required',
        ]);
        $roomId = $params['room_id'];
        $key    = $params['options'];
        $data   = vss_service()->getInavService()->getGlobal($roomId, $key);
        $this->success($data);
    }

    /**
     * 获取主持人信息
     */
    public function getAnchorRenAction()
    {
        $params = $this->getPost();
        vss_validator($params, [
            'room_id'    => 'required',
            'account_id' => 'required',
        ]);
        $account_id = $params['account_id'];
        $room_id    = $params['room_id'];
        $data       = vss_service()->getRoomService()->getDirect($account_id, $room_id);
        $this->success($data);
    }

    /**
     * 设置文档开关状态
     * @author fym
     * @since  2021/7/19
     */
    public function setDocumentStatusAction()
    {
        $params = vss_validator($this->getParam(), [
            'il_id'  => 'required|integer',
            'status' => 'required|integer|in:0,1' // 0 关闭 1 开启
        ]);

        $room = vss_model()->getRoomsModel()->getInfoByIlId($params['il_id']);
        if (!$room) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }

        $accountInfo = vss_service()->getTokenService()->getCurrentJoinUser($room['room_id'])->toArray();

        if (empty($accountInfo) || $accountInfo['role_name'] == RoomJoinRoleNameConstant::USER) {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }

        vss_service()->getRoomService()->setIsOpenDocument($room['room_id'], $params['status']);
        $this->success();
    }
}
