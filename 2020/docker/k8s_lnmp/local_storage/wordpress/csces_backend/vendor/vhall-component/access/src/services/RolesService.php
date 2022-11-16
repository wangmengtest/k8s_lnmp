<?php

namespace vhallComponent\access\services;

use App\Constants\ResponseCode;
use vhallComponent\access\constants\RoleConstant;
use Vss\Common\Services\WebBaseService;

class RolesService extends WebBaseService
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
        return vss_model()->getRoleModel()->create(['name' => $role_name, 'app_id' => $app_id]);
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
    public function update($role_id, $app_id, $role_name, $desc = '')
    {
        $data         = [];
        $data['name'] = $role_name;

        return vss_model()->getRoleModel()->where(['role_id' => $role_id, 'app_id' => $app_id])->update($data);
    }

    /**
     * 删除角色
     *
     * @param $role_id
     * @param $app_id
     *
     * @return int
     */
    public function delete($role_id, $app_id)
    {
        return vss_model()->getRoleModel()->where([
            'role_id' => $role_id,
            'app_id'  => $app_id,
        ])->update(['status' => RoleConstant::DELETE_STATUS]);
    }

    /**
     * 增加角色权限
     *
     * @param $role_id
     * @param $access_id
     *
     * @return bool|mixed
     *
     */
    public function addRolePermission($role_id, $access_id)
    {
        $data = $this->isJsonString($access_id);
        if (false === $data) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        vss_service()->getAccessService()->checkAccessId($data);

        return vss_model()->getRoleAccessModel()->batchCreate($role_id, $data);
    }

    /**
     * 删除角色权限
     *
     * @param $role_id
     * @param $access_id
     *
     * @return int|mixed
     *
     */
    public function deleteRolePermission($role_id, $access_id)
    {
        $data = $this->isJsonString($access_id);
        if (false === $data) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }

        return vss_model()->getRoleAccessModel()->where(['role_id' => $role_id])
            ->whereIn('access_id', $data)->update(['status' => RoleConstant::DELETE_STATUS]);
    }

    /**
     * 获取应用的所有角色列表
     *
     * @param $app_id
     *
     * @return array
     *
     */
    public function getRoleAPPList($app_id)
    {
        $list = vss_model()->getRoleModel()
            ->where(['app_id' => $app_id, 'status' => RoleConstant::NORMAL_STATUS])
            ->get(['role_id', 'name', 'status'])
            ->toArray();
        if (empty($list)) {
            $this->fail(ResponseCode::EMPTY_LIST);
        }

        return $list;
    }

    /**
     * 增加用户角色
     *
     * @param $app_id
     * @param $account_id
     * @param $role_id
     *
     * @return bool|mixed
     *
     */
    public function addRoleForUser($app_id, $account_id, $role_id)
    {
        $data = $this->isJsonString($account_id);
        if (false === $data) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }

        return vss_model()->getUserRoleModel()->batchCreate($role_id, $app_id, $data);
    }

    /**
     * 删除用户角色
     *
     * @param $app_id
     * @param $account_id
     * @param $role_id
     *
     * @return int|mixed
     *
     */
    public function deleteRoleForUser($app_id, $account_id, $role_id)
    {
        $data = $this->isJsonString($account_id);
        if (false === $data) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }

        return vss_model()->getUserRoleModel()->where(['role_id' => $role_id])
            ->whereIn('account_id', $data)->update(['status' => RoleConstant::DELETE_STATUS]);
    }

    /**
     * 通过角色id获取指定角色的权限列表
     *
     * @param $role_id
     * @param $app_id
     *
     * @return array
     */
    public function getAccessListById($role_id, $app_id)
    {
        $data = vss_model()->getRoleAccessModel()->newQuery()
            ->leftJoin('role', 'role_access.role_id', 'role.role_id')
            ->where('role.app_id', $app_id)
            ->where('role.role_id', $role_id)
            //->where('role.status', RoleConstant::NORMAL_STATUS)
            ->where('role_access.status', RoleConstant::NORMAL_STATUS)
            ->selectRaw('role_access.access_id,role.name')
            ->get()
            ->toArray();

        return !empty($data) ? $data : [];
    }

    /**
     * @param $str
     *
     * @return array|bool|mixed
     */
    public function isJsonString($str)
    {
        $jObject = json_decode($str, true);

        return (is_array($jObject)) ? $jObject : false;
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

    public function deleteByIds($roleIds)
    {
        return vss_model()->getRoleModel()->deleteByIds($roleIds);
    }
}
