<?php
/**
 * Created by PhpStorm.
 * User: zhangxz
 * Date: 2018/9/4
 * Time: 上午11:03
 */

namespace vhallComponent\paas\services;

use App\Constants\ResponseCode;
use Vss\Exceptions\PaasException;
use Vss\Exceptions\ValidationException;
use Vss\Utils\HttpUtil;
use Vss\Common\Services\WebBaseService;
use vhallComponent\paas\constants\BooleanConstant;

class PaasChannelServices extends WebBaseService
{
    /**
     * PAAS服务接口地址
     *
     * @var array|null|string
     */
    private $host = '';

    /**
     * PAAS服务应用ID
     *
     * @var array|null|string
     */
    private $appId = '';

    /**
     * PAAS服务应用秘钥
     *
     * @var array|null|string
     */
    private $secretKey = '';

    /**
     * PaasServiceImpl constructor.
     */
    public function __construct()
    {
        $this->host      = vss_config('paas.host');
        $this->appId     = $_REQUEST['app_id'] ?? vss_service()->getTokenService()->getAppId();
        $this->secretKey = vss_paas_util()->getPaasAppSecretByAppId($this->appId);
    }

    /**
     * 发送PAAS请求
     *
     * @param string $uri
     * @param array  $params
     *
     * @return mixed
     */
    public function request(string $uri, array $params = [])
    {
        $params   = vss_paas_util()->generateParams($params, $this->appId, $this->secretKey);
        $response = HttpUtil::post($this->host . $uri, $params, null, 20);
        if ($response->getCode() != 200) {
            throw new PaasException($response->getCode(), $response->getMessage());
        }
        $data = $response->getData();
        if ($data['code'] != 200) {
            throw new PaasException($data['code'], $data['msg']);
        }
        return $data['data'];
    }

    /**
     * @inheritdoc
     */
    public function getOnlineNum($activityId)
    {
        $activity = $this->getActivity($activityId);
        return $this->getOnlineNumByChannel($activity->channel_id);
    }

    /**
     * @inheritdoc
     */
    public function getOnlineNumByChannel($channel)
    {
        $url      = vss_config('paas.host') . '/api/v2/channel/user-online-count';
        $param    = vss_paas_util()->generateParams([
            'channel_id' => $channel,
        ], $this->appId, $this->secretKey);
        $response = HttpUtil::post($url, $param);
        $data     = $response->getData();
        if (!empty($data['code']) && $data['code'] == 200) {
            return empty($data['data']['count']) ? 0 : $data['data']['count'];
        }
        return 0;
    }

    /**
     * @inheritdoc
     */
    public function sendMessage($activityId, $body)
    {
        $activity = vss_model()->getRoomsModel()->findByRoomId($activityId);
        return $this->sendMessageByChannel($activity->channel_id, $body);
    }

    /**
     * 发送通知消息
     *
     * @param $activityId
     * @param $body
     *
     * @return int|null
     */
    public function sendNotifyMessage($activityId, $body)
    {
        $activity = vss_model()->getRoomsModel()->findByRoomId($activityId);
        return $this->sendMessageByChannel($activity->nify_channel, $body);
    }

    /**
     * @inheritdoc
     */
    public function sendMessageByChannel(
        $channel,
        $body,
        $thirdUserId = null,
        $type = 'service_room',
        $client = 'pc_browser',
        $isCache = 1
    ) {
        $url     = vss_config('paas.host') . '/api/v2/message/send';
        $request = [
            'channel_id' => $channel,
            'type'       => $type,
            'client'     => $client,
            'body'       => json_encode($body),
            'is_cache'   => $isCache
        ];
        if ($thirdUserId) {
            $request['third_party_user_id'] = $thirdUserId;
        }
        $param = vss_paas_util()->generateParams($request, $this->appId, $this->secretKey);

        $response = HttpUtil::post($url, $param);
        $data     = $response->getData();
        if (!empty($data['code']) && $data['code'] == 200) {
            return $data['data']['count'] ?? 0;
        }
        vss_logger()->info('message_error', compact('url', 'param', 'data', 'response'));

        return null;
    }

    public function sendChatMessage($activityId, $thirdUserId, $body)
    {
        $activity = vss_model()->getRoomsModel()->findByRoomId($activityId);
        return $this->sendChatMessageByChannel($activity->channel_id, $thirdUserId, $body);
    }

