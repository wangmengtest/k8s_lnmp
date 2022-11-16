<?php

namespace vhallComponent\action\models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use vhallComponent\decouple\models\WebBaseModel;

/**
 *+----------------------------------------------------------------------
 * Class ActionsModel
 * 操作表模型
 *+----------------------------------------------------------------------
 *
 * @property int $action_id       主键
 * @property string  $controller_name 控制器名称
 * @property string  $action_name     操作名称
 * @property int $pid             父id
 * @property string  $desc            描述
 * @property string  $updated_at      更新时间
 * @property string  $created_at      创建时间
 * @property string  $deleted_at
 *
 * @package App\Models
 * @author  ensong.liu@vhall.com
 * @date    2019-01-30 14:03:22
 * @version
 *+----------------------------------------------------------------------
 */
class ActionsModel extends WebBaseModel
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'action_id';

    protected $table = 'actions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['controller_name', 'action_name', 'pid', 'desc'];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        //绑定删除事件
        static::deleted(function ($model) {
            //删除角色权限操作记录
            $condition = ['action_id' => $model->action_id];
            $roleActionsModel = self::getRoleActionsModel();
            $roleActionsModel::getInstance()->where($condition)->delete();
        });
    }

    /**
     * @param Collection $list
     * @param int        $pid
     *
     * @return Collection|null
     * @author ensong.liu@vhall.com
     * @date   2019-01-31 15:41:54
     *
     */
    public static function getTreeList($list, int $pid = 0)
    {
        $treeCollection = Collection::make();
        foreach ($list as $key => $value) {
            if ($value['pid'] == $pid) {
                $value['children'] = self::getTreeList($list, $value['action_id']);
                $treeCollection->add($value);
            }
        }

        return $treeCollection;
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

        //角色菜单
        $model->when($condition['role_id'] ?? '', function (Builder $query) use ($condition) {
            $roleActionsModel = self::getRoleActionsModel();
            $query->leftJoin($roleActionsModel::getInstance()
                    ->getTable() . ' as role_actions', 'actions.action_id', '=', 'role_actions.action_id')
                ->where('role_actions.role_id', '=', $condition['role_id']);
        });
        //父id
        $model->when($condition['pid'] ?? '', function (Builder $query) use ($condition) {
            $query->where('actions.pid', '=', intval($condition['pid']));
        });

        return $model;
    }

    /**
     * 根据id批量删除
     *
     * @param array $ids
     *
     * @return mixed
     * @author   ming.wang@vhall.com
     *
     * @uses     wangming
     */
    public static function delByIds(array $ids)
    {
        return self::query()->whereIn('action_id', $ids)->delete();
    }

    /**
     *
     *
     * @param array $data
     *
     * @uses     wangming
     * @author   ming.wang@vhall.com
     */
    public static function firstOrCreate(array $data)
    {
        return self::query()->firstOrCreate($data);
    }
}
