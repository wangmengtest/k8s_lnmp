<?php

namespace vhallComponent\redpacket\services;

use App\Constants\ResponseCode;
use Vss\Utils\HttpUtil;
use Vss\Common\Services\WebBaseService;
use vhallComponent\pay\services\PayService;

class RedpacketService extends WebBaseService
{
    /**
     * 创建红包
     * @param $params
     * @return mixed
     * @throws $error
     */
    public static function create($params)
    {
        $validator        = vss_validator($params, [
            'channel'      => 'required|in:ALIPAY,WEIXIN',
            'service_code' => 'required|in:QR_PAY,CASHIER,H5_PAY,JSAPI',
            'number'       => 'required',
            'type'         => 'required',
            'amount'       => 'required',
            'source_id'    => 'required',
            'describe'     => '',
            'open_id' => 'required_if:service_code,JSAPI',
            'gift_type' => '',
        ]);
        $params['amount'] = $params['amount'];
        if ($params['amount'] < 0) {
            self::getInstance()->fail(ResponseCode::TYPE_INVALID_MONEY);
        }
        $params['start_time']      = date('Y-m-d H:i:s');
        $params['condition']       = 0;
        $params['push_channel']    = self::getchannelId($params['source_id']);
        if (!$params['push_channel']) {
            self::getInstance()->fail(ResponseCode::BUSINESS_CREATE_FAILED);
        }
        //礼包
        if (!empty($params['gift_type'])) {
            $data  = vss_service()->getPublicForwardService()->redPacketCreate($params);
            if ($data) {
                self::giftSetMsg($data);
                return $data;
            }
            return [];
        }

        $payParams                 = [
            'channel'      => $params['channel'],
            'service_code' => $params['service_code'],
        ];
        $payParams['biz_order_no'] = PayService::makeBizOrderNO('RP');
        $payParams['title']        = '活动红包';
        $payParams['detail']       = '活动红包:' . $params['describe'];
        $payParams['total_fee']    = $params['amount'];
        $payParams['open_id']      = $params['open_id'];
        $params['trade_no']        = $payParams['biz_order_no'];
        $data                      = vss_service()->getPublicForwardService()->redPacketCreate($params);
        if ($data) {
            if (!empty($data['red_packet_id'])) {
                $payParams['optional'] = 'red_packet';
                $data['pay_data']      = PayService::getPayment($payParams);
            } else {
                $data['pay_data'] = '';
            }
            return $data;
        }
        return [];
    }

    /**
     * 设置红包支付状态
     * @param $params
     * @return mixed
     */
    public static function payStatusSet($params)
    {
        //虚拟支付不走共享
        $ifPay = vss_config('pay.fakePay');
        if (!$ifPay) {
            $data  = vss_service()->getPublicForwardService()->redPacketPayStatusSet($params);
        } else {
            $data = PayService::getTradeNoCache($params['trade_no']);
            if (!empty($data)) {
                $data['pay_status'] = 1;
                $params['third_party_trade_no'] = $params['trade_no'];
            }
            vss_logger()->info('Red_payStatusSet1', ['data'=>$data, 'params'=>$params]);
        }
        vss_logger()->info('redpackss', ['desf'=>$data]);

        if (!empty($data) && is_array($data) && !empty($data['pay_status']) && $data['pay_status'] == 1) {
            $userInfo         = vss_service()->getRoomService()->getUserInfoByAccountId(
                $data['source_id'],
                $data['third_party_user_id']
            );
            $data['nickname'] = $userInfo['nickname'] ?? '';
            $data['avatar']   = $userInfo['avatar'] ?? '';
            vss_service()->getPaasChannelService()->sendMessage($data['source_id'], [
                'type'                  => 'red_envelope_push',
                'room_id'               => $data['source_id'],
                'red_packet_uuid'       => $data['red_packet_uuid'],
                'sender_id'             => $data['third_party_user_id'],
                'sender_nickname'       => $data['nickname'],
                'sender_avatar'         => $data['avatar'],
                'red_packet_describe'   => $data['describe'],
                'red_packet_number'     => $data['number'],
                'red_packet_amount'     => $data['amount'],
                'red_packet_type'       => $data['type'],
                'red_packet_start_time' => $data['start_time']
            ]);

            $da=[
                'room_id'   => $data['source_id'],
                'amount'    => $data['amount'],
                'source'    => 0,
                'trade_no'  => $params['third_party_trade_no'],
                'app_id'    => $params['app_id'],
                'channel'   => $params['channel']=='WEIXIN' ? 1 : 2,
                'account_id'=> $data['third_party_user_id'],
                'status' => 1
            ];

            vss_model()->getOrderDetailModel()->create($da);

            //增加红包聊天消息
            vss_service()->getPaasChannelService()->sendNotice($data['source_id'], [
                'room_id'               => $data['source_id'],
                'red_packet_describe'   => $data['describe'],
                'red_status'            => $data['pay_status'],
                'red_packet_uuid'       => $data['red_packet_uuid'],
            ], $data['third_party_user_id'], 'redpacket');
            return $data;
        }
        return false;
    }

