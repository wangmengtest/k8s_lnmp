<?php

namespace App\Component\room\src\controllers\v2;

use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;

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
     * 获取房间信息
     *
     *
     * @author  jin.yang@vhall.com
     * @date    2020-08-08
     */
    public function getAction()
    {
        $roomId            = $this->getPost('room_id');
        $join_user         = vss_service()->getTokenService()->getCurrentJoinUser($roomId);
        $roomInfo          = vss_service()->getRoomService()->get($roomId, $join_user->account_id);
        $data              = $roomInfo->toArray();
        $data['role_name'] = $join_user->role_name;
        $data['nickname']  = $join_user->nickname;
        $this->success($data);
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
        $data = vss_service()->getRoomService()->getAttr($this->getParam());
        $this->success($data);
    }

    /**
     * 获取推流信息
     *
     * @return void
     * @author michael
     * @date   2019/9/30
     */
    public function getPushInfoAction()
    {
        $data = vss_service()->getRoomService()->getPushInfo($this->getParam());
        $this->success($data);
    }

    /**
     * 获取直播流的流信息
     *
     * @return void
     * @author michael
     * @date   2019/9/30
     */
    public function getStreamMsgAction()
    {
        $data = vss_service()->getRoomService()->getStreamMsg($this->getParam());
        $this->success($data);
    }

    /**
     * 开始直播
     *
     * @return void
     *
     * @author       michael
     * @date         2019/9/30
     */
    public function startLiveAction()
    {
        $roomId    = $this->getPost('room_id');
        $startType = $this->getPost('start_type', 1);
        if (!vss_service()->getRoomService()->startLive($roomId, $startType)) {
            $this->fail(ResponseCode::BUSINESS_START_LIVE_FAILED);
        }
        $this->success();
    }

    /**
     * 结束直播
     *
     * @return void
     *
     * @author       michael
     * @date         2019/9/30
     */
    public function endLiveAction()
    {
        $roomId  = $this->getPost('room_id');
        $endType = $this->getPost('end_type', 0);
        $vodid   = $this->getPost('vod_id');
        $tag     = $this->getPost('tag');
        vss_logger()->info('rtm-pull-first', [$tag]);
        if ($vodid) {
            // 结束点播
            $params = [
                'vod_id'  => $vodid,  // 点播ID
                'action'  => 'SubmitVODToLive',
                'app_id'  => vss_config('paas.apps.lite.appId'), //$this->getPost('app_id'),
                'cmd'     => 'stop',
                'room_id' => $roomId,
                'quality' => $this->getPost('quality', '720p'),
                'loop'    => $this->getPost('loop', 1),
            ];
            vss_service()->getPaasService()->dibblingVod($params);
        } else {
            if (!empty($tag)) {
                $res = vss_service()->getRoomService()->endLive($roomId, $endType, ['tag' => true]);
            } else {
                $res = vss_service()->getRoomService()->endLive($roomId, $endType);
            }
            if (!$res) {
                $this->fail(ResponseCode::BUSINESS_END_LIVE_FAILED);
            }
        }
        $this->success();
    }

    /**
     * 设置直播状态
     *
     * @return void
     * @author michael
     * @date   2019/9/30
     */
    public function setLiveStatusAction()
    {
        $roomId = $this->getPost('room_id');
        $status = $this->getPost('status');
        if (!vss_service()->getRoomService()->setStatus($roomId, $status)) {
            $this->fail(ResponseCode::BUSINESS_SET_FAILED);
        }
        $this->success();
    }

    /**
     * 判断房间状态
     *
     *
     */
    public function getRoomStatusAction()
    {
        $roomId  = $this->getPost('room_ids');
        $roomArr = vss_service()->getRoomService()->getRoomsStatu($roomId);
        $this->success($roomArr);
    }

    /**
     * Notes: 直接开始点播转直播
     * Author: michael
     * Date: 2019/10/12
     * Time: 11:16
     *
     */
    public function dibblingAction()
    {
        $params = [
            'vod_id'  => $this->getPost('vod_id'),  // 点播ID
            'action'  => 'SubmitVODToLive',
            'app_id'  => vss_config('paas.apps.lite.appId'), //$this->getPost('app_id'),
            'cmd'     => $this->getPost('cmd', 'start'),
            'room_id' => $this->getPost('room_id'),
            'quality' => $this->getPost('quality', '720p'),
            'loop'    => $this->getPost('loop', 1),
        ];
        // 开始转直播
        $data = vss_service()->getPaasService()->dibblingVod($params);
        vss_service()->getRoomService()->createDibbling($params);
        $this->success($data);
    }

    /**
     * 获取房间的直播信息
     */
    public function getRoomExtendsInfoAction()
    {

        //1、接收参数信息
        $params = $this->getParam();
        //1.1、验证
        vss_validator($params, [
            'room_id' => 'required',
        ]);

        //2、返回参数信息
        $roomInfo = vss_service()->getRoomService()->getRoomExtends($params['room_id']);
        $this->success($roomInfo);
    }

    public function warmVideoAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'il_id'  => 'required',
            'type'   => 'required',
            'vod_id' => 'required_if:type,1',
        ]);

        $roomArr = vss_service()->getRoomService()->saveWarm($params);
        $this->success($roomArr);
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
            'il_id'      => 'required',
            'account_id' => 'required',
        ]);

        $result = vss_service()->getRoomService()->getWarm($params);
        if ($result) {
            $this->success($result);
        }
    }
}
