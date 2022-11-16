<?php

namespace vhallComponent\sign\controllers\v2;

use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;

/**
 *+----------------------------------------------------------------------
 * @file SignController.php
 * @date 2019-06-19 22:51:00
 *+----------------------------------------------------------------------
 */

/**
 *+----------------------------------------------------------------------
 * Class SignController
 * 签到控制器
 *+----------------------------------------------------------------------
 *
 * @author  yi.yang@vhall.com
 * @date    2019-06-19 22:51:00
 * @link    http://yapi.vhall.domain/project/21/interface/api/cat_540
 * @version v1.0.0
 *+----------------------------------------------------------------------
 */

use vhallComponent\sign\services\SignService;

class SignController extends BaseController
{
    protected $userInfo;

    protected $roomInfo;

    public function init()
    {
        parent::init();
        $param = $this->getParam();
        if (!empty($param['room_id'])) {
            $this->roomInfo = vss_service()->getRoomService()->getRoomInfoByRoomId($param['room_id']);
            if (!empty($this->roomInfo) && is_array($this->roomInfo)) {
                if (!empty($param['third_party_user_id'])) {
                    $this->userInfo = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId($param['third_party_user_id'],
                        $param['room_id']);
                    if (empty($this->userInfo)) {
                        $this->fail(ResponseCode::EMPTY_USER);
                    }
                } else {
                    $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
                }
            } else {
                $this->fail(ResponseCode::EMPTY_ROOM);
            }
        } else {
            if (empty($param['type'])) {
                $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
            }
        }
    }

    /**
     * 发起签到
     */
    public function addAction()
    {
        $param               = $this->getParam();
        $param['creator_id'] = $param['third_party_user_id'];
        $param['source_id']  = $param['room_id'];
        if (!empty($param['creator_id']) && !empty($param['source_id'])) {
            if (!empty($this->userInfo['nickname'])) {
                $param['creator_nickname'] = $this->userInfo['nickname'];
            }
            if (!empty($this->userInfo['avatar'])) {
                $param['creator_avatar'] = $this->userInfo['avatar'];
            }
            $data = SignService::add($param);
            //记录签到发起时间
            $sign_data              = $data;
            $sign_data['vss_token'] = $param['vss_token'];
            vss_service()->getBigDataService()->requestServerSignParams($sign_data);

            $this->success($data);
        } else {
            $this->fail(ResponseCode::COMP_SIGN_INITIATE_FAILED);
        }
    }

    /**
     * 用户签到
     */
    public function inAction()
    {
        $param              = $this->getParam();
        $param['signer_id'] = $param['third_party_user_id'];
        $param['source_id'] = $param['room_id'];
        if (!empty($param['signer_id']) && !empty($param['source_id'])) {
            if (!empty($this->userInfo['nickname'])) {
                $param['signer_nickname'] = $this->userInfo['nickname'];
            }
            if (!empty($this->userInfo['avatar'])) {
                $param['signer_avatar'] = $this->userInfo['avatar'];
            }
            $data = SignService::in($param);
            //签到上报
            vss_service()->getBigDataService()->requestClientSignParams($param);
            $this->success($data);
        } else {
            $this->fail(ResponseCode::COMP_SIGN_USER_FAILED);
        }
    }

    /**
     * 获取发起的签到列表
     */
    public function getsAction()
    {
        $param               = $this->getParam();
        $param['creator_id'] = $param['third_party_user_id'];
        $param['source_id']  = !empty($param['room_id']) ? $param['room_id'] : '';
        if (!empty($param['type']) && $param['type'] == 'all') {
            unset($param['source_id']);
        }
        $data = SignService::gets($param);
        $this->success($data);
    }

    /**
     * 获取签到记录列表
     */
    public function recordsGetAction()
    {
        $param               = $this->getParam();
        $param['creator_id'] = $param['third_party_user_id'];
        $param['source_id']  = !empty($param['room_id']) ? $param['room_id'] : '';
        if (!empty($param['type']) && $param['type'] == 'all') {
            unset($param['source_id']);
        }
        $data = SignService::recordsGet($param);
        $this->success($data);
    }
}
