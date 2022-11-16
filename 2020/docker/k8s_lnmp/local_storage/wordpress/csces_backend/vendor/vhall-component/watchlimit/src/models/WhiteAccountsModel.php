<?php

namespace vhallComponent\watchlimit\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * WhiteAccounts
 *
 * @property int $id
 * @property int $whitename             手机号码
 * @property string  $whitepaas             密码
 * @property int  $il_id                房间
 * @property int  $limit_type           0:默认 1:上报 2:登录 3:白名单
 * @property string  $updated_at
 * @property string  $created_at
 * @property string  $deleted_at
 *
 * @uses     zhangjainwei
 * @date     2020-11-02
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class WhiteAccountsModel extends WebBaseModel
{
    protected $table = 'white_accounts';

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
    protected $fillable = ['whitename', 'whitepaas', 'limit_type', 'il_id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * 白名单手机查询
     *
     * @author ensong.liu@vhall.com
     * @date 2019-05-13 20:27:45
     *
     * @param array $accountIds
     *
     * @return array
     */
    public function whitesearch($phone, $ilId)
    {
        $model = $this->where('whitename', $phone)->where('il_id', $ilId)
            ->get()
            ->toArray();
        return $model;
    }

    /**
     * 批量删除
     *
     * @param $ids
     *
     * @return int
     * @author   ming.wang@vhall.com
     *
     * @uses     wangming
     */
    public function deleteByIds($ids, $force = true)
    {
        $build = self::query()->whereIn('id', $ids);
        if ($force) {
            return $build->forcedelete();
        }
        return $build->delete();
    }
}
