<?php

namespace vhallComponent\menu\models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use vhallComponent\action\models\RoleMenuesModel;
use vhallComponent\decouple\models\WebBaseModel;

/**
 *+----------------------------------------------------------------------
 * Class MenuesModel
 * 菜单表模型
 *+----------------------------------------------------------------------
 *
 * @property int $menu_id    主键
 * @property string  $name       菜单名称
 * @property string  $url        菜单链接
 * @property int $pid        父ID
 * @property bool $sort       排序
 * @property string  $updated_at 修改时间
 * @property string  $created_at 创建时间
 * @property string  $deleted_at
 *
 * @package App\Models
 * @author  ensong.liu@vhall.com
 * @date    2019-01-30 14:02:01
 * @version
 *+----------------------------------------------------------------------
 */
class MenuesModel extends WebBaseModel
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'menu_id';

    protected $table      = 'menues';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'url', 'pid', 'sort'];

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
            //删除角色权限菜单记录
            $condition = ['menu_id' => $model->menu_id];
            RoleMenuesModel::getInstance()->where($condition)->delete();
            //删除子菜单
            $condition = ['pid' => $model->menu_id];
            MenuesModel::getInstance()->where($condition)->delete();
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
                $value['children'] = self::getTreeList($list, $value['menu_id']);
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
        //父菜单
        $model->when($condition['menu_id'] ?? '', function ($query) use ($condition) {
            $query->where('menues.menu_id', '=', $condition['menu_id']);
        });
        //父菜单
        $model->when($condition['pid'] ?? '', function ($query) use ($condition) {
            $query->where('menues.pid', '=', $condition['pid']);
        });
        //角色菜单
        $model->when($condition['role_id'] ?? '', function ($query) use ($condition) {
            $query->leftJoin(RoleMenuesModel::getInstance()
                    ->getTable() . ' as role_menues', 'menues.menu_id', '=', 'role_menues.menu_id')
                ->where('role_menues.role_id', '=', $condition['role_id']);
        });

        return $model;
    }
}
