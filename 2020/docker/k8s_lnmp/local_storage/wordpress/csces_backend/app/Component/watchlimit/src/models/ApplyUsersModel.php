<?php

namespace App\Component\watchlimit\src\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * ApplyUsersModel
 *
 * @property int    $answer_id     回答id
 * @property int    $il_id         房间id
 * @property int    $phone         手机号码
 * @property string $limit_type    0:默认 1:上报 2:登录 3:白名单
 * @property int    $apply_id      报名表id
 * @property string $updated_at
 * @property string $created_at
 * @property string $deleted_at
 *
 * @uses     yangjin
 * @date     2020-05-19
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class ApplyUsersModel extends WebBaseModel
{
    protected $table = 'apply_users';

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
    protected $fillable = ['answer_id', 'il_id', 'limit_type', 'phone', 'apply_id'];

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

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
    }

    /**
     * 保存-修改
     *
     * @param $ilId
     * @param $data
     *
     * @return bool
     */
    public function saveByIlIdAndPhone($ilId, $phone, $data)
    {
        $save = $this->updateOrCreate(['il_id' => $ilId, 'phone' => $phone], $data);
        if (!$save) {
            return false;
        }
        return $save->toArray();
    }
}
