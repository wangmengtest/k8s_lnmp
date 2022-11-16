<?php

namespace vhallComponent\reward\controllers\v2;

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
 * Class RewardController
 * 打赏控制器
 *+----------------------------------------------------------------------
 *
 * @author  yi.yang@vhall.com
 * @date    2019-06-19 22:51:00
 * @link    http://yapi.vhall.domain/project/21/interface/api/cat_572
 * @version v1.0.0
 *+----------------------------------------------------------------------
 */

use vhallComponent\reward\services\RewardService;

class RewardController extends BaseController
{
    protected $userInfo;

    protected $roomInfo;

    public function init()
    {
        parent::init();
        $param = vss_validator($this->getParam(), [
            'room_id'             => 'required',
            'third_party_user_id' => 'required',
        ]);

        $this->roomInfo = vss_service()->getRoomService()->getRoomInfoByRoomId($param['room_id']);
        if (!$this->roomInfo || !is_array($this->roomInfo)) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }

        $this->userInfo = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId($param['third_party_user_id'],
            $param['room_id']);
        if (empty($this->userInfo)) {
            $this->fail(ResponseCode::BUSINESS_INVALID_USER);
        }
    }

    public function giveAction()
    {
        $param                = $this->getParam();
        $param['rewarder_id'] = $param['third_party_user_id'];
        $param['source_id']   = $param['room_id'];
        if (!empty($param['rewarder_id']) && !empty($param['source_id'])) {
            if (!empty($this->userInfo['nickname'])) {
                $param['rewarder_nickname'] = $this->userInfo['nickname'];
            }
            if (!empty($this->userInfo['avatar'])) {
                $param['rewarder_avatar'] = $this->userInfo['avatar'];
            }
            if (empty($param['describe'])) {
                $param['describe'] = '很精彩，赞一个！';
            }
            $data = RewardService::give($param);
            $this->success($data);
        } else {
            $this->fail(ResponseCode::COMP_REWARD_FAILED);
        }
    }

    /**
     * 获取打赏排行信息
     */
    public function rewarderRankAction()
    {
        $param = $this->getParam();
        $data = RewardService::rewarderRank($param);
        $this->success($data);
    }

    /**
     * 获取打赏排行榜
     */
    public function listRankAction()
    {
        $param = $this->getParam();
        $data = RewardService::listRank($param);
        $this->success($data);
    }

    /**
     * 获取打赏记录列表
     */
    public function recordsGetAction()
    {
        $param = $this->getParam();
        $data = RewardService::recordsGet($param);
        $this->success($data);
    }
}