    /**
     * 获取红包列表
     * @param $params
     * @return mixed
     */
    public static function getList($params)
    {
        return vss_service()->getPublicForwardService()->redPacketGetList($params);
    }

    /**
     * 获取红包我的领取详情
     * @param $params
     * @return mixed
     */
    public static function getRecord($params)
    {
        return vss_service()->getPublicForwardService()->redPacketGetRecord($params);
    }

    /**
     * 获取红包信息
     * @param $params
     * @return mixed
     */
    public static function getInfo($params)
    {
        return vss_service()->getPublicForwardService()->redPacketGetInfo($params);
    }

    /**
     * 抢红包
     * @param $params
     * @return mixed
     */
    public static function get($params)
    {
        try {
            $result = vss_service()->getPublicForwardService()->redPacketGet($params);
            vss_logger()->info('red-first', ['params'=>$params, 'result'=>$result]);
        } catch (\Exception $exception) {
            $result = ['red_packet' => self::getInfo($params)];
            vss_logger()->info('red-guo', ['params'=>$params, 'result'=>$result]);
            if ($result && !empty($result['red_packet']['valid_time'] && $result['red_packet']['valid_time']<0)) {
                vss_service()->getPaasChannelService()->updateRedpacketStatus($result['red_packet']['red_packet_uuid'], 4);
                self::getInstance()->fail(ResponseCode::COMP_RED_PACKET_EXPIRE);
            }

            if ($exception->getCode() == 110014) {
                $result = ['red_packet' => self::getInfo($params)];
                return  self::getInstance()->fail($exception->getCode(), $exception->getMessage());
            }
            self::getInstance()->fail($exception->getCode(), $exception->getMessage());
        }
        if (!empty($result['red_packet']['third_party_user_id'])) {
            $userInfo                         = vss_service()->getRoomService()->getUserInfoByAccountId(
                $result['red_packet']['source_id'],
                $result['red_packet']['third_party_user_id']
            );
            $result['red_packet']['nickname'] = $userInfo['nickname'] ?? '';
            $result['red_packet']['avatar']   = $userInfo['avatar'] ?? '';
            $result['status']                 = !empty($result['get_red_packet_record_id']) ? 1 : 0;

            $inComeInsert=[
                'amount'    => $result['amount'],
                'app_id'    => $result['app_id'],
                'account_id'=> $result['third_party_user_id'],
            ];
            vss_service()->getIncomeService()->saveIncome($inComeInsert);

            $orderInsert=[
                'room_id'   => $result['red_packet']['source_id'],
                'amount'    => $result['amount'],
                'source'    => 0,
                'trade_no'  => $result['red_packet']['trade_no'],
                'app_id'    => $result['app_id'],
                'account_id'=> $result['third_party_user_id'],
            ];
            vss_model()->getOrderDetailModel()->create($orderInsert);
        }
        if (!empty($result['amount']) && $result['amount'] > 0 && !empty($result['red_packet']['red_packet_id'])) {
            $redPacket = $result['red_packet'];
            $params = [];
            $params['redpacket_id']    = $redPacket['red_packet_id'];
            $params['source_id']       = $redPacket['source_id'];
            $params['money']           = $result['amount'];
            $params['sender_id']       = $redPacket['third_party_user_id'];
            $params['receive_user_id'] = $result['third_party_user_id'];
            $params['redpacket_uuid']  = $redPacket['red_packet_uuid'];
            $params['redpacket_type']  = $redPacket['type'];
            $params['app_id']          = $redPacket['app_id'];
            //  self::getRedPacketBalance($params);
        }
        return $result;
    }

    /**
     * 获取抢红包记录列表
     * @param $params
     * @return mixed
     */
    public static function recordsGet($params)
    {
        unset($params['source_id']);
        $result = vss_service()->getPublicForwardService()->redPacketGetRecords($params);
        if (!empty($result['list']) && is_array($result['list'])) {
            $accountIds = array_unique(array_column($result['list'], 'third_party_user_id'));
            $roomId     = !empty($result['list'][0]['source_id']) ? $result['list'][0]['source_id'] : '';
            $userInfos  = vss_service()->getRoomService()->getUserInfosByAccountIds($roomId, $accountIds);
            foreach ($result['list'] as &$row) {
                $row['nickname'] = !empty($userInfos[$row['third_party_user_id']]['nickname']) ? $userInfos[$row['third_party_user_id']]['nickname'] : '';
                $row['avatar']   = !empty($userInfos[$row['third_party_user_id']]['avatar']) ? $userInfos[$row['third_party_user_id']]['avatar'] : '';
            }
        }
        return $result;
    }

