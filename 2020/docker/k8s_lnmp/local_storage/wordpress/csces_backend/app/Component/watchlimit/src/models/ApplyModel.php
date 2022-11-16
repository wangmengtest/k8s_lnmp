<?php

namespace App\Component\watchlimit\src\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * ApplyModel
 *
 * @property int    $il_id         房间ID
 * @property int    $source_id     表单ID
 * @property string $limit_type    限制类型 0:登录 1:上报 2:默认登录 3:白名单
 * @property string $updated_at
 * @property string $created_at
 * @property string $deleted_at
 *
 * @uses     yangjin
 * @date     2020-05-19
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class ApplyModel extends WebBaseModel
{
    protected $table = 'apply';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['il_id', 'source_id', 'limit_type'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    # protected $appends = ['status_str', 'sex_str', 'type_str'];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        self::updated(function (self $model) {
            $model->putCache('InfoByIlId', $model->il_id, $model->getAttributes());
        });
        self::deleted(function (self $data) {
            $data->deleteCache('InfoByIlId', $data->il_id);
        });
    }

    /**
     * 保存-修改
     *
     * @param $ilId
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
     * 获取信息
     *
     * @param $ilId
     *
     * @return mixed
     */
    public function getApplyInfoByIlId($ilId)
    {
        $info = $this->getCache('InfoByIlId', $ilId, function () use ($ilId) {
            $model = $this->where('il_id', $ilId)->first();
            return $model ? $model->getAttributes() : [];
        });
        return $info;
    }
}
