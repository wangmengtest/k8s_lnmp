<?php

namespace vhallComponent\room\models;

use vhallComponent\decouple\models\WebBaseModel;
use vhallComponent\room\constants\RoomJoinRoleNameConstant;

/**
 * Class RoomJoinsModel
 *
 * @package App\Models
 * @property int $join_id                   主键ID
 * @property string  $room_id                   房间id
 * @property string  $account_id                用户id
 * @property string  $nickname                  用户昵称
 * @property string  $avatar                    用户头像
 * @property string  $role_name                 角色信息，1主持人2观众3助理4嘉宾
 * @property bool $is_banned                 是否禁言，1是0否
 * @property bool $is_kicked                 是否踢出，1是0否
 * @property int $device_type               设备类型，0未检测 1手机端 2PC 3SDK
 * @property int $device_status             设备状态，0未检测1可以上麦2不可以上麦
 * @property bool $is_signed                 是否签到：1 是 0 否
 * @property bool $is_answered_questionnaire 是否回答过问卷：1 是 0 否
 * @property bool $is_lottery_winner         是否已经成为抽奖中奖者：1 是 0 否
 * @property bool $is_answered_vote          是否投过票：1 是 0 否
 * @property bool $is_answered_exam          是否回答过试卷：1 是 0 否
 * @property int $status                    在线状态：0 离线 1 在线
 * @property string  $updated_at
 * @property string  $created_at
 * @property string  $deleted_at
 */
class RoomJoinsModel extends WebBaseModel
{
    protected $table = 'room_joins';

    protected $casts = [
        'account_id' => 'string',
    ];

    protected $attributes = [
        'room_id'       => '',
        'account_id'    => '',
        'nickname'      => '',
        'avatar'        => '',
        'role_name'     => '',
        'is_banned'     => 0,
        'is_kicked'     => 0,
        'device_type'   => 0,
        'device_status' => 0,
        'updated_at'    => '0000-00-00 00:00:00',
        'created_at'    => '0000-00-00 00:00:00',
        'deleted_at'    => null
    ];

    protected $primaryKey = 'join_id';

    protected static function boot()
    {
        self::created(function (self $data) {
            $data->putCache('InfoByJoinId', $data->join_id, $data->getAttributes());
            $data->putCache('InfoByAccountIdAndRoomId', $data->account_id . 'and' . $data->room_id, $data->join_id);
            $data->role_name == RoomJoinRoleNameConstant::HOST && $data->putCache(
                'HostByRoomId',
                $data->room_id,
                $data->join_id
            );
        });
        self::updated(function (self $data) {
            $data->putCache('InfoByJoinId', $data->join_id, $data->getAttributes());
            $data->putCache('InfoByAccountIdAndRoomId', $data->account_id . 'and' . $data->room_id, $data->join_id);
            $data->role_name == RoomJoinRoleNameConstant::HOST && $data->putCache(
                'HostByRoomId',
                $data->room_id,
                $data->join_id
            );
        });
        self::deleted(function (self $data) {
            $data->delCache($data->join_id, $data->account_id, $data->room_id, $data->role_name);
            $data->is_banned == 1 && $data->deleteCache('getBannedNum', $data->room_id);
        });

        parent::boot();
    }

    /**
     * @param $join_id
     *
     * @return $this
     */
    public function findByJoinId($join_id)
    {
        $attributes = $this->getCache('InfoByJoinId', $join_id, function () use ($join_id) {
            $model = $this->find($join_id);
            return $model ? $model->getAttributes() : null;
        });
        return empty($attributes) ? null : $this->newInstance($attributes, true)->syncOriginal();
    }

    /**
     * @param $account_id
     * @param $room_id
     *
     * @return $this
     */
    public function findByAccountIdAndRoomId($account_id, $room_id)
    {
        if (empty($account_id) || empty($room_id)) {
            return null;
        }
        $join_id = $this->getCache(
            'InfoByAccountIdAndRoomId',
            $account_id . 'and' . $room_id,
            function () use ($account_id, $room_id) {
                $model = $this->where(compact('account_id', 'room_id'))->first();
                return $model ? $model->join_id : null;
            }
        );
        return empty($join_id) ? null : $this->findByJoinId($join_id);
    }

    /**
     * @param $room_id
     *
     * @return $this
     */
    public function findHostByRoomId($room_id)
    {
        $join_id = $this->getCache('HostByRoomId', $room_id, function () use ($room_id) {
            $model = $this->where(['room_id' => $room_id, 'role_name' => RoomJoinRoleNameConstant::HOST])->first();
            return $model ? $model->join_id : null;
        });
        return empty($join_id) ? null : $this->findByJoinId($join_id);
    }

    public function getBannedNum($room_id)
    {
        return $this->getCache('getBannedNum', $room_id, function () use ($room_id) {
            return $this->where(['room_id' => $room_id, 'is_banned' => 1])->count();
        });
    }

    /**
     * @param $joinIds
     * @param $update
     * @return int
     */
    public function updateByJoinIds($joinIds, $update)
    {
        if (empty($joinIds)) {
            return null;
        }
        return $this->whereIn('join_id', $joinIds)->update($update);
    }

    /**
     * 批量修改并删除缓存
     * @param $roomId
     * @param $accountIds
     * @param $update
     */
    public function updateByRoomIdAccountIds($roomId, $accountIds, $update)
    {
        $res = $this->where('room_id', $roomId)->whereIn('account_id', $accountIds)->update($update);
        //清除缓存
        if ($res) {
            $list = $this->where('room_id', $roomId)->whereIn('account_id', $accountIds)->get(['join_id', 'account_id'])->toArray();
            foreach ($list as $info) {
                $this->delCache($info['join_id'], $info['account_id'], $roomId);
            }
        }
    }

    /**
     * 获取参会用户信息 通过room_id account_id
     * @param $roomId
     * @param array $accountIds
     * @param array $columns
     * @return array
     */
    public function listByRoomIdAccountIds($roomId, array $accountIds=[], $columns = ['*'], $keyBy = '')
    {
        if (empty($roomId)) {
            return [];
        }
        $list = $this->where('room_id', $roomId)->whereIn('account_id', $accountIds)->get($columns);
        //以何为键
        $keyBy && $list = $list->keyBy($keyBy);
        $list = $list->toArray();
        return $list;
    }

    /**
     * 删除缓存
     * @param string $joinId
     * @param string $accountId
     * @param string $roomId
     * @param string $roleName
     */
    public function delCache($joinId = '', $accountId = '', $roomId = '', $roleName = '')
    {
        $joinId && $this->deleteCache('InfoByJoinId', $joinId);
        $accountId && $roomId && $this->deleteCache('InfoByAccountIdAndRoomId', $accountId . 'and' . $roomId);
        $roleName == RoomJoinRoleNameConstant::HOST && $roomId && $this->deleteCache('HostByRoomId', $roomId);
    }
}
