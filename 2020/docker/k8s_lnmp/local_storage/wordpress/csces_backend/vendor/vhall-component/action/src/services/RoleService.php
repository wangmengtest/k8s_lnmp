<?php

namespace vhallComponent\action\services;

use App\Constants\ResponseCode;
use vhallComponent\action\constants\ActionConstant;
use vhallComponent\action\models\RoleModel;
use Vss\Common\Services\WebBaseService;
use Vss\Exceptions\ResponseException;

/**
 * RoleServiceTrait
 *
 * @author   jin.yangjin@vhall.com
 * @uses     yangjin
 * @date     2020-08-06
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class RoleService extends WebBaseService
{
    /**
     * 增加角色
     *
     * @param $role_name
     * @param $app_id
     *
     * @return mixed|RoleModel
     */
    public function create($role_name, $app_id = '', $desc = '', $level = 0)
    {
        vss_model()->getRoleModel()->getConnection()->beginTransaction();
        try {
            //角色名称是否存在
            if (vss_model()->getRoleModel()->getCount(['name' => $role_name])) {
                $this->fail(ResponseCode::BUSINESS_ROLE_EXIST);
            }
            $roleModel = vss_model()->getRoleModel();
            //保存数据
            $attributes = [
                'name'   => $role_name,
                'desc'   => $desc,
                'level'  => $level,
                'status' => $roleModel::STATUS_ENABLED,
            ];
            $roleInfo   = vss_model()->getRoleModel()->addRow($attributes);
            if (!$roleInfo) {
                $this->fail(ResponseCode::BUSINESS_CREATE_FAILED);
            }

            //设置初始角色菜单权限
            foreach (ActionConstant::INIT_ROLE_MENU_LIST as $menuId) {
                if (vss_model()->getMenuesModel()->getCount(['menu_id' => $menuId]) > 0) {
                    $attributes = [
                        'role_id' => $roleInfo['role_id'],
                        'menu_id' => $menuId,
                    ];
                    vss_model()->getRoleMenuesModel()->addRow($attributes);
                }
            }

            //设置初始角色操作权限
            foreach (ActionConstant::INIT_ROLE_ACTION_LIST as $actionId) {
                if (vss_model()->getActionsModel()->getCount(['action_id' => $actionId]) < 0) {
                    $attributes = [
                        'role_id'   => $roleInfo['role_id'],
                        'action_id' => $actionId,
                    ];
                    vss_model()->getRoleActionsModel()->addRow($attributes);
                }
            }

            vss_model()->getRoleModel()->getConnection()->commit();

            //返回数据
            return $roleInfo;
        } catch (\Exception $e) {
            vss_model()->getRoleModel()->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * 修改角色
     *
     * @param $role_id
     * @param $app_id
     * @param $role_name
     *
     * @return int
     */
    public function update($role_id, $role_name, $desc = '')
    {
        $data         = [];
        $data['name'] = $role_name;
        $data['desc'] = $desc;
        //角色名称是否存在
        if (vss_model()->getRoleModel()->getCount(['name' => $role_name])) {
            $this->throwError('60032', '角色名称已存在');
        }
        return vss_model()->getRoleModel()->where(['role_id' => $role_id])->update($data);
    }

    /**
     * @param $params
     *
     * @return mixed
     * @author   ming.wang@vhall.com
     *
     * @uses     wangming
     */
    public function getOne($params)
    {
        //管理员信息
        $condition = ['role_id' => $params['role_id']];
        $with      = ['menues', 'actions'];
        $roleInfo  = vss_model()->getRoleModel()->getRow($condition, $with);

        if (empty($roleInfo)) {
            $this->fail(ResponseCode::EMPTY_ROLE);
        }

        return $roleInfo;
    }

    /**
     * @param $params
     *
     * @return mixed
     * @author   ming.wang@vhall.com
     *
     * @uses     wangming
     */
    public function getList($params)
    {
        $condition = [
            'keyword'    => $params['keyword'] ?? '',
            'begin_time' => $params['begin_time'] ?? '',
            'end_time'   => $params['end_time'] ?? '',
            'status'     => $params['status'] ?? '',
        ];

        $condition = array_filter($condition);

        return vss_model()->getRoleModel()->getList($condition, [], $params['page'] ?? 0);
    }

    /**
     * 角色删除
     *
     * @param $roleIds
     *
     * @return int
     */
    public function deleteByIds($roleIds)
    {
        return vss_model()->getRoleModel()->deleteByIds($roleIds);
    }
}
