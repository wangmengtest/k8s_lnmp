<?php

namespace vhallComponent\lottery\controllers\console;

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
            'room_id'    => 'required',
            'creator_id' => '',
        ]);
    }

    /**
     * 获取抽奖列表
     */
    public function getsAction()
    {
        $param              = $this->getParam();
        $param['source_id'] = $param['room_id'];
        $data               = LotteryService::gets($param);
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
     * 抽奖导出
     */
    public function exportLotteryAction()
    {
        $params = $this->getParam();
        vss_service()->getLotteryService()->exportLottery($params);
        $this->success();
    }

}