    /**
     * 根据来源ID结束红包并退还未领取的红包
     * @param $params
     * @return mixed
     */
    public static function overBySourceId($params)
    {
        try {
            $list = vss_service()->getPublicForwardService()->redPacketOverBySourceId($params);
            if (!empty($list) && is_array($list)) {
                foreach ($list as $redPacket) {
                    self::refundRedPacketBalance($redPacket);
                }
            }
        } catch (\Exception $exception) {
            vss_logger()->info(
                'overBySourceIdError',
                ['code' => $exception->getCode(), 'msg' => $exception->getMessage()]
            );
        }
    }

    /**
     * 领取红包增加红包余额
     * @param $params
     * @return mixed
     */
    public static function getRedPacketBalance($params)
    {
        if (!empty($params['money']) && $params['money'] > 0) {
            return self::getInstance()->requestSaaS('api/vss/sync/redpacket', $params);
        }
        return false;
    }

    /**
     * 退还未领取的红包
     * @param $redPacket
     * @return mixed
     */
    public static function refundRedPacketBalance($redPacket)
    {
        if (!empty($redPacket['refund_amount']) && $redPacket['refund_amount'] > 0) {
            $params                    = [];
            $params['redpacket_id']    = $redPacket['red_packet_id'];
            $params['source_id']       = $redPacket['source_id'];
            $params['money']           = $redPacket['refund_amount'];
            $params['sender_id']       = $redPacket['third_party_user_id'];
            $params['receive_user_id'] = $redPacket['third_party_user_id'];
            $params['redpacket_uuid']  = $redPacket['red_packet_uuid'];
            $params['redpacket_type']  = $redPacket['type'];
            $params['app_id']          = $redPacket['app_id'];
            return self::getRedPacketBalance($params);
        }
        return false;
    }

    /**
     * 红包服务应用信息设置接口
     * @param $params
     * @return mixed
     */
    public static function setting($params)
    {
        $validator        = vss_validator($params, [
            'valid_timeout'      => 'required',
            'pay_callback_url'     => '',
            'get_red_packet_callback_url' => '',
            'private_key' => '',
        ]);
        return vss_service()->getPublicForwardService()->redPacketSetting($params);
    }

    /**
     * 发送请求到SaaS服务
     * @param string $uri
     * @param array $params
     * @return mixed
     * @throws
     */
    public function requestSaaS(string $uri, array $params = [])
    {
        $params = self::makeCallbackParams($params);
        $response = HttpUtil::post(vss_config('saas.domain') . '/' . $uri, $params, null, 20);
        if ($response->getCode() != 200) {
            $this->fail($response->getCode(), $response->getMessage());
        }
        $data = $response->getData();
        if ($data['code'] != 200) {
            $this->fail($data['code'], $data['msg']);
        }
        return $data['data'];
    }

    /**
     * 生成带回调地址签名的请求参数
     * @param array $params
     * @return mixed
     * @throws
     */
    public function makeCallbackParams($params)
    {
        if (!empty($params['app_id'])) {
            $params['random']    = mt_rand(1000, 9999);
            $params['signed_at'] = microtime(true);
            $params['sign']      = vss_paas_util()->sign(
                $params,
                vss_paas_util()->getPaasAppSecretByAppId($params['app_id'])
            );
            return $params;
        }
        return [];
    }

    /**
     * @param $param
     * @return false|\vhallComponent\room\models\RoomsModel|null
     */
    private static function getchannelId($room_id)
    {
        $res=vss_service()->getRoomService()->get($room_id)->toArray();
        if ($res) {
            return $res['channel_id'] ? $res['channel_id'] : null;
        }
        return null;
    }

    /**
     * 设置礼包消息
     * @param $params
     * @return mixed
     */
    public static function giftSetMsg($data)
    {
        if (!empty($data) && is_array($data)) {
            $userInfo         = vss_service()->getRoomService()->getUserInfoByAccountId(
                $data['source_id'],
                $data['third_party_user_id']
            );
            $data['nickname'] = $userInfo['nickname'] ?? '';
            $data['avatar']   = $userInfo['avatar'] ?? '';
            vss_service()->getPaasChannelService()->sendMessage($data['source_id'], [
                'type'                  => 'gift_packet_push',
                'room_id'               => $data['source_id'],
                'red_packet_uuid'       => $data['red_packet_uuid'],
                'sender_id'             => $data['third_party_user_id'],
                'sender_nickname'       => $data['nickname'],
                'sender_avatar'         => $data['avatar'],
                'red_packet_describe'   => $data['describe'],
                'red_packet_number'     => $data['number'],
                'red_packet_amount'     => $data['amount'],
                'red_packet_type'       => $data['type'],
                'red_packet_start_time' => $data['start_time']
            ]);
            //增加红包聊天消息
            vss_service()->getPaasChannelService()->sendNotice($data['source_id'], [
                'room_id'               => $data['source_id'],
                'red_packet_describe'   => $data['describe'],
                'red_status'            => 1,
                'red_packet_uuid'       => $data['red_packet_uuid'],
            ], $data['third_party_user_id'], 'redpacket');
            return true;
        }
        return false;
    }
}
