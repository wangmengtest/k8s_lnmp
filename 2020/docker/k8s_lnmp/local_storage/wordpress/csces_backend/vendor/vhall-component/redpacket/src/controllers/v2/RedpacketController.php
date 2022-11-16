<?php

namespace vhallComponent\redpacket\controllers\v2;

use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;
use vhallComponent\redpacket\services\RedpacketService;

/**
 *+----------------------------------------------------------------------
 * @file SignController.php
 * @date 2019-06-19 22:51:00
 *+----------------------------------------------------------------------
 */

/**
 *+----------------------------------------------------------------------
 * Class RedpacketController
 * 红包控制器
 *+----------------------------------------------------------------------
 *
 * @author  yi.yang@vhall.com
 * @date    2019-06-19 22:51:00
 * @link    http://yapi.vhall.domain/project/21/interface/api/cat_580
 * @version v1.0.0
 *+----------------------------------------------------------------------
 */
class RedpacketController extends BaseController
{
    protected $userInfo;

    protected $roomInfo;

    public function init()
    {
        parent::init();

        $param = vss_validator($this->getParam(), [
            'room_id'             => 'required',
            'third_party_user_id' => 'required'
        ]);

        $this->roomInfo = vss_service()->getRoomService()->getRoomInfoByRoomId($param['room_id']);
        if (!$this->roomInfo || !is_array($this->roomInfo)) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }

        $this->userInfo = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId(
            $param['third_party_user_id'],
            $param['room_id']
        );