    /**
     * @inheritdoc
     */
    public function sendChatMessageByChannel($channel, $thirdUserId, $body)
    {
        $url     = vss_config('paas.host') . '/api/v2/message/send';
        $request = [
            'channel_id'          => $channel,
            'type'                => 'service_im',
            'third_party_user_id' => $thirdUserId,
            'client'              => 'pc_browser',
            'body'                => json_encode($body)
        ];
        $param   = vss_paas_util()->generateParams($request, $this->appId, $this->secretKey);

        $response = HttpUtil::post($url, $param);
        $data     = $response->getData();
        if (!empty($data['code']) && $data['code'] == 200) {
            return $data['data']['count'] ?? 0;
        }
        return null;
    }

    /**
     * 设置第三方用户信息
     *
     * @param $thirdPartUserId
     * @param $nickname
     * @param $avatar
     *
     * @return mixed
     */
    public function saveUserInfo($thirdPartUserId, $nickname, $avatar = 'default.jpg')
    {
        return $this->request(
            '/api/v2/channel/save-user-info',
            ['third_party_user_id' => $thirdPartUserId, 'nick_name' => $nickname, 'avatar' => $avatar,]
        );
    }

    /**
     * 检查用户是否在线
     *
     * @param $activityId
     * @param $activityUserIds
     *
     * @return array
     */
    public function checkUserOnline($activityId, $activityUserIds)
    {
        $activity = $this->getActivity($activityId);
        return $this->checkUserOnlineByChannel($activity->channel_id, $activityUserIds);
    }

    /**
     * 检查用户是否在线
     *
     * @param $channel
     * @param $activityUserIds
     *
     * @return array
     */
    public function checkUserOnlineByChannel($channel, $activityUserIds)
    {
        $url   = vss_config('paas.host') . '/api/v2/channel/check-user-online';
        $param = vss_paas_util()->generateParams([
            'third_party_user_ids' => implode(',', $activityUserIds),
            'channel_id'           => $channel
        ], $this->appId, $this->secretKey);

        $response = HttpUtil::post($url, $param);
        $data     = $response->getData();
        if (empty($data['code']) && $data['code'] != 200) {
            return [];
        }
        $retData = $data['data']['connections'] ?? [];
        foreach ($retData as & $val) {
            $val = ($val >= 1) ? BooleanConstant::YES : BooleanConstant::NO;
        }
        return $retData;
    }

    /**
     * @inheritdoc
     */
    public function getMessageCountAndUserCount($activityId)
    {
        $url      = vss_config('paas.host') . '/api/v2/channel/send-message-stat';
        $activity = $this->getActivity($activityId);
        $param    = vss_paas_util()->generateParams([
            'channel_id' => $activity->channel_id
        ], $this->appId, $this->secretKey);
        $response = HttpUtil::post($url, $param);
        $data     = $response->getData();
        if (empty($data['code']) && $data['code'] != 200) {
            return [];
        }
        return $data['data'];
    }

    public function getActivity($activityId)
    {
        $activity = vss_model()->getRoomsModel()->findByRoomId($activityId);
        !$activity->channel_id && $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        return $activity;
    }

    /**
     * 获取历史消息
     *
     * @param $params
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function getMessageLists($params)
    {
        if (!empty($params)) {
            $url      = vss_config('paas.host') . '/api/v2/message/lists';
            $params   = vss_paas_util()->generateParams($params, $this->appId, $this->secretKey);
            $response = HttpUtil::post($url, $params);
            $data     = $response->getData();
            if (empty($data['code']) && $data['code'] != 200) {
                return [];
            }
            return $data['data'] ?? [];
        }
        return [];
    }

    /**
     * 发送公告
     *
     * @param     $activityId
     * @param     $content
     * @param     $accountId
     * @param int $type
     *
     * @return int|mixed|void|null
     */
    public function sendNotice($activityId, $content, $accountId, $type = 'text')
    {
        if ($type == 'redpacket') {
            $red_packet_uuid = $content['red_packet_uuid'];
        }
        $content   = json_encode(['type' => $type, 'content' => $content], JSON_UNESCAPED_UNICODE);
        $createArr = [
            'room_id'    => $activityId,
            'account_id' => $accountId,
            'content'    => $content
        ];
        if ($type == 'redpacket') {
            $createArr['type']            = 1;
            $createArr['red_packet_uuid'] = empty($red_packet_uuid) ? '' : $red_packet_uuid;
            return vss_model()->getNoticeModel()->create($createArr);
        }
        vss_model()->getNoticeModel()->create($createArr);
        return $this->sendMessage($activityId, [
            'type'                   => 'room_announcement',
            'room_join_id'           => $accountId, //参会id
            'room_announcement_text' => $content, //内容
            'push_time'              => date('Y-m-d H:i:s'), //推送时间
        ]);
    }

