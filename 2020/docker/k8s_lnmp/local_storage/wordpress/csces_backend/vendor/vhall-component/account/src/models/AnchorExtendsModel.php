<?php

namespace vhallComponent\account\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class AnchorExtendsModel
 * 主播信息补充表
 *
 * @package App\Models
 * @property int $account_id    主播用户ID
 * @property int $connect_num   主播账户连接数限制
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string $deleted_at
 *
 * @package vhallComponent\account\models
 */
class AnchorExtendsModel extends WebBaseModel
{
    protected $table = 'anchor_extends';

    protected $primaryKey = 'id';

    protected $attributes = [
        'id'      => null,
        'account_id'        => null,
        'connect_num'         => 0,
        'created_at'   => '0000-00-00 00:00:00',
        'updated_at'   => '0000-00-00 00:00:00',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function (self $data) {
            $data->putCache('InfoByAccountId', $data->account_id, $data->getAttributes());
        });
        static::saved(function (self $data) {
            $data->putCache('InfoByAccountId', $data->account_id, $data->getAttributes());
        });
        self::updated(function (self $data) {
            $data->putCache('InfoByAccountId', $data->account_id, $data->getAttributes());
        });
    }

    /**
     * 通过account_id保存/修改
     *
     * @param $accountId
     * @param $data
     *
     * @return bool|array
     */
    public function saveByAccountId($accountId, $data)
    {
        $save = $this->updateOrCreate(['account_id' => $accountId], $data);
        if (!$save) {
            vss_logger()->error($save->errorInfo);
            return false;
        }
        return $save->toArray();
    }

    /**
     * 通过account_id获取信息
     * @param $accountId
     * @return $this|null
     */
    public function getInfoByAccountId($accountId)
    {
        $attributes = $this->getCache('InfoByAccountId', $accountId, function () use ($accountId) {
            $model = $this->where('account_id', $accountId)->first();
            return $model ? $model->getAttributes() : null;
        });

        return empty($attributes) ? null : $this->newInstance($attributes, true)->syncOriginal();
    }
}
