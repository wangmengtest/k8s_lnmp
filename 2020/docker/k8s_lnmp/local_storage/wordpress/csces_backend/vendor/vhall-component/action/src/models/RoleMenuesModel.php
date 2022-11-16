<?php

namespace vhallComponent\action\models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use vhallComponent\decouple\models\WebBaseModel;

/**
 *+----------------------------------------------------------------------
 * Class RoleMenus
 * 角色-菜单关联表模型
 *+----------------------------------------------------------------------
 *
 * @property int $id         主键
 * @property int $role_id    角色ID
 * @property int $menu_id    菜单ID
 * @property string  $updated_at 修改时间
 * @property string  $created_at 创建时间
 * @property string  $deleted_at
 *
 * @package App\Models
 * @author  ensong.liu@vhall.com
 * @date
 * @see
 * @link
 * @version
 *+----------------------------------------------------------------------
 */
class RoleMenuesModel extends WebBaseModel
{
    /**
     * The primary key for the model.
     *
     * @todo 这个主键值比较特殊，纯粹为了满足Roles\menus|hasManyThrough方法
     * @var string
     */
    protected $primaryKey = 'menu_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['role_id', 'menu_id'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'role_menues';

    /**
     * 模型关联-角色表
     *
     * @return BelongsTo
     * @author ensong.liu@vhall.com
     * @date   2019年02月14日20:17:33
     */
    public function role()
    {
        return $this->belongsTo('vhallComponent\user\models\RoleModel', 'role_id', 'role_id');
    }

    /**
     * 模型关联-菜单表
     *
     * @return BelongsTo
     * @author ensong.liu@vhall.com
     * @date   2019年02月14日20:17:33
     */
    public function menu()
    {
        return $this->belongsTo('vhallComponent\user\models\MenuesModel', 'menu_id', 'menu_id');
    }

    /**
     * 条件构造器
     *
     * @param Builder $model
     * @param array   $condition
     *
     * @return Builder
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 15:14:34
     *
     */
    protected function buildCondition(Builder $model, array $condition): Builder
    {
        $model = parent::buildCondition($model, $condition);

        return $model;
    }
}
