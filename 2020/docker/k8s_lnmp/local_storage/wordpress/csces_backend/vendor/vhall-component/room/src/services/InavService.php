<?php

namespace vhallComponent\room\services;

use App\Constants\ResponseCode;
use Exception;
use Vss\Common\Services\WebBaseService;
use vhallComponent\room\constants\CachePrefixConstant;
use vhallComponent\room\constants\InavGlobalConstant;
use vhallComponent\room\constants\RoomConstant;
use vhallComponent\room\constants\RoomJoinRoleNameConstant;
use vhallComponent\room\models\RoomJoinsModel;
use vhallComponent\account\constants\AccountConstant;

/**
 * InavServiceTrait
 *
 * @uses     yangjin
 * @date     2020-08-12
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class InavService extends WebBaseService
{
    /**
     * 获取邀请列表
     *
     * @param $room_id
     *
     * @return array
     * @throws Exception
     */
    public function getInviteList($room_id)
    {
        $t      = time();
        $result = vss_redis()->zrangebyscore(
            CachePrefixConstant::INTERACT_INVITE . $room_id,
            $t - CachePrefixConstant::INVITE_VALID_TIME,
            '+inf',
            ['withscores' => true]
        );
        if (!empty($result)) {
            foreach ($result as &$v) {
                $v = $v + CachePrefixConstant::INVITE_VALID_TIME - $t;
            }
        }
        return $result;
    }

    /**
     * 邀请
     *
     * @param RoomJoinsModel $join_user 接收用户
     *
     * @throws Exception
     */
    public function sendInvite($join_user)
    {
        if (array_key_exists($join_user->account_id, $this->getInviteList($join_user->room_id))) {
            $this->fail(ResponseCode::BUSINESS_REPEAT_INVITATION);
        }
        $invite_count = vss_redis()->zcount(
            CachePrefixConstant::INTERACT_INVITE . $join_user->room_id,
            time() - CachePrefixConstant::INVITE_VALID_TIME,
            '+inf'
        );

        //当前邀请数量
        $speaker_count = $this->getSpeakerCount($join_user);
        if ($speaker_count >= CachePrefixConstant::SPEAKER_MAX_NUM) {
            $this->fail(ResponseCode::BUSINESS_SPEAKER_FULL);
        } elseif ((int)$invite_count + $speaker_count < CachePrefixConstant::SPEAKER_MAX_NUM) {//邀请数量不能大于（当前邀请数量+上麦数量）
            vss_redis()->zadd(
                CachePrefixConstant::INTERACT_INVITE . $join_user->room_id,
                time(),
                $join_user->account_id
            );
            vss_redis()->expire(
                CachePrefixConstant::INTERACT_INVITE . $join_user->room_id,
                CachePrefixConstant::INVITE_VALID_TIME
            );

            $this->sendMsg($join_user->room_id, [
                'type'           => 'vrtc_connect_invite', //邀请
                'room_join_id'   => $join_user->account_id, //参会id
                'nick_name'      => $join_user->nickname, //昵称
                'room_role'      => $join_user->role_name, //用户角色（1:老师 2:学员 3:助教 4:嘉宾 5:监课）
                'prohibit_speak' => $join_user->is_banned, //是否禁言Y是N否
                'kicked_out'     => $join_user->is_kicked, //是否踢出Y是N否
                'device_type'    => $join_user->device_type, //设备类型0其他1手机端2PC
                'device_status'  => $join_user->device_status, //设备状态0未检测1可上麦2不可上麦
                'avatar'         => $join_user->avatar, //头像
                'target_id'      => $join_user->account_id, //参会id
            ]);
        } else {
            $this->fail(ResponseCode::BUSINESS_SPEAKER_OR_INVITATION_FULL);
        }
    }

    /**
     * 回复邀请
     *
     * @param RoomJoinsModel $join_user 接收用户
     * @param int            $type      1同意 0拒绝
     *
     * @throws Exception
     */
    public function replyInvite($join_user, $type)
    {
        if (!$type) {
            vss_redis()->zrem(CachePrefixConstant::INTERACT_INVITE . $join_user->room_id, $join_user->account_id);
        }
        $this->sendMsg($join_user->room_id, [
            'type'           => $type ? 'vrtc_connect_invite_agree' : 'vrtc_connect_invite_refused',
            'room_join_id'   => $join_user->account_id, //参会id
            'nick_name'      => $join_user->nickname, //昵称
            'room_role'      => $join_user->role_name, //用户角色（1:老师 2:学员 3:助教 4:嘉宾 5:监课）
            'prohibit_speak' => $join_user->is_banned, //是否禁言Y是N否
            'kicked_out'     => $join_user->is_kicked, //是否踢出Y是N否
            'device_type'    => $join_user->device_type, //设备类型0其他1手机端2PC
            'device_status'  => $join_user->device_status, //设备状态0未检测1可上麦2不可上麦
            'avatar'         => $join_user->avatar, //头像
        ]);
    }

    /**
     * 获取已上麦列表用户
     *
     * @param $room_id
     *
     * @return array
     * @throws Exception
     */
    public function getSpeakerList($room_id)
    {
        $rows   = vss_redis()->hgetall(CachePrefixConstant::INTERACT_SPEAKER . $room_id);
        $result = [];
        foreach ($rows as $row) {
            $result[] = json_decode($row, true);
        }
        return $result;
    }

    /**
     * 上麦
     *
     * @param RoomJoinsModel $join_user 参会用户
     * @param int            $type      上麦方式 1举手 2邀请
     *
     * @throws Exception
     */
    public function addSpeaker($join_user, $type = 1)
    {
        if ($join_user->role_name != RoomJoinRoleNameConstant::HOST) {
            $key = CachePrefixConstant::INTERACT_HANDSUP . $join_user->account_id;
            if (vss_redis()->get($key) != 2 &&
                !array_key_exists($join_user->account_id, $this->getInviteList($join_user->room_id))
            ) {
                $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
            }
        }

        if (!vss_redis()->hexists(
            CachePrefixConstant::INTERACT_SPEAKER . $join_user->room_id,
            $join_user->account_id
        )) {
            $speaker_count = $this->getSpeakerCount($join_user);

            if ($speaker_count < CachePrefixConstant::SPEAKER_MAX_NUM) {
                vss_redis()->hset(
                    CachePrefixConstant::INTERACT_SPEAKER . $join_user->room_id,
                    $join_user->account_id,
                    json_encode([
                        'nick_name'  => $join_user->nickname,
                        'role_name'  => $join_user->role_name,
                        'account_id' => $join_user->account_id,
                        'audio'      => 1,
                        'video'      => 1
                    ])
                );
                vss_redis()->zrem(
                    CachePrefixConstant::INTERACT_INVITE . $join_user->room_id,
                    $join_user->account_id
                );//上麦删除邀请
                vss_redis()->del([CachePrefixConstant::INTERACT_HANDSUP . $join_user->account_id]);
                $this->sendMsg($join_user->room_id, [
                    'type'              => 'vrtc_connect_success', //上麦
                    'room_join_id'      => $join_user->account_id, //参会id
                    'nick_name'         => $join_user->nickname, //昵称
                    'room_role'         => $join_user->role_name, //用户角色（1:老师 2:学员 3:助教 4:嘉宾 5:监课）
                    'prohibit_speak'    => $join_user->is_banned, //是否禁言Y是N否
                    'kicked_out'        => $join_user->is_kicked, //是否踢出Y是N否
                    'device_type'       => $join_user->device_type, //设备类型0其他1手机端2PC
                    'device_status'     => $join_user->device_status, //设备状态0未检测1可上麦2不可上麦
                    'avatar'            => $join_user->avatar, //头像
                    'vrtc_audio_status' => 'on', //麦克风开关on开off关
                    'vrtc_video_status' => 'on', //摄像头开关on开off关
                    'vrtc_connect_type' => $type, //连麦类型apply主动申请上麦invite主持人邀请上麦
                ]);
            } else {
                $this->fail(ResponseCode::BUSINESS_SPEAKER_FULL);
            }
        } else {
            $this->fail(ResponseCode::BUSINESS_REPEAT_SPEAKER);
        }
    }

    /**
     * 下麦
     *
     * @param RoomJoinsModel $join_user         参会用户
     * @param RoomJoinsModel $receive_join_user 被操作用户
     * @param                $reason
     *
     * @throws Exception
     */
    public function notSpeaker($join_user, $receive_join_user, $reason = null)
    {
        vss_redis()->hdel(
            CachePrefixConstant::INTERACT_SPEAKER . $receive_join_user->room_id,
            $receive_join_user->account_id
        );

        $main_screen = vss_redis()->hget(
            CachePrefixConstant::INTERACT_GLOBAL . $receive_join_user->room_id,
            InavGlobalConstant::MAIN_SCREEN
        );

        //主画面
        if ($receive_join_user->account_id == $main_screen) {
            $webinar_host = vss_model()->getRoomJoinsModel()->findHostByRoomId($receive_join_user->room_id);
            if (!empty($webinar_host)) {
                $this->setDocPermission($webinar_host);
                $this->setMainScreen($webinar_host);
            }
        }

        $this->sendMsg($receive_join_user->room_id, [
            'type'         => 'vrtc_disconnect_success', //下麦
            'room_join_id' => $join_user->account_id, //参会id
            'target_id'    => $receive_join_user->account_id, //被操作人参会id
            'vrtc_reason'  => $reason, //下麦原因
            'nick_name'    => $receive_join_user->nickname, //昵称
            'room_role'    => $receive_join_user->role_name, //用户角色（1:老师 2:学员 3:助教 4:嘉宾 5:监课）
        ]);

//        vss_service()->getSaasService()->syncSpeakSwitch($receive_join_user, 0);
    }

    /**
     * 获取上麦用户属性
     *
     * @param RoomJoinsModel $join_user
     * @param                $attr    属性字段
     * @param int            $default 默认
     *
     * @return mixed
     * @throws Exception
     */
    public function getSpeakerAttr($join_user, $attr, $default = 0)
    {
        $speaker = vss_redis()->hget(
            CachePrefixConstant::INTERACT_SPEAKER . $join_user->room_id,
            $join_user->account_id
        );
        if (!empty($speaker)) {
            $speaker = json_decode($speaker, true);
            return $speaker[$attr];
        }
        return $default;
    }

    /**
     * 更新上麦用户属性
     *
     * @param RoomJoinsModel $join_user
     * @param                $attr 更新属性
     *
     * @throws Exception
     */
    public function setSpeakerAttr($join_user, $attr)
    {
        $row = vss_redis()->hget(
            CachePrefixConstant::INTERACT_SPEAKER . $join_user->room_id,
            $join_user->account_id
        );
        if (!empty($row)) {
            $row = json_decode($row, true);
            $row = array_merge($row, $attr);
            vss_redis()->hset(
                CachePrefixConstant::INTERACT_SPEAKER . $join_user->room_id,
                $join_user->account_id,
                json_encode($row)
            );
            if (isset($attr['audio'])) {
                $this->sendMsg($join_user->room_id, [
                    'type'         => $row['audio'] ? 'vrtc_mute_cancel' : 'vrtc_mute', //麦克风开关
                    'room_join_id' => $join_user->account_id, //参会id
                    'target_id'    => $join_user->account_id, //参会id
                ]);
            }
            if (isset($attr['video'])) {
                $this->sendMsg($join_user->room_id, [
                    'type'         => $row['video'] ? 'vrtc_frames_display' : 'vrtc_frames_forbid', //摄像头开关
                    'room_join_id' => $join_user->account_id, //参会id
                    'target_id'    => $join_user->account_id, //参会id
                ]);
            }
        } else {
            $this->fail(ResponseCode::BUSINESS_NOT_SPEAKER);
        }
    }

    /**
     * 结束活动参数清空
     *
     * @param $room_id
     *
     * @throws Exception
     */
    public function clearGlobal($room_id)
    {
        vss_redis()->hdel(CachePrefixConstant::INTERACT_GLOBAL . $room_id, 'is_handsup');//开关举手
        vss_redis()->hdel(CachePrefixConstant::INTERACT_GLOBAL . $room_id, 'is_doc');//开关文档
        vss_redis()->hdel(CachePrefixConstant::INTERACT_GLOBAL . $room_id, InavGlobalConstant::DEFINITION);//清晰度
        vss_redis()->hdel(CachePrefixConstant::INTERACT_GLOBAL . $room_id, InavGlobalConstant::START_TYPE);//发起方式
        $join_user = vss_model()->getRoomJoinsModel()->findHostByRoomId($room_id);
        vss_redis()->hset(
            CachePrefixConstant::INTERACT_GLOBAL . $room_id,
            InavGlobalConstant::DOC_PERMISSION,
            $join_user->account_id
        );//文档操作人
        vss_redis()->hset(
            CachePrefixConstant::INTERACT_GLOBAL . $room_id,
            InavGlobalConstant::MAIN_SCREEN,
            $join_user->account_id
        );//主画面
        vss_redis()->del([CachePrefixConstant::INTERACT_SPEAKER . $room_id]);//上麦列表
        vss_redis()->del([CachePrefixConstant::INTERACT_INVITE . $room_id]);//邀请列表
        $this->delGlobal($room_id, InavGlobalConstant::ALL_BANNED);
    }

    /**
     * 初始化数据
     *
     * @param $room_id
     *
     * @throws Exception
     */
    public function initGlobal($room_id)
    {
        $join_user = vss_model()->getRoomJoinsModel()->findHostByRoomId($room_id);
        self::addSpeaker($join_user);//主持人上麦
        self::setMainScreen($join_user);
        self::setDocPermission($join_user);
    }

    /**
     * 申请上麦
     *
     * @param RoomJoinsModel $join_user 参会用户
     * @param                $type      1申请 0取消
     *
     * @return bool
     * @throws Exception
     */
    public function apply($join_user, $type)
    {
        $key = CachePrefixConstant::INTERACT_HANDSUP . $join_user->account_id;
        if ($type == 1) {
            if (vss_redis()->get($key) == 1) {
                return true;
            }
            vss_redis()->set($key, 1, CachePrefixConstant::HANDSUP_VALID_TIME);
        } else {
            if (vss_redis()->get($key) != 1) {
                return true;
            }
            vss_redis()->ttl($key) > 1 && vss_redis()->set($key, 4, CachePrefixConstant::HANDSUP_VALID_TIME);
        }
        return $this->sendMsg($join_user->room_id, [
            'type'           => $type ? 'vrtc_connect_apply' : 'vrtc_connect_apply_cancel',
            'room_join_id'   => $join_user->account_id, //参会id
            'nick_name'      => $join_user->nickname, //昵称
            'room_role'      => $join_user->role_name, //用户角色（1:老师 2:学员 3:助教 4:嘉宾 5:监课）
            'prohibit_speak' => $join_user->is_banned, //是否禁言Y是N否
            'kicked_out'     => $join_user->is_kicked, //是否踢出Y是N否
            'device_type'    => $join_user->device_type, //设备类型0其他1手机端2PC
            'device_status'  => $join_user->device_status, //设备状态0未检测1可上麦2不可上麦
            'avatar'         => $join_user->avatar, //头像
        ]);
    }

    /**
     * 回复上麦申请
     *
     * @param RoomJoinsModel $join_user 参会用户
     * @param                $type      1同意 0拒绝
     *
     * @throws Exception
     */
    public function replyApply($join_user, $type)
    {
        $key = CachePrefixConstant::INTERACT_HANDSUP . $join_user->account_id;
        if ($type == 1) {
            $speaker_count = $this->getSpeakerCount($join_user);
            if ($speaker_count >= CachePrefixConstant::SPEAKER_MAX_NUM) {
                $this->fail(ResponseCode::BUSINESS_SPEAKER_FULL);
            }
            if (vss_redis()->get($key) == 1) {
                vss_redis()->set($key, 2, CachePrefixConstant::HANDSUP_VALID_TIME);
            } else {
                $this->fail(ResponseCode::BUSINESS_NOT_APPLY);
            }
        } else {
            if (vss_redis()->get($key) != 1) {
                $this->fail(ResponseCode::BUSINESS_NOT_APPLY);
            } else {
                vss_redis()->ttl($key) > 1 && vss_redis()->set($key, 3, CachePrefixConstant::HANDSUP_VALID_TIME);
            }
        }
        $this->sendMsg($join_user->room_id, [
            'type'           => $type ? 'vrtc_connect_agree' : 'vrtc_connect_refused',
            'room_join_id'   => $join_user->account_id, //参会id
            'nick_name'      => $join_user->nickname, //昵称
            'room_role'      => $join_user->role_name, //用户角色（1:老师 2:学员 3:助教 4:嘉宾 5:监课）
            'prohibit_speak' => $join_user->is_banned, //是否禁言Y是N否
            'kicked_out'     => $join_user->is_kicked, //是否踢出Y是N否
            'device_type'    => $join_user->device_type, //设备类型0其他1手机端2PC
            'device_status'  => $join_user->device_status, //设备状态0未检测1可上麦2不可上麦
            'avatar'         => $join_user->avatar, //头像
            'target_id'      => $join_user->account_id, //参会id
        ]);
    }

    /**
     * 设置文档权限
     *
     * @param RoomJoinsModel $join_user 参会用户
     *
     * @throws Exception
     */
    public function setDocPermission($join_user)
    {
        vss_redis()->hset(
            CachePrefixConstant::INTERACT_GLOBAL . $join_user->room_id,
            InavGlobalConstant::DOC_PERMISSION,
            $join_user->account_id
        );
        $this->sendMsg($join_user->room_id, [
            'type'           => 'vrtc_speaker_switch',
            'room_join_id'   => $join_user->account_id, //参会id
            'nick_name'      => $join_user->nickname, //昵称
            'room_role'      => $join_user->role_name, //用户角色（1:老师 2:学员 3:助教 4:嘉宾 5:监课）
            'prohibit_speak' => $join_user->is_banned, //是否禁言Y是N否
            'kicked_out'     => $join_user->is_kicked, //是否踢出Y是N否
            'device_type'    => $join_user->device_type, //设备类型0其他1手机端2PC
            'device_status'  => $join_user->device_status, //设备状态0未检测1可上麦2不可上麦
            'avatar'         => $join_user->avatar, //头像
            'target_id'      => $join_user->account_id, //参会id
        ]);
    }

    /**
     * 设置主画面
     *
     * @param RoomJoinsModel $join_user 参会用户
     *
     * @throws Exception
     */
    public function setMainScreen($join_user)
    {
        vss_redis()->hset(
            CachePrefixConstant::INTERACT_GLOBAL . $join_user->room_id,
            InavGlobalConstant::MAIN_SCREEN,
            $join_user->account_id
        );
        $this->sendMsg($join_user->room_id, [
            'type'           => 'vrtc_big_screen_set',
            'room_join_id'   => $join_user->account_id, //参会id
            'nick_name'      => $join_user->nickname, //昵称
            'room_role'      => $join_user->role_name, //用户角色（1:老师 2:学员 3:助教 4:嘉宾 5:监课）
            'prohibit_speak' => $join_user->is_banned, //是否禁言Y是N否
            'kicked_out'     => $join_user->is_kicked, //是否踢出Y是N否
            'device_type'    => $join_user->device_type, //设备类型0其他1手机端2PC
            'device_status'  => $join_user->device_status, //设备状态0未检测1可上麦2不可上麦
            'avatar'         => $join_user->avatar, //头像
            'target_id'      => $join_user->account_id, //参会id
        ]);
    }

    /**
     * 切换主讲人
     *
     * @param RoomJoinsModel $join_user 参会用户
     *
     * @throws Exception
     */
    public function switchSpeaker($join_user)
    {
        if (in_array($join_user->role_name, [RoomJoinRoleNameConstant::HOST, RoomJoinRoleNameConstant::GUEST])) {//切换主画面
            $this->setDocPermission($join_user);
            $this->setMainScreen($join_user);
        } else {//切换文档操作人
            $webinar_host = vss_model()->getRoomJoinsModel()->findByJoinId($join_user->room_id);
            $this->setDocPermission($webinar_host);
            $this->setMainScreen($join_user);
        }
    }

    /**
     * 设置观看端布局
     *
     * @param $room_id
     * @param $layout
     *
     * @throws Exception
     */
    public function setLayout($room_id, $layout)
    {
        vss_redis()->hset(CachePrefixConstant::INTERACT_GLOBAL . $room_id, InavGlobalConstant::LAYOUT, $layout);
        $this->sendMsg($room_id, [
            'type'        => 'vrtc_layout_set', //设置观看端布局
            'vrtc_layout' => $layout, //观看端布局
        ]);
    }

    /**
     * 获取观看端布局
     *
     * @param $room_id
     *
     * @return int
     * @throws Exception
     */
    public function getLayout($room_id)
    {
        return vss_redis()->hget(CachePrefixConstant::INTERACT_GLOBAL . $room_id, InavGlobalConstant::LAYOUT);
    }

    /**
     * 设置清晰度
     *
     * @param $room_id
     * @param $definition
     *
     * @throws Exception
     */
    public function setDefinition($room_id, $definition)
    {
        vss_redis()->hset(
            CachePrefixConstant::INTERACT_GLOBAL . $room_id,
            InavGlobalConstant::DEFINITION,
            $definition
        );
        $this->sendMsg($room_id, [
            'type'            => 'vrtc_definition_set', //设置清晰度
            'vrtc_definition' => $definition, //清晰度
        ]);
    }

    /**
     * 获取清晰度
     *
     * @param $room_id
     *
     * @return int
     * @throws Exception
     */
    public function getDefinition($room_id)
    {
        return vss_redis()->hget(CachePrefixConstant::INTERACT_GLOBAL . $room_id, InavGlobalConstant::DEFINITION);
    }

    /**
     * 获取上麦数量
     *
     * @param RoomJoinsModel $join_user 参会用户
     *
     * @return int
     * @throws Exception
     */
    public function getSpeakerCount($join_user)
    {
        $speaker_count  = count(self::getSpeakerList($join_user->room_id));
        $doc_permission = vss_redis()->hget(
            CachePrefixConstant::INTERACT_GLOBAL . $join_user->room_id,
            InavGlobalConstant::DOC_PERMISSION
        );
        //文档操作人
        //文档操作人没有上麦也占位置
        if ($doc_permission
            && !vss_redis()->hexists(CachePrefixConstant::INTERACT_SPEAKER . $join_user->room_id, $doc_permission)
            && $doc_permission != $join_user->account_id) {
            $speaker_count++;
        }
        return $speaker_count;
    }

    /**
     * 设置设备
     *
     * @param RoomJoinsModel $join_user 接收用户
     * @param                $device_type
     * @param                $device_status
     *
     * @throws Exception
     */
    public function setDevice($join_user, $device_type, $device_status)
    {
        if (empty($join_user)) {
            $this->fail(ResponseCode::BUSINESS_INVALID_USER);
        }
        $join_user->update(compact('device_type', 'device_status'));
        $this->sendMsg($join_user->room_id, [
            'type'          => 'vrtc_connect_device_check',
            'room_join_id'  => $join_user->account_id, //参会id
            'room_role'     => $join_user->role_name, //用户角色（1:老师 2:学员 3:助教 4:嘉宾 5:监课）
            'nick_name'     => $join_user->nickname, //昵称
            'device_type'   => $join_user->device_type, //设备类型0其他1手机端2PC
            'device_status' => $join_user->device_status, //设备状态0未检测1可上麦2不可上麦
        ]);
    }

    /**
     * 获取互动配置
     *
     * @param $room_id
     * @param $key
     * @param $default
     *
     * @return mixed|null
     * @throws Exception
     */
    public function getGlobal($room_id, $key, $default = null)
    {
        $res = vss_redis()->hget(CachePrefixConstant::INTERACT_GLOBAL . $room_id, $key);
        return is_null($res) ? $default : $res;
    }

    /**
     * 设置观众申请上麦许可
     *
     * @param $room_id
     * @param $status
     *
     * @throws Exception
     */
    public function setHandsup($room_id, $status)
    {
        vss_redis()->hset(
            CachePrefixConstant::INTERACT_GLOBAL . $room_id,
            InavGlobalConstant::IS_HANDSUP,
            (int)$status
        );
        if ($status) {
            $this->sendMsg($room_id, [
                'type' => 'vrtc_connect_open', //开启上麦许可
            ]);
        } else {
            $this->sendMsg($room_id, [
                'type' => 'vrtc_connect_close', //关闭上麦许可
            ]);
        }
    }

    /**
     * 获取观众申请上麦许可
     *
     * @param $room_id
     *
     * @return int
     * @throws Exception
     */
    public function getHandsup($room_id)
    {
        return (int)vss_redis()->hget(
            CachePrefixConstant::INTERACT_GLOBAL . $room_id,
            InavGlobalConstant::IS_HANDSUP
        );
    }

    public function sendMsg($room_id, $msg)
    {
        return vss_service()->getPaasChannelService()->sendMessage($room_id, $msg);
    }

    /**
     * 发公告
     *
     * @param $params
     *
     * @return mixed
     *
     */
    public function sendNotice($params)
    {
        vss_validator($params, [
            'room_id' => 'required',
            'content' => 'required'
        ]);
        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        if ($join_user->role_name == RoomJoinRoleNameConstant::USER) {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }
        return vss_service()->getPaasChannelService()->sendNotice(
            $params['room_id'],
            $params['content'],
            $join_user->account_id
        );
    }

    /**
     * 设置全体禁言
     *
     * @param RoomJoinsModel $join_user
     * @param                $type
     *
     * @return mixed
     * @throws Exception
     */
    public function setAllBanned($join_user, $type)
    {
        vss_redis()->hset(
            CachePrefixConstant::INTERACT_GLOBAL . $join_user->room_id,
            InavGlobalConstant::ALL_BANNED,
            (int)$type
        );
        return vss_service()->getPaasChannelService()->sendChatMessage($join_user->room_id, $join_user->account_id, [
            'type' => $type ? 'disable_all' : 'permit_all'
        ]);
    }

    /**
     * 获取全体禁言
     *
     * @param $room_id
     *
     * @return int
     * @throws Exception
     */
    public function getAllBanned($room_id)
    {
        return (int)vss_redis()->hget(
            CachePrefixConstant::INTERACT_GLOBAL . $room_id,
            InavGlobalConstant::ALL_BANNED
        );
    }

    /**
     * 获取文档权限
     *
     * @param $room_id
     *
     * @return int
     * @throws Exception
     */
    public function getDocPermission($room_id)
    {
        $host = vss_model()->getRoomJoinsModel()->findHostByRoomId($room_id);
        return vss_redis()->hget(
            CachePrefixConstant::INTERACT_GLOBAL . $room_id,
            InavGlobalConstant::DOC_PERMISSION
        ) ?: $host->account_id;
    }

    /**
     * 获取主画面
     *
     * @param $room_id
     *
     * @return int
     * @throws Exception
     */
    public function getMainScreen($room_id)
    {
        $host = vss_model()->getRoomJoinsModel()->findHostByRoomId($room_id);
        return vss_redis()->hget(
            CachePrefixConstant::INTERACT_GLOBAL . $room_id,
            InavGlobalConstant::MAIN_SCREEN
        ) ?: $host->account_id;
    }

    /**
     * 设置互动配置
     *
     * @param $room_id
     * @param $key
     * @param $val
     *
     * @return mixed
     * @throws Exception
     */
    public function setGlobal($room_id, $key, $val)
    {
        return vss_redis()->hset(CachePrefixConstant::INTERACT_GLOBAL . $room_id, $key, $val);
    }

    /**
     * 设置互动配置
     *
     * @param $room_id
     * @param $key
     *
     * @return mixed
     * @throws Exception
     */
    public function delGlobal($room_id, $key)
    {
        return vss_redis()->hdel(CachePrefixConstant::INTERACT_GLOBAL . $room_id, $key);
    }

    /**
     * 设置桌面演示
     *
     * @param $room_id
     * @param $status
     *
     * @throws Exception
     */
    public function setDesktop($room_id, $status)
    {
        vss_redis()->hset(
            CachePrefixConstant::INTERACT_GLOBAL . $room_id,
            InavGlobalConstant::IS_DESKTOP,
            (int)$status
        );
        if ($status) {
            $this->sendMsg($room_id, [
                'type' => 'desktop_open', //开启桌面演示
            ]);
        } else {
            $this->sendMsg($room_id, [
                'type' => 'desktop_close', //关闭桌面演示
            ]);
        }
    }

    /**
     * 获取桌面演示状态
     *
     * @param $room_id
     *
     * @return int
     * @throws Exception
     */
    public function getDesktop($room_id)
    {
        return (int)vss_redis()->hget(
            CachePrefixConstant::INTERACT_GLOBAL . $room_id,
            InavGlobalConstant::IS_DESKTOP
        );
    }

    /**
     * @param        $ilId
     * @param        $accountInfo
     * @param string $password
     *
     * @return array
     *
     * @author  jin.yang@vhall.com
     * @date    2020-08-13
     */
    public function get($ilId, $accountInfo, $password = '', $rolename = null)
    {
        //房间信息
        $liveInfo = vss_model()->getRoomsModel()->getInfoByIlId($ilId);
        if (!$liveInfo) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }

        # vhallEOF-watchlimit-inavService-get-1-start
        
        //观看限制
        $liveInfo["phone"]     = $accountInfo["phone"];
        $applyInfo             = vss_service()->getWatchlimitService()->getApplyorderby($ilId);
        $liveInfo["form_id"]   = $applyInfo["source_id"];
        $liveInfo["accs_type"] = $accountInfo["account_type"];
        $data["is_visitor"]    = $liveInfo["is_visitor"] = $accountInfo["account_type"] == AccountConstant::ACCOUNT_TYPE_VISITOR ? 1 : 0;

        # vhallEOF-watchlimit-inavService-get-1-end

        $accountId = $accountInfo['account_id'] ?? '123';  //未登录时默认123
        $data      = ['room_info' => $liveInfo];

        //标签获取
        $data['tags'] = vss_service()->getRoomService()->getTagsInfo($liveInfo['topics']);

        $ret = vss_redis()->get(RoomConstant::WARM_INFO . $liveInfo['il_id']);
        $ret = json_decode($ret, true);
        vss_logger()->info('Vss-redis', ['data' => $ret]);

        if ($ret) {
            if ($ret['type'] == 0) {
                $ret['img'] = $liveInfo['image'];
            }
            $data['warm_info'] = $ret;
        } else {
            $data['warm_info'] = ['type' => 0];
        }
        $data['second'] = $this->getStamp($liveInfo);

        if ($accountInfo) {
            //获取直播间用户角色
            $role                        = $this->getRoomJoinsRoleName($liveInfo, $accountInfo, $password, $rolename);
            $data['type']                = $role;
            $data['role_name']           = $role;
            $data['third_party_user_id'] = $accountId;

            # vhallEOF-watchlimit-inavService-get-2-start
        
           //观看权限判断
            if(empty($password)){
                vss_service()->getWatchlimitService()->watchdecide($data);
            }

        # vhallEOF-watchlimit-inavService-get-2-end

            //并发控制
            # vhallEOF-perfctl-inavService-get-1-start
        
            //并发控制
            if($role == AccountConstant::TYPE_WATCH){
                $liveInfo = is_array($liveInfo)?$liveInfo:$liveInfo->toArray();
                $connectData = vss_service()->getConnectctlService()->connectCtl($liveInfo,$accountId);
                $data["room_max_count"] = $connectData["room_max_count"];
            }

        # vhallEOF-perfctl-inavService-get-1-end

            $inavId                   = $liveInfo['inav_id'];
            $channelId                = $liveInfo['channel_id'];
            $data['access_token_url'] = vss_service()->getPaasService()
                ->buildPaasRequestUrl(
                    [
                        'third_party_user_id'  => $accountId,
                        'publish_stream'       => $liveInfo['room_id'],
                        'chat'                 => $channelId,
                        'operate_document'     => $channelId,
                        'kick_inav'            => $inavId,
                        'publish_inav_stream'  => $inavId,
                        'kick_inav_stream'     => $inavId,
                        'askfor_inav_publish'  => $inavId,
                        'audit_inav_publish'   => $inavId,
                        'publish_inav_another' => $inavId,
                        'apply_inav_publish'   => $inavId,
                        'data_collect_manage'  => 'all',
                        'data_collect_submit'  => 'all',
                        'data_collect_view'    => 'all',
                    ],
                    '/api/v2/base/create-v2-access-token'
                );

            //回放文档是否存在 todo fixme 考虑文档组件集成
            # vhallEOF-document-inavService-get-1-start
        
                $data["document_exists"] = vss_model()->getDocumentStatusModel()->findExistsByRecordId($liveInfo["record_id"],$ilId);

        # vhallEOF-document-inavService-get-1-end

            //VSS_TOKEN
            $create_token      = [
                'app_id'              => vss_service()->getTokenService()->getAppId(),
                'third_party_user_id' => $accountInfo['account_id'],
                'expire_time'         => date('Y-m-d H:i:s', strtotime('+1 day')),
            ];
            $data['vss_token'] = vss_service()->getTokenService()->create($create_token)['access_token'] ?? '';
            //加入VSS房间
            $params    = [
                'room_id'       => $liveInfo['room_id'],
                'account_id'    => $accountInfo['account_id'],
                'nickname'      => $accountInfo['nickname'],
                'username'      => $accountInfo['username'],
                'avatar'        => $accountInfo['profile_photo'] ?:
                    vss_config('application.static.headPortrait.default'),
                'role_name'     => $role,
                'device_type'   => 0,
                'device_status' => 0,
            ];
            $joinModel = vss_service()->getRoomService()->join($params);

            //踢出状态
            $data['is_kick_out'] = (int)$joinModel->is_kicked;
            //发言状态
            $data['is_block_speek'] = (int)$joinModel->is_banned;

            if ($data['is_kick_out'] == 1) {
                $this->fail(ResponseCode::BUSINESS_KICKED);
            }

            $url                  = vss_config('application.url') . "/assistant/$ilId?";
            $params               = [
                'embedType' => 'chat',
                'vssToken'  => base64_encode($data['vss_token']),
                'roomId'    => $liveInfo['room_id']
            ];
            $data['web_chat_url'] = $url . http_build_query($params);
            $data['web_doc_url']  = $url . http_build_query(array_merge($params, ['embedType' => 'doc']));
            $data['is_visitor']   = $accountInfo['account_type'] == AccountConstant::ACCOUNT_TYPE_VISITOR ? 1 : 0;

            //记录pv量
            if ($liveInfo['status'] != RoomConstant::STATUS_STOP) {
                vss_service()->getRoomService()->addPv($ilId, $accountInfo['account_id']);
            }
        } else {
            //获取VSS房间状态
            $data['status'] = $liveInfo['status'];
        }

        # vhallEOF-invitecard-InavService-get-1-start
        
        //邀请卡状态
        $inviteStatus = vss_service()->getInviteCardService()->getStatus($liveInfo["room_id"]);
        $data["invite_status"] = $inviteStatus;

        # vhallEOF-invitecard-InavService-get-1-end

        return $data;
    }

    /**
     *  直播已播放时长
     * */
    public function getStamp($liveInfo)
    {
        if ($liveInfo['begin_live_time'] == '0000-00-00 00:00:00') {
            $da = 0;
        } elseif ($liveInfo['status'] == 1 && $liveInfo['begin_live_time']) {
            $da = time() - strtotime($liveInfo['begin_live_time']);
        } else {
            $da = 0;
        }
        return $da;
    }

    /**
     * 获取用户进入直播间时所使用角色
     *
     * @param $liveInfo
     * @param $accountInfo
     * @param $password
     * @param $rolename
     *
     * @return int
     *
     */
    public function getRoomJoinsRoleName($liveInfo, $accountInfo, $password, $rolename)
    {
        $accountType = AccountConstant::TYPE_WATCH;
        $rolename    = (int)$rolename;
        if ($password) {
            if ($accountInfo['account_id'] == $liveInfo['account_id']) {
                $this->fail(ResponseCode::AUTH_ROLE_ERROR);
            }
            $supplyInfo = vss_model()->getRoomSupplyModel()->getInfoByIlId($liveInfo['il_id']);
            if ($password == $supplyInfo['assistant_sign']) {//助理
                $accountType = AccountConstant::TYPE_ASSISTANT;
            } elseif ($password == $supplyInfo['interaction_sign']) {
                $accountType = AccountConstant::TYPE_INTERACTION;
            } else {
                $this->fail(ResponseCode::AUTH_ROLE_PASSWORD_ERROR);
            }

            if ($liveInfo['status'] == RoomConstant::STATUS_STOP) {
                $this->fail(ResponseCode::AUTH_NOT_SUPPORT_ROLE_PASSWORD);
            }
        } else {
            if ($accountInfo['account_id'] == $liveInfo['account_id']) {
                //主持人 $rolename 有传值且不为主持人 报错
                if ($rolename && $rolename != 1) {
                    $this->fail(ResponseCode::AUTH_ROLE_ERROR);
                }
                $accountType = AccountConstant::TYPE_MASTER;
            } else {
                if (!empty($rolename)) {
                    //$rolename 有传值且非观众 报错
                    if (!in_array($rolename, [2])) {
                        $this->fail(ResponseCode::AUTH_ROLE_ERROR);
                    }
                    $accountType = $rolename;
                }
            }
        }

        return $accountType;
    }
}
