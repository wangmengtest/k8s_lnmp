<?php

namespace vhallComponent\sign\services;

use Vss\Common\Services\WebBaseService;

class SignService extends WebBaseService
{
    /**
     * 发起签到
     * @param $params
     * @return mixed
     */
    public static function add($params)
    {
        $data = vss_service()->getPublicForwardService()->signAdd($params);
        if ($data) {
            vss_service()->getPaasChannelService()->sendMessage($params['source_id'], [
                'type'                  => 'sign_in_push',
                'room_id'               => $data['source_id'],
                'sign_id'               => $data['id'],
                'sign_show_time'        => $data['show_time'],
                'sign_creator_id'       => $data['creator_id'],
                'sign_creator_avatar'   => $data['creator_avatar'],
                'sign_creator_nickname' => $data['creator_nickname']
            ]);
            return $data;
        }
        return [];
    }

    /**
     * 用户签到
     * @param $params
     * @return mixed
     */
    public static function in($params)
    {
        $data = vss_service()->getPublicForwardService()->signIn($params);
        if (!empty($data) && is_array($data)) {
            vss_service()->getRoomService()->signedUserInfo($params['source_id'], $params['signer_id']);
        }
        return $data;
    }

    /**
     * 获取签到记录列表
     * @param $params
     * @return mixed
     */
    public static function recordsGet($params)
    {
        return vss_service()->getPublicForwardService()->signRecordsGet($params);
    }

    /**
     * 获取发起的签到列表
     * @param $params
     * @return mixed
     */
    public static function gets($params)
    {
        return vss_service()->getPublicForwardService()->signGets($params);
    }
}
