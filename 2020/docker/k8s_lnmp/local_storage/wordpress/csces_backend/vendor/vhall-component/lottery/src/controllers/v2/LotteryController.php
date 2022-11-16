<?php

namespace vhallComponent\lottery\controllers\v2;

use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;

/**
 *+----------------------------------------------------------------------
 * @file SignController.php
 * @date 2019-06-19 22:51:00
 *+----------------------------------------------------------------------
 */

use vhallComponent\lottery\services\LotteryService;

/**
 *+----------------------------------------------------------------------
 * Class LotteryController
 * 抽奖控制器
 *+----------------------------------------------------------------------
 *
 * @author  yi.yang@vhall.com
 * @date    2019-06-19 22:51:00
 * @link    http://yapi.vhall.domain/project/21/interface/api/cat_600
 * @version v1.0.0
 *+----------------------------------------------------------------------
 */
class LotteryController extends BaseController
{
    public function init()
    {
        parent::init();

        vss_validator($this->getParam(), [
            'room_id'             => 'required',
            'third_party_user_id' => 'required',
        ]);
    }

    /**
     * 发起抽奖
     */
    public function addAction()
    {
        $param               = $this->getParam();
        $param['creator_id'] = $param['third_party_user_id'];
        $param['source_id']  = $param['room_id'];
        $userInfo            = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId($param['third_party_user_id'],
            $param['room_id']);

        if (empty($userInfo)) {
            $this->fail(ResponseCode::EMPTY_USER);
        }

        if (!empty($userInfo['nickname'])) {
            $param['creator_nickname'] = $userInfo['nickname'];
        }
        if (!empty($userInfo['avatar'])) {
            $param['creator_avatar'] = $userInfo['avatar'];
        }
        $data = LotteryService::add($param);
        if (!empty($data) && is_array($data)) {
            //抽奖上报
            $lottery_data              = $data;
            $lottery_data['vss_token'] = $param['vss_token'];
            vss_service()->getBigDataService()->requestServerLotteryParams($lottery_data);
        }
        $this->success($data);
    }

    /**
     * 获取可以参与抽奖的人数
     */
    public function countAction()
    {
        $param                     = $this->getParam();
        $data                      = [];
        $data['count']             = LotteryService::getCount($param);
        $data['room_id']           = $param['room_id'];
        $data['lottery_type']      = $param['lottery_type'];
        $data['lottery_rule']      = $param['lottery_rule'];
        $data['lottery_rule_text'] = $param['lottery_rule_text'];
        $this->success($data);
    }

    /**
     * 搜索符合范围条件的抽奖用户名单
     */
    public function searchAction()
    {
        $param                = $this->getParam();
        $data                 = [];
        $data['list']         = LotteryService::search($param);
        $data['room_id']      = $param['room_id'];
        $data['lottery_type'] = $param['lottery_type'];
        $data['lottery_rule'] = $param['lottery_rule'];
        $data['keyword']      = $param['keyword'];
        $this->success($data);
    }

    /**
     * 结束抽奖
     */
    public function endAction()
    {
        $param               = $this->getParam();
        $param['creator_id'] = $param['third_party_user_id'];
        $param['source_id']  = $param['room_id'];
        $data                = LotteryService::end($param);
        $this->success($data);
    }

    /**
     * 更新领奖信息
     */
    public function awardAction()
    {
        $param                    = $this->getParam();
        $param['lottery_user_id'] = $param['third_party_user_id'];
        $param['source_id']       = $param['room_id'];
        $data                     = LotteryService::award($param);
        $this->success($data);
    }

    /**
     * 获取抽奖列表
     */
    public function getsAction()
    {
        $param               = $this->getParam();
        $param['creator_id'] = $param['third_party_user_id'];
        $param['source_id']  = $param['room_id'];
        $data                = LotteryService::gets($param);
        $this->success($data);
    }

    /**
     * 获取抽奖中奖用户名单
     */
    public function usersGetAction()
    {
        $param              = $this->getParam();
        $param['source_id'] = $param['room_id'];
        $data               = LotteryService::usersGet($param);
        $this->success($data);
    }

    /**
     * 获取抽奖列表及中奖信息
     */
    public function detailListAction()
    {
        $param               = $this->getParam();
        $param['creator_id'] = $param['third_party_user_id'];
        $param['source_id']  = $param['room_id'];
        $data                = LotteryService::detailList($param);
        $this->success($data);
    }

    /**
     * 导入数据
     */
    public function importUserAction()
    {
        $param = $this->getParam();
        $data  = vss_service()->getLotteryService()->importUser($param);
        $this->success($data);
    }

    /**
     * 抽奖模板导出
     */
    public function importTemplateAction()
    {
        vss_service()->getLotteryService()->importTemplate();
    }

    /**
     * 当前抽奖模板名称
     */
    public function importTitleAction()
    {
        $param = $this->getParam();
        $data  = vss_service()->getLotteryService()->importTitle($param['room_id']);
        $this->success($data);
    }

    /**
     * 发布中奖信息
     */
    public function publishAction()
    {
        $param = $this->getParam();
        $data  = vss_service()->getLotteryService()->publish($param);
        $this->success($data);
    }
}
