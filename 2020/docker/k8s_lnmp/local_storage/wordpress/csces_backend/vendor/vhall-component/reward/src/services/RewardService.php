<?php

namespace vhallComponent\reward\services;

use App\Constants\ResponseCode;
use Vss\Common\Services\WebBaseService;
use vhallComponent\pay\services\PayService;
use vhallComponent\redpacket\services\RedpacketService;

class RewardService extends WebBaseService
{
    const PAY_TYPE = [
        'ALIPAY' => 1,
        'WEIXIN'=>3
    ];

    /**
     * 打赏接口
     * @param $params
     * @return mixed
     * @throws $error
     */
    public static function give($params)
    {
        vss_validator($params, [
            'channel'       => 'required|in:ALIPAY,WEIXIN',
            'service_code'  => 'required|in:QR_PAY,CASHIER,H5_PAY,JSAPI',
            'reward_amount' => 'required|numeric',
            'source_id'     => 'required|max:255',
            'describe'      => '',
            'open_id'       => 'required_if:service_code,JSAPI'
        ]);
        $roomInfo  = vss_service()->getRoomService()->getRoomInfoByRoomId($params['source_id']);
        if (empty($roomInfo['account_id'])) {
            self::getInstance()->fail(ResponseCode::EMPTY_ROOM);
        }
        $params['receiver_id']   = !empty($roomInfo['account_id']) ? $roomInfo['account_id'] : '';
        $params['reward_amount'] = $params['reward_amount'];
        if ($params['reward_amount'] < 0) {
            self::getInstance()->fail(ResponseCode::COMP_REWARD_INVALID_MONEY);
        }
        $params['describe']        = $params['describe'] ?? '';
        $payParams                 = [
            'channel'      => $params['channel'],
            'service_code' => $params['service_code'],
        ];
        $payParams['biz_order_no'] = PayService::makeBizOrderNO('RW');
        $payParams['title']        = '活动打赏';
        $payParams['detail']       = '活动打赏:' . $params['describe'];
        $payParams['total_fee']    = $params['reward_amount'];
        $payParams['open_id']      = $params['open_id'];
        $params['trade_no']        = $payParams['biz_order_no'];
        $data                      = vss_service()->getPublicForwardService()->rewardGive($params);
        if (!empty($data['id'])) {
            //打赏上报数据收集
            vss_service()->getBigDataService()->saveParams($params, $payParams['biz_order_no'], 'reward');
            $payParams['optional'] = 'reward';
            $data['pay_data']      = PayService::getPayment($payParams);
        } else {
            $data['pay_data'] = '';
        }


        return $data;
    }

    /**
     * 获取打赏排行榜
     * @param $params
     * @return mixed
     */
    public static function listRank($params)
    {
        $data                      = vss_service()->getPublicForwardService()->rewardListRank($params);

        if (!empty($data['list']) && is_array($data['list'])) {
            $list       = $data['list'];
            $accountIds = array_column($data['list'], 'rewarder_id');
            $userInfos  = vss_service()->getRoomService()->getUserInfosByAccountIds(
                $data['source_id'],
                $accountIds
            );
            foreach ($list as &$row) {
                $userInfo = !empty($userInfos[$row['rewarder_id']]) ? $userInfos[$row['rewarder_id']] : [];
                if ($userInfo) {
                    $row['rewarder_nickname'] = $userInfo['nickname'];
                    $row['rewarder_avatar']   = $userInfo['avatar'];
                } else {
                    $row['rewarder_nickname'] = '';
                    $row['rewarder_avatar']   = '';
                }
            }
            $data['list'] = $list;
            return $data;
        }
        return [];
    }

