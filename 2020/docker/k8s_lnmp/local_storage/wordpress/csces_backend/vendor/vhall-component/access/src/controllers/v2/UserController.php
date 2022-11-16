<?php

namespace vhallComponent\access\controllers\v2;

use vhallComponent\decouple\controllers\BaseController;

/**
 * AccessControllerTrait
 *
 * @uses     yangjin
 * @date     2020-07-31
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class UserController extends BaseController
{
    /**
     * 获取用户权限列表
     *
     */
    public function getAccessListAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'account_id' => 'required',
            'app_id' => 'required',
            'role_name' => 'required'
        ]);

        $this->success(vss_service()->getAccessService()->getAccessCodeByUid($params['account_id'], $params['app_id'], $params['role_name']));
    }

    /**
     * 增加用户角色
     *
     */
    public function addRoleAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'app_id' => 'required',
            'account_id' => 'required',
            'role_id' => 'required',
        ]);
        $this->success(vss_service()->getRolesService()->addRoleForUser($params['app_id'], $params['account_id'], $params['role_id']));
    }

    /**
     * 删除用户角色
     *
     */
    public function deleteRoleAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'app_id' => 'required',
            'account_id' => 'required',
            'role_id' => 'required',
        ]);
        $this->success(vss_service()->getRolesService()->deleteRoleForUser($params['app_id'], $params['account_id'], $params['role_id']));
    }
}
