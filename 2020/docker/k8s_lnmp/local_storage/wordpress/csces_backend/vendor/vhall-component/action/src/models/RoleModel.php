<?php

namespace vhallComponent\action\models;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use vhallComponent\menu\models\MenuesModel;
use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class RoleModel
 *
 * @package App\Models
 * @property int $role_id
 * @property string  $name       角色名称
 * @property string  $code       角色标识
 * @property string  $desc       描述
 * @property bool $status     状态 0：正常 1：无效
 * @property string  $app_id     应用id
 * @property bool $level      角色级别
 * @property string  $updated_at 更新时间
 * @property string  $created_at 创建时间
 * @property string  $deleted_at
 */
class RoleModel extends WebBaseModel
{
    protected $table      = 'role';

    protected $primaryKey = 'role_id';

    protected $attributes = [
        'name'       => '',
        'code'       => '',
        'desc'       => '',
        'app_id'     => '',
        'status'     => 1,
        'level'      => 0,
        'created_at' => '1970-01-01 00:00:00',
        'updated_at' => '1970-01-01 00:00:00',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'code', 'desc', 'app_id', 'level', 'status'];

    /**
     * 角色状态:1>开启,0>关闭
     */
    const STATUS_DISABLED = 0;

    const STATUS_ENABLED = 1;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['status_str'];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        //绑定插入时间
        static::creating(function ($model) {
            $model->code = strtoupper(str_random(6));
        });

        //绑定删除事件
        static::deleting(function ($model) {
            //重置角色下管理员角色
            $condition  = ['role_id' => $model->role_id];
            $attributes = ['role_id' => 0];
            $adminsModel = self::getAdminsModel();
            $adminsModel::getInstance()->where($condition)->update($attributes);

            RoleActionsModel::getInstance()->where($condition)->forceDelete();
            RoleMenuesModel::getInstance()->where($condition)->forceDelete();
        });
    }

    /**
     * 模型关联-操作表
     *
     * @return HasManyThrough
     * @author ensong.liu@vhall.com
     * @date   2019年02月14日20:48:58
     */
    public function actions()
    {
        return $this->hasManyThrough(ActionsModel::class, RoleActionsModel::class, 'role_id', 'action_id');
    }

    /**
     * 模型关联-菜单表
     *
     * @return HasManyThrough
     * @author ensong.liu@vhall.com
     * @date   2019年02月14日20:49:14
     */
    public function menues()
    {
        return $this->hasManyThrough(MenuesModel::class, RoleMenuesModel::class, 'role_id', 'menu_id');
    }

    /**
     * 状态字符串-访问器
     *
     * @return string
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 14:09:50
     */
    public function getStatusStrAttribute(): string
    {
        return self::getStatusStr($this->status);
    }

    /**
     * 获取状态字符串
     *
     * @param int $status
     *
     * @return null|string
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 11:04:35
     *
     */
    public static function getStatusStr(int $status): string
    {
        switch ($status) {
            case self::STATUS_DISABLED:
                $string = '关闭';
                break;
            case self::STATUS_ENABLED:
                $string = '开启';
                break;
            default:
                $string = null;
        }

        return $string;
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
        //角色名称
        $model->when($condition['name'] ?? '', function (Builder $query) use ($condition) {
            $query->where('name', $condition['name']);
        });
        //关键字
        $model->when($condition['keyword'] ?? '', function (Builder $query) use ($condition) {
            $query->where('role.name', 'like', sprintf('%%%s%%', $condition['keyword']))
                ->orWhere('role.code', 'like', sprintf('%%%s%%', $condition['keyword']));
        });
        //时间范围-开始
        $model->when($condition['begin_time'] ?? '', function (Builder $query) use ($condition) {
            $query->where('role.created_at', '>=', $condition['begin_time']);
        });
        //时间范围-结束
        $model->when($condition['end_time'] ?? '', function (Builder $query) use ($condition) {
            $query->where('role.created_at', '<=', date('Y-m-d 23:59:59', strtotime($condition['end_time'])));
        });
        //状态
        $model->when(isset($condition['status']) && $condition['status'] != ''
            && in_array($condition['status'], [
                self::STATUS_DISABLED,
                self::STATUS_ENABLED,
            ]), function (Builder $query) use ($condition) {
                $query->where('role.status', '=', $condition['status']);
            });

        return $model;
    }

    /**
     * 刷新角色菜单权限
     *
     * @param int   $roleId
     * @param array $menuIds
     *
     * @return bool|null
     * @throws Exception
     * @author ensong.liu@vhall.com
     * @date   2019-02-17 02:03:50
     *
     */
    public function refreshMenus(int $roleId, array $menuIds): bool
    {
        $this->getConnection()->beginTransaction();
        try {
            vss_model()->getRoleMenuesModel()->where(['role_id' => $roleId])->forceDelete();
            $menuIds = array_unique($menuIds);
            foreach ($menuIds as $menuId) {
                $menuInfo = vss_model()->getMenuesModel()->getRow(['menu_id' => $menuId]);
                if ($menuInfo) {
                    vss_model()->getRoleMenuesModel()->insert([
                        'role_id' => $roleId,
                        'menu_id' => $menuId,
                    ]);
                }
            }
            $this->getConnection()->commit();

            return true;
        } catch (Exception $e) {
            $this->getConnection()->rollBack();

            return false;
        }
    }

    /**
     * 刷新角色操作权限
     *
     * @param int   $roleId
     * @param array $actionIds
     *
     * @return bool|null
     * @throws Exception
     * @author ensong.liu@vhall.com
     * @date   2019-02-17 02:03:50
     *
     */
    public function refreshActions(int $roleId, array $actionIds): bool
    {
        $this->getConnection()->beginTransaction();
        try {
            if ($this->getRow(['role_id' => $roleId])) {
                vss_model()->getRoleActionsModel()->where(['role_id' => $roleId])->forceDelete();
            }
            $actionIds = array_unique($actionIds);
            foreach ($actionIds as $actionId) {
                $actionInfo = vss_model()->getActionsModel()->getRow(['action_id' => $actionId]);
                if ($actionInfo) {
                    vss_model()->getRoleActionsModel()->addRow([
                        'role_id'   => $roleId,
                        'action_id' => $actionId,
                    ]);
                }
            }
            $this->getConnection()->commit();

            return true;
        } catch (Exception $e) {
            $this->getConnection()->rollBack();

            return false;
        }
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
    public static function deleteByIds($ids)
    {
        return self::query()->whereIn('role_id', $ids)->delete();
    }
}