    /**
     * 获取打赏排行信息
     * @param $params
     * @return mixed
     */
    public static function rewarderRank($params)
    {
        $data                      = vss_service()->getPublicForwardService()->rewardRewarderRank($params);

        if (!empty($data['source_id']) && !empty($data['rewarder_id'])) {
            $userInfo = vss_service()->getRoomService()->getUserInfoByAccountId(
                $data['source_id'],
                $data['rewarder_id']
            );
            if (!empty($userInfo) && is_array($userInfo)) {
                $data['rewarder_nickname'] = $userInfo['nickname'];
                $data['rewarder_avatar']   = $userInfo['avatar'];
            } else {
                $data['rewarder_nickname'] = '';
                $data['rewarder_avatar']   = '';
            }
            return $data;
        }
        return [];
    }

    /**
     * 获取打赏记录
     * @param $params
     * @return mixed
     */
    public static function recordsGet($params)
    {
        return vss_service()->getPublicForwardService()->rewardRecordsGet($params);
    }

    /**
     * 设置打赏支付状态
     * @param $params
     * @return mixed
     */
    public static function payStatusSet($params)
    {
        //虚拟支付
        $fakePay = vss_config('pay.fakePay');
        if (!$fakePay) {
            $data = vss_service()->getPublicForwardService()->rewardPayStatusSet($params);
        } else {
            $data = PayService::getTradeNoCache($params['trade_no']);
            if (!empty($data)) {
                $data['pay_status'] = 1;
                $data['count'] = 1;
                $params['third_party_trade_no'] = $params['trade_no'];
            }
            vss_logger()->info('Rew_payStatusSet1', ['data'=>$data, 'params'=>$params]);
        }

        if (!empty($data) && is_array($data) && !empty($data['pay_status']) && $data['pay_status'] == 1) {
            $da=[
                'room_id'   => $data['source_id'],
                'amount'    => $data['reward_amount']*1000000,
                'source'    => 2,
                'trade_no'  => $params['third_party_trade_no'],
                'app_id'    => $data['app_id'],
                'channel'   => $params['channel']=='WEIXIN' ? 1 : 2,
                'account_id'=> $data['receiver_id'],
            ];
            vss_logger()->info('reward-order', ['data'=>$data, 'da'=>$da]);
            vss_model()->getOrderDetailModel()->create($da);
            vss_service()->getIncomeService()->saveIncome($da);

            //支出
            $out_da=[
                'room_id'   => $data['source_id'],
                'amount'    => $data['reward_amount'],
                'source'    => 2,
                'trade_no'  => $params['third_party_trade_no'],
                'app_id'    => $data['app_id'],
                'channel'   => $params['channel']=='WEIXIN' ? 1 : 2,
                'account_id'=> $data['rewarder_id'],
                'status' => 1
            ];
            vss_model()->getOrderDetailModel()->create($out_da);
            /*self::paymentSaaS([
                'source_id' => $data['source_id'],
                'amount'    => $data['reward_amount'],
                'event'     => 'REWARD',
                'trade_no'  => $params['third_party_trade_no'],
                'app_id'    => $data['app_id'],
                'channel'   => $params['channel'],
                'user_id'   => $data['rewarder_id'],
                'target_id' => strval($data['id'])
            ]);*/
            vss_service()->getPaasChannelService()->sendMessage($data['source_id'], [
                'type'               => 'reward_pay_success',
                'room_id'            => $data['source_id'],
                'reward_receiver_id' => $data['receiver_id'],
                'reward_describe'    => $data['describe'],
                'reward_amount'      => $data['reward_amount'],
                'reward_count'       => $data['count'],
                'rewarder_id'        => $data['rewarder_id'],
                'rewarder_nickname'  => $data['rewarder_nickname'],
                'rewarder_avatar'    => $data['rewarder_avatar']
            ]);
            return $data;
        }
        return false;
    }

    /**
     * 回调SaaS增加余额
     * @param $params
     * @return mixed
     */
    public static function paymentSaaS($params)
    {
        if (!empty($params['amount']) && $params['amount'] > 0) {
            $params['pay_type'] = self::PAY_TYPE[$params['channel']];
            return RedpacketService::getInstance()->requestSaaS('api/vss/sync/payment', $params);
        }
        return false;
    }
}
