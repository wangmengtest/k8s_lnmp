<?php
/**
 * Created by PhpStorm.
 * User: liuxiangliang
 * Date: 2020/10/15
 * Time: 18:31
 */

namespace vhallComponent\pay\controllers\api;

use vhallComponent\decouple\controllers\BaseController;

class PayController extends BaseController
{
    /**
     * 支付回调通知接口
     */
    public function notifyAction()
    {
        $param = $_POST;

        vss_logger()->info('pay_notify', ['POST' => $_POST, 'GET' => $_GET, 'REQUEST' => $_REQUEST]);
        $payService = vss_service()->getPayService();
        if ($payService::checkSign($param)) {
            if ($param['optional'] == 'reward') {
                $statusParam = [
                    'app_id'               => $param['app_id'],
                    'trade_no'             => $param['biz_order_no'],
                    'pay_status'           => $param['pay_status'] == 'SUCCESS' ? 1 : 2,
                    'third_party_trade_no' => $param['trade_no'],
                    'channel'              => $param['channel']
                ];
                $rewardService = vss_service()->getRewardService();
                if ($rewardService::payStatusSet($statusParam)) {
                    //打赏上报
                    vss_service()->getBigDataService()->requestRewardParams(date('Y-m-d') . '_reward_' . $param['biz_order_no']);
                    die('success');
                }
            } elseif ($param['optional'] == 'gift') {
                $statusParam = [
                    'app_id'               => $param['app_id'],
                    'trade_no'             => $param['biz_order_no'],
                    'pay_status'           => $param['pay_status'] == 'SUCCESS' ? 1 : 2,
                    'third_party_trade_no' => $param['trade_no'],
                    'channel'              => $param['channel']
                ];
                if (vss_service()->getGiftService()->setPayStatus($statusParam)) {
                    //送礼物上报
                    vss_service()->getBigDataService()->requestGiftParams(date('Y-m-d') . '_gift_' . $param['biz_order_no']);
                    die('success');
                }
            } elseif ($param['optional'] == 'red_packet') {
                $statusParam = [
                    'app_id'               => $param['app_id'],
                    'trade_no'             => $param['biz_order_no'],
                    'pay_status'           => $param['pay_status'] == 'SUCCESS' ? 1 : 2,
                    'third_party_trade_no' => $param['trade_no'],
                    'channel'              => $param['channel']
                ];
                $redpacketService = vss_service()->getRedpacketService();
                if ($redpacketService::payStatusSet($statusParam)) {
                    die('success');
                }
            }
        }
        die('failure');
    }
}
