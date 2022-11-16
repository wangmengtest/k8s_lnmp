<?php

namespace vhallComponent\room\models;

use vhallComponent\decouple\models\WebBaseModel;
use vhallComponent\room\constants\RoomConstant;

/**
 * Class RoomSupplyModel
 * 房间信息补充表
 *
 * @package App\Models
 * @property int    $il_id            房间ID
 * @property string $room_id          PAAS房间id
 * @property int    $account_id       PAAS房间id
 * @property string $custom_tag       用户自定义简介tag
 * @property string $assistant_sign   助理口令
 * @property string $interaction_sign 互动口令
 * @property int    mode              1,互动助理模式,0普通模式
 * @property string $created_at       创建时间
 * @property string $updated_at       更新时间
 * @property string $deleted_at
 *
 * @package vhallComponent\room\models
 */
class RoomSupplyModel extends WebBaseModel
{
    protected $table = 'room_supply';

    protected $primaryKey = 'il_id';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // 纯直播模式下没有嘉宾
            if ($model->live_type != RoomConstant::LIVE_TYPE_ONLY) {
                $model->interaction_sign = rand(100000, 999999);
            }
            $model->assistant_sign = rand(100000, 999999);

            unset($model->live_type);
        });

        self::updated(function (self $data) {
            $data->putCache('InfoByIlId', $data->il_id, $data->getAttributes());
        });
    }

    /**
     * 模型关联-房间表
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function rooms()
    {
        return $this->belongsTo(RoomsModel::class, 'il_id', 'il_id');
    }

    /**
     * 通过il_id保存/修改
     *
     * @param $roomId
     * @param $data
     *
     * @return bool
     */
    public function saveByIlId($ilId, $data)
    {
        $save = $this->updateOrCreate(['il_id' => $ilId], $data);
        if (!$save) {
            return false;
        }
        return $save->toArray();
    }

    /**
     * 通过il_id获取信息
     *
     * @param $ilId
     *
     * @return $this|null
     */
    public function getInfoByIlId($ilId)
    {
        $attributes = $this->getCache('InfoByIlId', $ilId, function () use ($ilId) {
            $model = $this->where('il_id', $ilId)->first();

            return $model ? $model->getAttributes() : null;
        });

        return empty($attributes) ? null : $this->newInstance($attributes, true)->syncOriginal();
    }
}
