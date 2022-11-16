<?php

namespace vhallComponent\action\models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use vhallComponent\decouple\models\WebBaseModel;

/**
 *+----------------------------------------------------------------------
 * Class RoleActions
 * 角色-操作关联表模型
 *+----------------------------------------------------------------------
 * @property int $id         主键
 * @property int $role_id    角色ID
 * @property int $action_id  操作ID
 * @property string  $updated_at 更新时间
 * @property string  $created_at 创建时间
 * @property string  $deleted_at
 *
 * @package App\Models
 * @author  ensong.liu@vhall.com
 * @date    2019-01-30 14:05:00
 * @version
 *+----------------------------------------------------------------------
 */
class RoleActionsModel extends WebBaseModel
{
    /**
     * The primary key for the model.
     *
     * @todo 这个主键值比较特殊，纯粹为了满足Roles\actions|hasManyThrough方法
     * @var string
     */
    protected $primaryKey = 'action_id';

    protected $table = 'role_actions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['role_id', 'action_id'];

    /**
     * 模型关联-角色表
     *
     * @return BelongsTo
     * @author ensong.liu@vhall.com
     * @date   2019年02月14日20:17:33
     */
    public function role()
    {
        return $this->belongsTo(RoleModel::class, 'role_id', 'role_id');
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

    /**
     * 新增一条记录
     *
     * @param array $attributes
     *
     * @return RoleActionsModel|null
     * @author ensong.liu@vhall.com
     * @date   2019-02-16 21:09:09
     *
     */
    public function addRow(array $attributes)
    {
        return $this->create($attributes);
    }

    /**
     * 获取一条记录
     *
     * @param array $condition
     * @param array $with
     *
     * @return RoleActionsModel
     * @author ensong.liu@vhall.com
     * @date   2019-02-12 17:09:48
     *
     */
    public function getRow(array $condition = [], array $with = [])
    {
        return $this->where($condition)->with($with)->first();
    }
}
