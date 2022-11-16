<?php

namespace vhallComponent\room\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class RoomExtendsModel
 * 房间扩展信息类模型
 *
 * @package App\Models
 * @property int $id         主键id
 * @property string  $room_id    房间id
 * @property string  $custom_tag 用户自定义简介tag
 * @property string  $start_time 开播时间
 * @property string  $end_time   结束时间
 * @property bool $start_type 开播类型1 web 2 app 3 sdk 4 推拉流 5 定时 6 admin后台 7第三方8 助手
 * @property bool $end_type   结束类型1 web 2 app 3 sdk 4 推拉流 5 定时 6 admin后台 7第三方8 助手
 * @property bool $is_delete  是否删除>0|否,1|是
 * @property string  $created_at 创建时间
 * @property string  $updated_at 更新时间
 * @property string  $deleted_at
 *
 * @author  ensong.liu@vhall.com
 * @date    2019-06-09 16:16:35
 * @version v1.0.0
 */
class RoomExtendsModel extends WebBaseModel
{
    protected $table      = 'room_extends';

    protected $primaryKey = 'id';

    protected static function boot()
    {
        self::created(function (RoomExtendsModel $data) {
            $data->deleteCache('startByRoomId', $data->room_id);
        });
        self::updated(function (self $data) {
            $data->deleteCache('startByRoomId', $data->room_id);
        });
        self::deleted(function (self $data) {
            $data->deleteCache('startByRoomId', $data->room_id);
        });
        parent::boot();
    }

    /**
     * 获取当前发起类型
     *
     * @param $room_id
     *
     * @return $this
     */
    public function findStartByRoomId($room_id)
    {
        $attributes = $this->getCache('startByRoomId', $room_id, function () use ($room_id) {
            $model = $this->where(compact('room_id'))->where('end_type', 0)->orderBy('created_at', 'desc')->first();
            return $model ? $model->getAttributes() : null;
        });
        return empty($attributes) ? null : $this->newInstance($attributes, true)->syncOriginal();
    }
}