        if (empty($this->userInfo)) {
            $this->fail(ResponseCode::EMPTY_USER);
        }
    }

    /**
     * 创建并发起红包
     */
    public function createAction()
    {
        $param              = $this->getParam();
        $param['source_id'] = $param['room_id'];
        $data               = RedpacketService::create($param);
        if (!empty($data) && is_array($data)) {
            $data['nickname'] = $this->userInfo['nickname'];
            $data['avatar']   = $this->userInfo['avatar'];
        }
        $this->success($data);
    }

    /**
     * 用户抢红包
     */
    public function getAction()
    {
        $param                  = $this->getParam();
        $param['source_id']     = $param['room_id'];
        $param['red_packet_id'] = $param['red_packet_uuid'];
        $data                   = RedpacketService::get($param);

        if (!empty($data) && is_array($data)) {
            $data['nickname'] = $this->userInfo['nickname'];
            $data['avatar']   = $this->userInfo['avatar'];
        }
        //红包上报
        if (!empty($data) && is_array($data)) {
            vss_service()->getBigDataService()->requestRedPacketParams($param, $data, false);
        }
        if ($data['status'] == 1) {
            $redPacket = $data['red_packet'];

            $msgData = [
                'type'                      => 'red_envelope_open_success',
                'room_id'                   => $data['source_id'],
                'red_packet_uuid'           => $redPacket['red_packet_uuid'],
                'sender_id'                 => $redPacket['third_party_user_id'],
                'sender_nickname'           => $redPacket['nickname'],
                'sender_avatar'             => $redPacket['avatar'],
                'red_packet_describe'       => $redPacket['describe'],
                'red_packet_type'           => $redPacket['type'],
                'red_packet_start_time'     => $redPacket['start_time'],
                'red_packet_number'         => $redPacket['number'],
                'red_packet_get_user_count' => $redPacket['get_user_count'],
                'red_packet_amount'         => $redPacket['amount'],
                'red_packet_status'         => $redPacket['get_user_count'] >= $redPacket['number'] ? 0 : 1,
                'receiver_id'               => $data['third_party_user_id'],
                'receiver_nickname'         => $data['nickname'],
                'receiver_avatar'           => $data['avatar'],
                'receiver_amount_ranking'   => $data['amount_ranking'],
                'receiver_amount'           => $data['amount'],
                'receiver_percent'          => $data['percent'],
                'gift_type'                 => $redPacket['gift_type']
            ];

            vss_service()->getPaasChannelService()->sendMessage($data['source_id'], $msgData);
            //红包上报
            if (!empty($data) && is_array($data)) {
                vss_service()->getBigDataService()->requestRedPacketParams($param, $data, true);
            }
            $status = $redPacket['get_user_count'] >= $redPacket['number'] ? 3 : 2;
            vss_service()->getPaasChannelService()->updateRedpacketStatus($redPacket['red_packet_uuid'], $status);
            /*vss_service()->getPaasChannelService()->sendNotice($data['source_id'], [
                'room_id'               => $data['source_id'],
                'red_packet_describe'   => $data['describe'],
                'red_packet_uuid'       => $redPacket['red_packet_uuid'],
                'red_status'            => $redPacket['get_user_count'] >= $redPacket['number'] ? 0 : 2,
            ], $data['third_party_user_id'],'redpacket');*/
        }

        $this->success($data);
    }

    /**
     * 获取我领取的一个红包信息
     */
    public function getMyInfoAction()
    {
        $param                  = $this->getParam();
        $param['source_id']     = $param['room_id'];
        $param['red_packet_id'] = $param['red_packet_uuid'];
        $redPacketInfo          = RedpacketService::getInfo($param);
        if (!empty($redPacketInfo) && is_array($redPacketInfo)) {
            $userInfo                  = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId($redPacketInfo['third_party_user_id'],
                $redPacketInfo['source_id']);
            $redPacketInfo['nickname'] = $userInfo['nickname'] ?? '';
            $redPacketInfo['avatar']   = $userInfo['avatar'] ?? '';
        }
        $data = RedpacketService::getRecord($param);
        if (!empty($data) && is_array($data)) {
            $data['nickname']   = $this->userInfo['nickname'];
            $data['avatar']     = $this->userInfo['avatar'];
            $data['status']     = 1;
            $data['red_packet'] = $redPacketInfo;
            $this->success($data);
        } else {
            $this->success(['status' => 0, 'red_packet' => $redPacketInfo]);
        }
    }

    /**
     * 获取最新的一个红包信息
     */
    public function getLastInfoAction()
    {
        $param               = $this->getParam();
        $param['source_id']  = $param['room_id'];
        $param['page']       = 1;
        $param['page_size']  = 1;
        $param['sort_type']  = 'desc';
        $param['pay_status'] = 1;
        $list                = RedpacketService::getList($param);
        if (!empty($list['list'][0]) && is_array($list['list'][0])) {
            $data             = $list['list'][0];
            $userInfo         = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId($data['third_party_user_id'],
                $data['source_id']);
            $data['nickname'] = $userInfo['nickname'] ?? '';
            $data['avatar']   = $userInfo['avatar'] ?? '';
            $data['status']   = !empty($data['valid_time']) && $data['valid_time'] > 0 && empty($data['refund_status']) ? 1 : 2;
            $this->success($data);
        } else {
            $this->success(['status' => 0]);
        }
    }

    /**
     * 获取红包信息接口
     */
    public function getInfoAction()
    {
        $param                  = $this->getParam();
        $param['source_id']     = $param['room_id'];
        $param['red_packet_id'] = $param['red_packet_uuid'];
        $data                   = RedpacketService::getInfo($param);
        if (!empty($data) && is_array($data)) {
            $data['nickname'] = $this->userInfo['nickname'];
            $data['avatar']   = $this->userInfo['avatar'];
        }
        $this->success($data);
    }

    /**
     * 获取抢红包记录列表
     */
    public function recordsGetAction()
    {
        $param                  = $this->getParam();
        $param['source_id']     = $param['room_id'];
        $param['red_packet_id'] = $param['red_packet_uuid'];
        //默认
        !$param['order'] && $param['order'] = 'created_at';
        $data = RedpacketService::recordsGet($param);
        if (!empty($data) && is_array($data)) {
            $redPacketInfo = RedpacketService::getInfo($param);
            if (!empty($redPacketInfo) && is_array($redPacketInfo)) {
                $userInfo                  = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId($redPacketInfo['third_party_user_id'],
                    $redPacketInfo['source_id']);
                $redPacketInfo['nickname'] = $userInfo['nickname'] ?? '';
                $redPacketInfo['avatar']   = $userInfo['avatar'] ?? '';
            }
            $data['red_packet'] = $redPacketInfo;
        }
        $this->success($data);
    }

    /**
     * 红包信息设置
     */
    public function settingAction()
    {
        $param = $this->getParam();
        $data  = RedpacketService::setting($param);
        $this->success($data);
    }
}
