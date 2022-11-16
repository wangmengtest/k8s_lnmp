<?php


namespace vhallComponent\gift\services;

use App\Constants\ResponseCode;
use Vss\Common\Services\WebBaseService;
use vhallComponent\pay\services\PayService;

class GiftService extends WebBaseService
{
    /**
     * 创建
     * @param $params
     * @return mixed
     *
     */
    public function create($params)
    {
        return vss_service()->getPublicForwardService()->giftAdd($params);
    }

    /**
     * 删除
     * @param $params
     * @return mixed
     *
     */
    public function delete($params)
    {
        return vss_service()->getPublicForwardService()->giftDelete($params);
    }

    /**
     * 更新
     * @param $params
     * @return mixed
     *
     */
    public function update($params)
    {
        return vss_service()->getPublicForwardService()->giftEdit($params);
    }

    /**
     * 列表
     * @param $params
     * @return mixed
     *
     */
    public function list($params)
    {
        $validator        = vss_validator($params, [
            'page'     => '',
            'pagesize' => '',
        ]);
        $page             = $params['page'] ?? 1;
        $pagesize         = $params['pagesize'] ?? 20;
        $params['offset'] = $pagesize * ($page - 1);
        $params['limit']  = $pagesize;
        $res              = empty($params['room_id']) ? vss_service()->getPublicForwardService()->giftList($params) : vss_service()->getPublicForwardService()->giftUsedList($params);

        return [
            'total'    => $res['count'],
            'page'     => $page,
            'pagesize' => $pagesize,
            'list'     => $res['list']
        ];
    }

    /**
     * 设置默认
     * @param $params
     * @return mixed
     *
     */
    public function mappingSave($params)
    {
        return vss_service()->getPublicForwardService()->giftMappingSave($params);
    }

    /**
     * 更新支付状态
     * @param $params
     * @return mixed
     *
     */
    public function setPayStatus($params)
    {
        //虚拟支付
        $fakePay = vss_config('pay.fakePay');
        if (!$fakePay) {
            $res = vss_service()->getPublicForwardService()->giftPayStatusSet($params);
        } else {
            $res = PayService::getTradeNoCache($params['trade_no']);
            if (!empty($res)) {
                $res['pay_status'] = 1;
                $params['third_party_trade_no'] = $params['trade_no'];
            }
            vss_logger()->info('Rew_payStatusSet1', ['data'=>$res, 'params'=>$params]);
        }

        if (!empty($res) && is_array($res) && !empty($res['pay_status']) && $res['pay_status'] == 1) {

            //收入
            $da=[
                'room_id'   => $res['source_id'],
                'amount'    => $res['price']*100,
                'source'    => 1,
                'trade_no'  => $params['third_party_trade_no'],
                'app_id'    => $res['app_id'],
                'channel'   => $params['channel']=='WEIXIN' ? 1 : 2,
                'account_id'=> $res['receiver_id'],
            ];
            vss_logger()->info('gift-order', ['data'=>$res, 'da'=>$da]);
            vss_model()->getOrderDetailModel()->create($da);
            vss_service()->getIncomeService()->saveIncome($da);

            //支出
            $out_da=[
                'room_id'   => $res['source_id'],
                'amount'    => $res['price'],
                'source'    => 1,
                'trade_no'  => $params['third_party_trade_no'],
                'app_id'    => $res['app_id'],
                'channel'   => $params['channel']=='WEIXIN' ? 1 : 2,
                'account_id'=> $res['gift_user_id'],
                'status' => 1
            ];
            vss_model()->getOrderDetailModel()->create($out_da);


            /*RewardService::paymentSaaS([
                'source_id' => $res['source_id'],
                'amount'    => $res['price'],
                'event'     => 'GIFT',
                'trade_no'  => $params['third_party_trade_no'],
                'app_id'    => $res['app_id'],
                'channel'   => $params['channel'],
                'user_id'   => $res['gift_user_id'],
                'target_id' => strval($res['gift_id'])
            ]);*/
            $msg = [
                'type'               => 'gift_send_success',
                'room_id'            => $res['source_id'],
                'gift_user_id'       => $res['gift_user_id'],
                'gift_user_nickname' => $res['gift_user_nickname'],
                'gift_user_avatar'   => $res['gift_user_avatar'],
                'gift_user_name'     => $res['gift_user_name'],
                'gift_user_phone'    => $res['gift_user_phone'],
                'gift_name'          => $res['name'],
                'gift_price'         => $res['price'],
                'gift_image_url'     => $res['image_url'],
                'gift_id'            => $res['gift_id'],
                'gift_receiver_id'   => $res['receiver_id'],
                'gift_creator_id'    => $res['creator_id'],
                'gift_numbers'       => !empty($res['numbers']) ? $res['numbers'] : 0,
            ];
            if (is_numeric($res['source_id']) && $res['source_id'] > 0) {
                $params = ['app_id'    => $res['app_id'],
                    'data'      => json_encode($msg),
                    'channel'   => $res['source_id'],
                    'type'      => 'gift_send_success',
                    'sender_id' => $res['gift_user_id']
                ];
                $messageService = vss_service()->getMessageService();
                $messageService::send($params);
            } else {
                vss_service()->getPaasChannelService()->sendMessage($res['source_id'], $msg);
            }
            return $res;
        }
        return false;
    }

