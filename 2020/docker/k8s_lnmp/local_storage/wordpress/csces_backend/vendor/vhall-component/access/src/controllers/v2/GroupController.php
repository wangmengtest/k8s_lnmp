<?php

namespace vhallComponent\access\controllers\v2;

use vhallComponent\decouple\controllers\BaseController;

/**
 * GroupController extends BaseController
 *
 * @uses     yangjin
 * @date     2020-08-06
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class GroupController extends BaseController
{
    /**
     * 创建组
     *
     */
    public function createAction()
    {
        $params    = $this->getParam();
        vss_validator($params, [
            'group_name' => 'required',
            'app_id'     => 'required'
        ]);
        $this->success(vss_service()->getGroupService()->create(
            $params['group_name'],
            $params['app_id']
        ));
    }

    /**
     * 修改组
     *
     */
    public function updateAction()
    {
        $params    = $this->getParam();
        vss_validator($params, [
            'group_id'   => 'required',
            'app_id'     => 'required',
            'group_name' => '',
        ]);
        $this->success(vss_service()->getGroupService()->update(
            $params['group_id'],
            $params['app_id'],
            $params['group_name']
        ));
    }

    /**
     * 删除组
     *
     */
    public function deleteAction()
    {
        $params    = $this->getParam();
        vss_validator($params, [
            'group_id' => 'required',
            'app_id'   => 'required',
        ]);
        $this->success(vss_service()->getGroupService()->delete(
            $params['group_id'],
            $params['app_id']
        ));
    }

    /**
     * 通过应用id获取组列表
     */
    public function getListByAppAction()
    {
        $params    = $this->getParam();
        vss_validator($params, [
            'app_id' => 'required',
        ]);
        $this->success(vss_service()->getGroupService()->getAPPList(
            $params['app_id']
        ));
    }

    /**
     * 增加组权限
     *
     */
    public function addAccessAction()
    {
        $params    = $this->getParam();
        vss_validator($params, [
            'group_id'  => 'required',
            'access_id' => 'required',
        ]);
        $this->success(vss_service()->getGroupService()->addGroupPermission(
            $params['group_id'],
            $params['access_id']
        ));
    }

    /**
     * 删除组权限
     *
     */
    public function deleteAccessAction()
    {
        $params    = $this->getParam();
        vss_validator($params, [
            'group_id'  => 'required',
            'access_id' => 'required',
        ]);
        $this->success(vss_service()->getGroupService()->deleteGroupPermission(
            $params['group_id'],
            $params['access_id']
        ));
    }

    /**
     * 增加用户到指定组
     *
     */
    public function addUserAction()
    {
        $params = $this->getParam();

        vss_validator($params, [
            'group_id'   => 'required',
            'account_id' => 'required',
            'app_id'     => 'required',
        ]);
        $this->success(vss_service()->getGroupService()->addPermissionForUser(
            $params['account_id'],
            $params['group_id'],
            $params['app_id']
        ));
    }

    /**
     *  删除组用户
     *
     */
    public function deleteUserAction()
    {
        $params    = $this->getParam();
        vss_validator($params, [
            'group_id'   => 'required',
            'account_id' => 'required',
            'app_id'     => 'required',
        ]);
        $this->success(vss_service()->getGroupService()->deletePermissionForUser(
            $params['group_id'],
            $params['account_id'],
            $params['app_id']
        ));
    }

    /**
     * 获取组权限
     *
     */
    public function infoAction()
    {
        $params    = $this->getParam();
        vss_validator($params, [
            'group_id' => 'required',
            'app_id'   => 'required',
        ]);
        $this->success(vss_service()->getGroupService()->getGroupListById(
            $params['group_id'],
            $params['app_id']
        ));
    }
}
