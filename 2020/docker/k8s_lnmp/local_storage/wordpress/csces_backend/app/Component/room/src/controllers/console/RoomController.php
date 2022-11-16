<?php

namespace App\Component\room\src\controllers\console;

use App\Component\account\src\constants\AccountConstant;
use App\Component\room\src\constants\RoomConstant;
use App\Component\room\src\constants\UploadConstant;
use App\Constants\ResponseCode;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use vhallComponent\decouple\controllers\BaseController;
use Illuminate\Support\Arr;
use App\Component\common\src\services\UploadFile;
use App\Component\room\src\constants\RoomJoinRoleNameConstant;
use App\Component\room\src\constants\RspStructConstant;
use App\Http\services\FileUpload;
use vhallComponent\watchlimit\constants\WatchlimitConstant;

/**
 * RoomController extends BaseController
 *
 * @author   jin.yangjin@vhall.com
 * @uses     yangjin
 * @date     2020-08-10
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class RoomController extends BaseController
{
    /**
     * 房间-创建记录
     *
     *
     * @throws \Throwable
     * @author ensong.liu@vhall.com
     * @date   2019-05-21 14:50:54
     */
    public function createAction(FileUpload $fileUpload, Request $request)
    {
        vss_validator($this->getParam(), [
            'name'             => 'required',
            'begin_time'       => 'required',
            'begin_time_stamp' => 'required',
            'live_type'        => 'in:1,2', // 1 互动直播 2 纯直播
            'show_chat'        => 'in:1,2', // 0 不展示 1 展示
            'show_duration'    => 'int',
            'notice_time'      => 'string',
            'limit_type'       => 'required|in:2,4', //0:登录 1:报名 2:默认/公开模式 3:白名单 4:内部模式
        ]);
        if($request->file('image')){
            if(!$this->isImage($_FILES['image']['name'])){
                $this->fail(ResponseCode::TYPE_INVALID_IMAGE);
            }
            $image = $fileUpload->store('image', 'img');
        }
        $name      = $this->getParam('name');
        $beginTime = $this->getParam('begin_time');

        $rooms = vss_service()->getPaasService()->createRoom();
        $orgIdByCode = vss_service()->getAccountOrgService()->orgIdByCode();
        if(empty($orgIdByCode[$this->accountInfo['org']])){
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }
        $data  = [
            'account_id'       => $this->accountInfo['account_id'],
            'account_name'     => $this->accountInfo['nickname'],
            'org'              => $orgIdByCode[$this->accountInfo['org']] ?? '',
            'dept'             => $orgIdByCode[$this->accountInfo['dept']] ?? '',
            'room_id'          => $rooms['live_room'],
            'channel_id'       => $rooms['channel_room'],
            'nify_channel'     => $rooms['nify_channel_room'],
            'inav_id'          => $rooms['hd_room'],
            'cover_image'      => $image ?? '',
            'subject'          => !empty($name) ? $name : sprintf(
                '%s年%s月%s日%s的直播',
                date('Y'),
                date('m'),
                date('d'),
                $this->accountInfo['nickname']
            ),
            'introduction'     => $this->getParam('introduction'),
            'start_time'       => empty($beginTime) ? date('Y-m-d H:i:s') : $beginTime,
            'begin_time_stamp' => $this->getParam('begin_time_stamp') ? $this->getParam('begin_time_stamp') : time(),
            'teacher_name'     => $this->getParam('teacher_name'),
            'topics'           => $this->getParam('topics'),
            'category'         => $this->getParam('category', 1),
            'vod_id'           => $this->getParam('vod_id'),
            'layout'           => $this->getParam('layout', 0),
            'mode'             => $this->getParam('mode', 1),
            'app_id'           => vss_service()->getTokenService()->getAppId(),
            'live_type'        => $this->getParam('live_type', 1),
            'show_chat'        => intval($this->getParam('show_chat', 1)),
            'show_duration'    => intval($this->getParam('show_duration', 0)),
            'notice_time'      => $this->getParam('notice_time', null),
            'limit_type'       => intval($this->getParam('limit_type', 4)),
        ];

        $data     = $this->getRoomExtendColumnData($data);
        $roomInfo = vss_service()->getRoomService()->create($data);
        if (!empty($roomInfo)) {
            $params = [
                'room_id'       => $roomInfo['room_id'],
                'account_id'    => $roomInfo['account_id'],
                'nickname'      => $this->accountInfo['nickname'],
                'username'      => $this->accountInfo['username'],
                'avatar'        => $this->accountInfo['profile_photo'] ?:
                    vss_config('application.static.headPortrait.default'),
                'role_name'     => RoomJoinRoleNameConstant::HOST,
                'device_type'   => 0,
                'device_status' => 0,
            ];
            vss_service()->getRoomService()->join($params);
            vss_service()->getRoomInvitedService()->createRoomInvited($roomInfo['il_id'], array_merge(['account_id'=>$this->accountInfo['account_id']], $this->getParam()));
            # vhallEOF-anchormanage-roomController-get-1-start

            //主播关联
            $anchorId = $this->getParam("anchor_id", "");
            if ($anchorId) {
                vss_service()->getAnchorManageService()->linkAnchorRoom($anchorId, $roomInfo["il_id"]);
            }

            //VSS_TOKEN
            $create_token        = [
                'app_id'              => vss_service()->getTokenService()->getAppId(),
                'third_party_user_id' => $this->accountInfo['account_id'],
                'expire_time'         => date('Y-m-d H:i:s', strtotime('+1 day')),
            ];
            $roomInfo->vss_token = vss_service()->getTokenService()->create($create_token)['access_token'] ?? '';
            $this->success($roomInfo);
        } else {
            $this->fail(ResponseCode::BUSINESS_CREATE_FAILED);
        }
    }

    /**
     * 获取房间扩展配置信息
     *
     * @param $data
     *
     * @return array
     */
    public function getRoomExtendColumnData($data)
    {
        $columns = [];
        # vhallEOF-config-RoomController-extend-1-start

        $columns = vss_service()->getConfigInfoService()->getRoomExtendColumn();

        # vhallEOF-config-RoomController-extend-1-end

        $extend = [];
        foreach ($columns as $column) {
            $extend[$column] = $this->getParam($column, '');
        }

        $data['extend'] = json_encode($extend);
        return $data;
    }

    /**q
     * 房间-获取记录
     *
     *
     * @author ensong.liu@vhall.com
     * @date   2019-05-21 14:49:15
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
        # vhallEOF-anchormanage-roomController-get-2-start

        //获取主播id
        /*$anchor = vss_service()->getAnchorManageService()->getAnchorIdByIlId($params["il_id"]);
        if ($anchor) {
            $data["anchor_id"] = $anchor->anchor_id;
        }*/

        # vhallEOF-anchormanage-roomController-get-2-end
        $this->success($data);
        //$this->success(vss_service()->getRoomFormatService()->formatDetail($data));
    }

    /**
     * 房间-删除记录
     *
     *
     * @throws Exception
     * @author ensong.liu@vhall.com
     * @date   2019-05-21 15:17:15
     */
    public function deleteAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'il_id'      => 'required',
            'account_id' => 'required',
        ]);
        $ilIds = explode(',', $params['il_id']);
        vss_logger()->info('room_delete_action', [$params, $ilIds]);

        //$ilIdList = vss_model()->getRoomsModel()->where('account_id', $params['account_id'])->whereIn('il_id',$ilIds)->get(['il_id']);
        $ilIdList = vss_model()->getRoomsModel()->whereIn('il_id', $ilIds)->get(['il_id']);
        vss_logger()->info('room_delete_action', [$ilIdList, $ilIds]);
        $ilIdStr = '';
        if (!empty($ilIdList)) {
            $ilIdList = $ilIdList->toArray();
            if(empty($ilIdList)){
                $this->fail(ResponseCode::EMPTY_ROOM);
            }
            $ilIds    = array_column($ilIdList, 'il_id');
            $ilIdStr  = implode(',', $ilIds);
        }
        vss_logger()->info('room_delete_action1', [$ilIdStr, $ilIdList]);
        $data = vss_service()->getRoomService()->deleteByIds($ilIdStr);
        //返回数据
        $this->success($data);
    }

    /*
     * 判断上传的是否是图片
     * */
    protected function isImage($filename) {
        $ext = substr($filename, strrpos($filename, '.')+1);//strrpos返回最后一次出现的索引位置
        return in_array($ext, UploadConstant::IMAGE_TYPE);
    }

    /**
     * 房间-修改记录
     * @author ensong.liu@vhall.com
     * @date   2019-05-21 14:52:17
     */
    public function updateAction(FileUpload $fileUpload, Request $request)
    {
        $params = $this->getParam(null, '', true);
        vss_logger()->error(
            'csces-room-update',
            ['url' => 'conlose/room/update', 'params' => $params]
        );
        $rule   = [
            'il_id'            => 'required',
            'subject'          => '',
            'introduction'     => '',
            'start_time'       => '',
            'begin_time_stamp' => '',
            'category'         => '',
            'topics'           => '',
            'account_id'       => '',
            'layout'           => '',
            'cover_image'      => '',
            'record_id'        => '',
            'notice_time'      => '',
            'show_duration'    => '',
            'show_chat'        => '',
            'limit_type'       => '',
        ];
        $params = vss_validator($params, $rule);

        if (strtotime($this->getParam('begin_time')) < time()) {
            $this->fail(ResponseCode::BUSINESS_OPEN_PLAY_TIME_ERROR);
        }

        if ($this->getParam('name')) {
            $params['subject'] = $this->getParam('name');
        }
        if ($this->getParam('begin_time')) {
            $params['start_time'] = $this->getParam('begin_time');
        }
        if ($this->getParam('changeimg', '')) {
            if($request->file('image')){
                if(!$this->isImage($_FILES['image']['name'])){
                    $this->fail(ResponseCode::TYPE_INVALID_IMAGE);
                }
                $params['cover_image'] = $fileUpload->store('image', 'img');
            }
        }

        $params['app_id'] = vss_service()->getTokenService()->getAppId();
        $ret              = vss_service()->getRoomService()->update($params);
        if ($ret) {
            $roomInfo = vss_model()->getRoomsModel()->getInfoByIlId($params['il_id']);

            vss_service()->getRoomInvitedService()->createRoomInvited($roomInfo['il_id'], array_merge(['account_id'=>$this->accountInfo['account_id']], $this->getParam()));
            # vhallEOF-anchormanage-roomController-get-3-start

            //修改/取消关联主播
            if ($roomInfo["status"] == 0) {
                $anchorId = $this->getParam("anchor_id", "");
                if ($anchorId) {
                    vss_service()->getAnchorManageService()->modifyLink($anchorId, $params["il_id"]);
                } else {
                    vss_service()->getAnchorManageService()->deleteLink($params["il_id"]);
                }
            }

            //有可能修改通知时间,需要移除集合元素,再次触发通知逻辑
            if(vss_redis()->sismember(RoomConstant::ROOMS_SMS_NOTICE_DEFAULT . date('Y-m-d'), intval($params["il_id"]))){
                vss_redis()->srem(RoomConstant::ROOMS_SMS_NOTICE_DEFAULT . date('Y-m-d'), intval($params["il_id"]));
            }
            if(vss_redis()->sismember(RoomConstant::ROOMS_PLAN_SMS_NOTICE_DEFAULT . date('Y-m-d'), intval($params["il_id"]))){
                vss_redis()->srem(RoomConstant::ROOMS_PLAN_SMS_NOTICE_DEFAULT . date('Y-m-d'), intval($params["il_id"]));
            }
        }
        $this->success($roomInfo);
    }

    /**
     * 房间-我创建的列表
     */
    public function ownerListAction()
    {
        $liveList = vss_service()->getRoomListService()->ownerList($this->getParam(), $this->accountInfo);
        $this->success($liveList, RspStructConstant::LIST);
    }

    /**
     * 房间-我创建的count
     */
    public function ownerCountAction()
    {
        $count = vss_service()->getCacheRoomListService()->ownerCount($this->getParam(), $this->accountInfo);
        $this->success(['count'=>$count]);
    }

    /**
     * 房间管理-列表
     */
    public function manageListAction()
    {
        $params = $this->getParam();

        // dept 权限控制
        if (isset($params['dept'])) {
            $params['dept']     = vss_service()->getAccountsService()->adaptDeptPermission($this->accountInfo, $params['dept'], "dept");
        }

        if (isset($params['org'])) {
            $params['org']      = vss_service()->getAccountsService()->adaptDeptPermission($this->accountInfo, $params['org'], "org");
        }

        $liveList = vss_service()->getRoomListService()->manageList($params, $this->accountInfo);
        $this->success($liveList, RspStructConstant::LIST);
    }

    /**
     * 房间-我参加的
     */
    public function invitedListAction()
    {
        $liveList = vss_service()->getRoomListService()->inviteList($this->getParam(), $this->accountInfo);
        $this->success($liveList, RspStructConstant::INVITED_LIST);
    }

    /**
     * 房间-我参加的-count
     */
    public function invitedCountAction()
    {
        $count = vss_service()->getCacheRoomListService()->inviteCount($this->getParam(), $this->accountInfo);
        $this->success(['count'=>$count]);
    }

    /**
     * 观看端-房间列表
     */
    public function watchListAction()
    {
        $liveList = vss_service()->getCacheRoomListService()->watchList($this->getParam(), $this->accountInfo);
        $this->success($liveList, RspStructConstant::LIST);
    }

    /**
     * 房间-获取推流地址
     *
     *
     * @author ensong.liu@vhall.com
     * @date   2019-05-21 15:03:21
     */
    public function getStreamAddressAction()
    {
        $params = $this->getParam();

        vss_validator($params, [
            'il_id' => 'required',
        ]);
        $where    = [
            'il_id'      => $params['il_id'],
            'account_id' => $this->accountInfo['account_id']
        ];
        $roomInfo = vss_service()->getRoomService()->getRow($where);

        $expireTime = date('Y-m-d H:i:s', time() + (24 * 3600 * 7));
        $streamInfo = vss_service()->getPaasService()->getPushInfo($roomInfo['room_id'], $expireTime);
        $this->success($streamInfo);
    }

    /**
     * 房间-获取口令信息
     *
     *
     * @author ensong.liu@vhall.com
     * @date   2019-05-21 14:55:03
     */
    public function getCompetenceAction()
    {
        $params = $this->getParam();

        vss_validator($params, [
            'il_id' => 'required',
        ]);

        $ilId      = $params['il_id'];
        $accountId = $this->accountInfo['account_id'];
        $roomInfo  = vss_model()->getRoomsModel()->getInfoByIlIdAndAccountId($ilId, $accountId);
        if (empty($roomInfo)) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }

        $roomSupplyInfo = vss_service()->getRoomService()->getRoomSupplyByIlId($ilId);
        $roomInfo       = array_merge($roomInfo, $roomSupplyInfo);
        $this->success($roomInfo);
    }

    /**
     * 判断房间状态
     */
    public function getRoomStatusAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'il_id' => 'required',
        ]);
        $condition = [
            'il_id'      => $params['il_id'],
            'account_id' => $this->accountInfo['account_id']
        ];

        $res = vss_model()->getRoomsModel()->where($condition)->first(['status']);
        $this->success($res);
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

    /**
     * 添加/更新自定义标签
     *
     *
     * @author bingtian.yu@vhall.com
     * @date   2020-06-17 11:03:48
     */
    public function updateCustomTagAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'il_id'      => 'required',
            'custom_tag' => 'required',
        ]);

        $ilId      = $params['il_id'];
        $customTag = $params['custom_tag'];

        $extends = vss_service()->getRoomService()->updateCustomTag($ilId, $customTag,
            $this->accountInfo['account_id']);

        $this->success($extends);
    }

    /**
     * 保存暖场视频
     * @throws Exception
     */
    public function saveWarmAction()
    {
        $data['type'] = $this->getParam('type', 0);
        if ($data['type'] == 1) {
            $data['vod_id'] = $this->getParam('vod_id', 0);
            if (!$data['vod_id']) {
                $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
            }
        }

        $data['il_id'] = $this->getParam('il_id', 0);
        if (empty($data['il_id'])) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }

        $result = vss_service()->getRoomService()->saveWarm($data);
        $this->success($result);
    }

    /**
     * 获取暖场信息
     *
     *
     */
    public function getWarmInfoAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'il_id' => 'required',
        ]);
        $params['account_id'] = $this->accountInfo['account_id'];

        $result = vss_service()->getRoomService()->getWarm($params);
        if ($result) {
            $this->success($result);
        }
    }

    /**
     * 获取房间属性状态
     * 白板开关、文档开关、举手开关、布局、清晰度等
     *
     * @return void
     *
     * @author       michael
     * @date         2019/9/30
     */
    public function getAttributesAction()
    {
        $params = $this->getParam();
        $data   = vss_service()->getRoomService()->getAttr($params);
        $this->success($data);
    }

    /**
     * 获取ACCESS_TOKEN数据
     */
    public function getAccessTokenAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'il_id' => 'required',
        ]);

        $ilId = $params['il_id'];
        //2、获取房间信息
        $roomInfo = vss_model()->getRoomsModel()->getInfoByIlId($ilId);
        if (!$roomInfo) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }
        $accessToken = vss_service()->getRoomService()->getAccessToken($roomInfo, $params['account_id']);
        if (empty($accessToken)) {
            $this->fail(ResponseCode::BUSINESS_CREATE_FAILED);
        }

        $this->success(['access_token' => $accessToken]);
    }

    /**
     * 房间数量信息
     */
    public function countInfoAction()
    {
        $data = vss_service()->getRoomService()->roomCount($this->accountInfo['account_id']);
        $this->success($data);
    }

    /**
     * 更新直播模式
     * @author fym
     * @since  2021/6/28
     */
    public function addLiveModeAction()
    {
        $params = vss_validator($this->getParam(), [
            'il_id'     => 'required|integer',
            'live_mode' => 'required|integer|in:1,2'
        ]);

        $params['account_id'] = $this->getParam('third_party_user_id');
        vss_service()->getRoomService()->update($params);
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
     * 修改房间状态
     */
    public function editStatusAction()
    {
        $params = $this->getParam();
        $params = vss_validator($params, [
            'il_id'      => 'required',
            'account_id' => 'required',
        ]);

        //恢复预告
        vss_service()->getRoomService()->editStatusToWating($params);
        $this->success();
    }

    /**
     * 检查嘉宾/助理口令
     */
    public function checkRoomPasswordAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'il_id'      => 'required',
            'role_name'  => 'required|in:3,4',
            'password'   => 'required',
        ]);
        vss_service()->getRoomService()->checkRoomPassword($params);
        $this->success([]);
    }

    /**
     * 第一次观看列表-展示有数据的tab
     */
    public function firstWatchListAction()
    {
        $status = vss_service()->getCacheRoomListService()->firstWatchList($this->getParam(), $this->accountInfo);
        $status = ($status === '') ? RoomConstant::STATUS_START : $status;
        $this->success(['tab'=>$status]);
    }
}