    /**
     * 送礼物API接口
     * @param $params
     * @return mixed
     * @throws $error
     */
    public function sendApi($params)
    {
        $validator              = vss_validator($params, [
            'channel'      => 'required|in:ALIPAY,WEIXIN',
            'service_code' => 'required|in:QR_PAY,CASHIER,H5_PAY,JSAPI',
            'gift_id'      => 'required',
            'source_id'    => 'required',
            'receiver_id'  => 'required',
            'gift_user_id' => 'required',
            'open_id'      => 'required_if:service_code,JSAPI'
        ]);
        $params['receiver_id']  = $params['receiver_id'];
        $params['source_id']    = $params['source_id'];
        $params['trade_no']     = PayService::makeBizOrderNO('GF');
        $params['gift_user_id'] = $params['gift_user_id'];
        $res                    = vss_service()->getPublicForwardService()->giftSend($params);

        $totalFee = !is_null($res['price']) ? $res['price'] : 0;

        $payParams                 = [
            'channel'      => $params['channel'],
            'service_code' => $params['service_code'],
        ];
        $payParams['optional']     = 'gift';
        $payParams['biz_order_no'] = $params['trade_no'];
        $payParams['title']        = '送礼物';
        $payParams['detail']       = '送礼物';
        $payParams['total_fee']    = $totalFee;
        $payParams['open_id']      = $params['open_id'];
        //上报数据收集
        $params['price']=$payParams['total_fee'];//添加上报金额
        vss_service()->getBigDataService()->saveParams($params, $payParams['biz_order_no'], 'gift');

        $res['pay_data']   = PayService::getPayment($payParams);
        return $res;
    }

    /**
     * 礼物接口
     * @param $params
     * @return mixed
     * @throws $error
     */
    public function send($params)
    {
        vss_validator($params, [
            'channel'      => 'required|in:ALIPAY,WEIXIN',
            'service_code' => 'required|in:QR_PAY,CASHIER,H5_PAY,JSAPI',
            'gift_id'      => 'required',
            'room_id'      => 'required',
            'open_id'      => 'required_if:service_code,JSAPI',
            'numbers'      => '',
        ]);
        $room      = vss_model()->getRoomsModel()->findByRoomId($params['room_id']);
        empty($room) && $this->fail(ResponseCode::EMPTY_ROOM);
        $params['receiver_id']  = $room->account_id;
        $params['source_id']    = $room->room_id;
        $params['trade_no']     = PayService::makeBizOrderNO('GF');
        $params['gift_user_id'] = vss_service()->getTokenService()->getAccountId();
        if (!empty($params['numbers'])) {
            $params['numbers']=$params['numbers'];
        }
        $res                    = vss_service()->getPublicForwardService()->giftSend($params);
        vss_logger()->info('gift-test', ['data'=>$res, 'params'=>$params]);

        $totalFee = !is_null($res['price']) ? $res['price'] : 0;

        if (!empty($params['numbers'])) {
            $totalFee=$totalFee*$params['numbers'];
        }

        $payParams                 = [
            'channel'      => $params['channel'],
            'service_code' => $params['service_code'],
        ];
        $payParams['optional']     = 'gift';
        $payParams['biz_order_no'] = $params['trade_no'];
        $payParams['title']        = '送礼物';
        $payParams['detail']       = '送礼物';
        $payParams['total_fee']    = $totalFee;
        $payParams['open_id']      = $params['open_id'];
        //上报数据收集
        $params['price']=$payParams['total_fee'];//添加上报金额
        vss_service()->getBigDataService()->saveParams($params, $payParams['biz_order_no'], 'gift');
        $res['pay_data']   = PayService::getPayment($payParams);
        return $res;
    }

    /**
     * 列表
     * @param $params
     * @return mixed
     *
     */
    public function usedList($params)
    {
        $validator        = vss_validator($params, [
            'page'     => '',
            'pagesize' => '',
        ]);
        $page             = $params['page'] ?? 1;
        $pagesize         = $params['pagesize'] ?? 20;
        $params['offset'] = $pagesize * ($page - 1);
        $params['limit']  = $pagesize;
        $res              = vss_service()->getPublicForwardService()->giftUsedList($params);

        return [
            'total'    => $res['count'],
            'page'     => $page,
            'pagesize' => $pagesize,
            'list'     => $res['list']
        ];
    }
}
