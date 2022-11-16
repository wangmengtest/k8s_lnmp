<?php

namespace vhallComponent\access\controllers\v2;

use vhallComponent\decouple\controllers\BaseController;

/**
 * RoleController extends BaseController
 *
 * @uses     yangjin
 * @date     2020-07-31
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class RoleController extends BaseController
{
    /**
     * 创建角色
     *
     *
     */
    public function createAction()
    {
        $params    = $this->getParam();
        vss_validator($params, [
            'role_name' => 'required',
            'app_id'    => 'required',
        ]);

        $this->success(vss_service()->getRolesService()->create(
            $params['role_name'],
            $params['app_id']
        ));
    }

    /**
     * 修改角色
     *
     *
     */
    public function updateAction()
    {
        $params    = $this->getParam();
        vss_validator($params, [
            'role_id'   => 'required',
            'app_id'    => 'required',
            'role_name' => 'required',
        ]);

        $this->success(vss_service()->getRolesService()->update(
            $params['role_id'],
            $params['app_id'],
            $params['role_name']
        ));
    }

    /**
     * 删除角色
     *
     *
     */
    public function deleteAction()
    {
        $params    = $this->getParam();
        vss_validator($params, [
            'role_id' => 'required',
            'app_id'  => 'required',
        ]);

        $this->success(vss_service()->getRolesService()->delete(
            $params['role_id'],
            $params['app_id']
        ));
    }

    /**
     * 增加角色权限
     *
     *
     */
    public function addAccessAction()
    {
        $params    = $this->getParam();
        vss_validator($params, [
            'role_id'   => 'required',
            'access_id' => 'required',
        ]);

        $this->success(vss_service()->getRolesService()->addRolePermission(
            $params['role_id'],
            $params['access_id']
        ));
    }

    /**
     * 删除角色权限
     *
     *
     */
    public function deleteAccessAction()
    {
        $params    = $this->getParam();
        vss_validator($params, [
            'role_id'   => 'required',
            'access_id' => 'required',
        ]);

        $this->success(vss_service()->getRolesService()->deleteRolePermission(
            $params['role_id'],
            $params['access_id']
        ));
    }

    /**
     * 获取应用角色列表
     *
     *
     */
    public function getListByAppAction()
    {
        $params    = $this->getParam();
        vss_validator($params, [
            'app_id' => 'required',
        ]);

        $this->success(vss_service()->getRolesService()->getRoleAPPList(
            $params['app_id']
        ));
    }

    /**
     * 通过角色获取权限详情
     *
     *
     */
    public function infoAction()
    {
        $params    = $this->getParam();
        vss_validator($params, [
            'role_id' => 'required',
            'app_id'  => 'required',
        ]);

        $this->success(vss_service()->getRolesService()->getAccessListById(
            $params['role_id'],
            $params['app_id']
        ));
    }
}
