<?php


namespace vhallComponent\invitecard\services;

use vhallComponent\room\constants\CachePrefixConstant;
use vhallComponent\room\constants\InavGlobalConstant;
use Vss\Common\Services\WebBaseService;

class InvitecardService extends WebBaseService
{
    /**
     * 创建
     * @param $params
     * @return \vhallComponent\invitecard\models\InviteCardModel
     */
    public function create($params)
    {
        vss_validator($params, [
            'room_id'           => 'required',
            'is_show_watermark' => '',
        ]);
        return vss_model()->getInviteCardModel()->create($params);
    }

    /**
     * 更新
     * @param $params
     * @return int|mixed
     *
     */
    public function update($params)
    {
        vss_validator($params, [
            'room_id'           => 'required',
            'title'             => '',
            'company'           => '',
            'date'              => '',
            'location'          => '',
            'desciption'        => '',
            'show_type'         => '',
            'img_type'          => '',
            'is_show_watermark' => '',
            'img'               => '',
            'welcome_txt'       => '',

        ]);
        $updateArr = [
            'title'             => $params['title'] ?? '',
            'company'           => $params['company'] ?? '',
            'date'              => $params['date'] ?? date('Y-m-d H:i:s'),
            'location'          => $params['location'] ?? '',
            'desciption'        => $params['desciption'] ?? '',
            'show_type'         => $params['show_type'] ?? 1,
            'img_type'          => $params['img_type'] ?? 1,
            'is_show_watermark' => $params['is_show_watermark'] ?? 0,
            'img'               => $params['img'] ?? '',
            'welcome_txt'       => $params['welcome_txt'] ?? '',
        ];
        return vss_model()->getInviteCardModel()->where(['room_id' => $params['room_id']])->update($updateArr);
    }

    /**
     * 信息
     * @param $roomId
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|mixed|object|null
     */
    public function info($roomId)
    {
        return vss_model()->getInviteCardModel()->where(['room_id' => $roomId])->first();
    }

    /**
     * 设置邀请卡状态
     * @param $room_id
     * @param $status
     * @return int|mixed
     * @throws \Exception
     */
    public function setInviteCard($room_id, $status)
    {
        return vss_redis()->hset(CachePrefixConstant::INTERACT_GLOBAL . $room_id, InavGlobalConstant::OPEN_INVITE_CARD, (int)$status);
    }

    /**
     * 获取邀请卡状态
     * @param $room_id
     * @return int
     * @throws \Exception
     */
    public function getStatus($room_id)
    {
        return (int)vss_redis()->hget(CachePrefixConstant::INTERACT_GLOBAL . $room_id, InavGlobalConstant::OPEN_INVITE_CARD);
    }

    public function invite($roomId, $inviteId, $beInviteId)
    {
        return vss_model()->getRoomInviteModel()->createInvite($roomId, $inviteId, $beInviteId);
    }

    /**
     * Notes: 上传文件
     * Author: michael
     * Date: 2019/10/25
     * Time: 15:03
     * @param $params
     * @return array|bool
     *
     */
    public function upload($params)
    {
        return [
            'img_url' =>vss_service()->getUploadService()->uploadImg('file')
        ];
    }
}