    /**
     * 获取待审核消息列表
     *
     * @param $params
     *
     * @return mixed
     */
    public function auditLists($params)
    {
        return $this->request('/api/v2/message/get-chat-audit-lists', $params);
    }

    /**
     * 设置审核开关接口
     *
     * @param $params
     *
     * @return mixed
     */
    public function setChannelSwitch($params)
    {
        return $this->request('/api/v2/channel/set-channel-switch', $params);
    }

    /**
     * 获取审核开关状态接口
     *
     * @param $params
     *
     * @return mixed
     */
    public function getChannelSwitch($params)
    {
        return $this->request('/api/v2/channel/get-channel-switch', $params);
    }

    /**
     * 设置是否自动处理聊天数据接口（switch开启能发,不能收,会转到审核频道）
     *
     * @param $params
     *
     * @return mixed
     */
    public function setChannelSwitchOptions($params)
    {
        return $this->request('/api/v2/channel/set-channel-switch-options', $params);
    }

    /**
     * 审核消息操作
     *
     * @param $params
     *
     * @return mixed
     */
    public function applyMessageSend($params)
    {
        return $this->request('/api/v2/message/apply-message-send', $params);
    }

    /**
     * 修改红包状态
     *
     * @param $red_packet_uuid
     * @param $status
     */
    public function updateRedpacketStatus($red_packet_uuid, $status)
    {
        $result = vss_model()->getNoticeModel()->where(['red_packet_uuid' => $red_packet_uuid])->first();
        if ($result) {
            $content                          = json_decode($result->content, true);
            $content['content']['red_status'] = $status;
            $content                          = json_encode($content, JSON_UNESCAPED_UNICODE);
            vss_model()->getNoticeModel()->where(['red_packet_uuid' => $red_packet_uuid])->update(['content' => $content]);
        }
    }

    /**
     * 获取在线连接数
     * @see http://www.vhallyun.com/docs/show/1338
     *
     * @param $channelId
     *
     * @return array
     * @throws \Exception
     */
    public function maxConnectionCount($channelId)
    {

        //1、地址
        $address = '/api/v2/channel/connection-count';

        //2、参数信息
        $data = [
            'channel_id' => $channelId
        ];
        return $this->request($address, $data);
    }

    /**
     * 获取在线连接数，一次最多50个
     * @see http://www.vhallyun.com/docs/show/1337
     *
     * @param
     */
    public function connectionCount($channelIds)
    {

        //1、地址
        $address = '/api/v2/das/get-channel-batch-user-online-count';

        //2、参数信息
        $params = [
            'channel_ids' => $channelIds
        ];

        //3、获取数据
        return $this->request($address, $params);
    }

    /**
     * 消息 V3 接口
     * 禁言/取消禁言接口
     * @auther yaming.feng@vhall.com
     * @date 2021/5/25
     *
     * @param string $roomId   房间 ID
     * @param string $type     类型， disable: 禁言一个用户， disable_all: 禁言所有用户， permit: 恢复一个用户， permit_all: 恢复所有用户
     * @param int    $userId   发起者 ID
     * @param int    $targetId 被禁言者 ID
     *
     * @return mixed
     * @throws \Exception
     */
    public function setSpeak($roomId, $type, $userId, $targetId = 0)
    {
        $types = [
            'disable', // 禁言某个用户， $targetId 必须传,
            'permit', // 解除用户的禁言状态， $targetId 必须传
            'disable_all', // 禁言整个频道的聊天， 全员禁言
            'permit_all', // 取消频道的禁言
        ];

        if (!in_array($type, $types)) {
            throw new \Exception('类型不存在');
        }

        $roomInfo = $this->getRoomsModel()->findByRoomId($roomId);

        $address = '/api/v2/channel/set-channel';

        $params = [
            'channel_id'          => $roomInfo->channel_id,
            'type'                => $type,
            'third_party_user_id' => $userId,
            'target_id'           => $targetId
        ];

        return $this->request($address, $params);
    }
}
