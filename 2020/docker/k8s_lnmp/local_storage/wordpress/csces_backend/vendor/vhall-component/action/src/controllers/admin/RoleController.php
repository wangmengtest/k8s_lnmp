<?php

namespace vhallComponent\action\controllers\admin;

use App\Constants\ResponseCode;
use vhallComponent\action\constants\ActionConstant;
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
     * 角色-信息
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-02-16 13:47:11
     * @method GET
     * @request int role_id    角色ID
     */
    public function getAction()
    {
        $params = $this->getParam();
        //参数列表
        vss_validator($params, [
            'role_id' => 'required',
        ]);

        $result = vss_service()->getRoleService()->getOne($this->getParam());

        $this->success($result);
    }

    /**
     * 角色-列表
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 16:54:37
     * @method  GET
     * @request int     page        页码
     * @request string  keyword     关键字
     * @request string  begin_time  开始时间
     * @request string  end_time    结束时间
     * @request int     status      状态
     */
    public function listAction()
    {
        $params = $this->getParam();
        //参数列表
        vss_validator($params, [
            'page'       => '',
            'keyword'    => '',
            'begin_time' => '',
            'end_time'   => '',
            'status'     => '',
        ]);

        $result = vss_service()->getRoleService()->getList($this->getParam());
        $this->success($result);
    }

    /**
     * 角色-添加
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 16:54:37
     * @method  POST
     * @request string  name    角色名称
     * @request string  desc    角色描述
     */
    public function addAction()
    {
        $params = $this->getParam();
        //参数列表
        vss_validator($params, [
            'name'  => 'required',
            'desc'  => '',
            'level' => '',
        ]);

        $result = vss_service()->getRoleService()->create(
            $params['name'],
            $params['app_id'],
            $params['desc'] ?? '',
            $params['level'] ?? 0
        );

        $this->success($result);
    }

    /**
     * 角色-删除
     *
     * @return void
     *
     * @author ensong.liu@vhall.com
     * @date   2019-01-29 16:54:37
     */
    public function deleteAction()
    {
        $params = vss_validator($this->getParam(), [
            'role_ids' => 'required',
        ]);

        $roleIds = explode(',', $params['role_ids']);
        $result  = vss_service()->getRoleService()->deleteByIds($roleIds);

        $this->success($roleIds);
    }

    /**
     * 角色-编辑
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 16:54:37
     * @method  POST
     * @request int     role_id 角色ID
     * @request string  name    角色名称
     * @request string  desc    角色描述
     */
    public function editAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'role_id' => 'required',
            'name'    => 'required',
            'desc'    => '',
        ]);

        //参数列表
        $roleId = $params['role_id'];
        $name   = $params['name'];
        $desc   = $params['desc'];

        $result = vss_service()->getRoleService()->update($roleId, $name, $desc);
        $this->success($result);
    }

    /**
     * 角色-状态
     *
     * @return void
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 16:54:37
     * @method  POST
     * @request int  role_id 角色ID
     * @request int  status  角色状态
     */
    public function editStatusAction()
    {
        $params = $this->getParam();
        //参数列表
        vss_validator($params, [
            'role_id' => 'required',
            'status'  => '',
        ]);
        //参数列表
        $roleId = $params['role_id'];
        $status = $params['status'];

        //角色信息
        $condition = [
            'role_id' => $roleId,
        ];
        $roleInfo  = vss_model()->getRoleModel()->getRow($condition);
        if (empty($roleInfo)) {
            $this->fail(ResponseCode::EMPTY_ROLE);
        }

        //保存数据
        $attributes = ['status' => $status];
        if ($roleInfo->updateRow($roleId, $attributes) == false) {
            $this->fail(ResponseCode::BUSINESS_EDIT_FAILED);
        }

        $this->success();
    }

    /**
     * 角色-编辑菜单权限
     *
     * @return void
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 16:54:37
     * @method  POST
     * @request int     role_id  角色ID
     * @request string  menu_ids 菜单ID，逗号隔开
     */
    public function editMenuesAction()
    {
        $params = $this->getParam();
        //参数列表
        vss_validator($params, [
            'role_id'  => 'required',
            'menu_ids' => 'required',
        ]);
        $menuIds = $params['menu_ids'];
        //角色信息
        $condition = [
            'role_id' => $roleId = $params['role_id'],
        ];
        $roleInfo  = vss_model()->getRoleModel()->getRow($condition);
        if (empty($roleInfo)) {
            $this->fail(ResponseCode::EMPTY_ROLE);
        }

        //保存数据
        $menuIdList = explode(',', (string)$menuIds);
        $menuIdList = array_merge($menuIdList, ActionConstant::INIT_ROLE_MENU_LIST);
        $refreshed  = vss_model()->getRoleModel()->refreshMenus($roleId, $menuIdList);
        if ($refreshed == false) {
            $this->fail(ResponseCode::BUSINESS_EDIT_FAILED);
        }

        $this->success();
    }

    /**
     * 角色-编辑操作权限
     *
     * @return void
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 16:54:37
     * @method  POST
     * @request int     role_id  角色ID
     * @request string  action_ids 操作ID，逗号隔开
     */
    public function editActionsAction()
    {
        $params = $this->getParam();
        //参数列表
        vss_validator($params, [
            'role_id'    => 'required',
            'action_ids' => 'required',
        ]);

        $roleId    = $params['role_id'];
        $actionIds = $params['action_ids'];

        //角色信息
        $condition = [
            'role_id' => $roleId,
        ];
        $roleInfo  = vss_model()->getRoleModel()->getRow($condition);
        if (empty($roleInfo)) {
            $this->fail(ResponseCode::EMPTY_ROLE);
        }

        //保存数据
        $actionList = explode(',', (string)$actionIds);
        $actionList = array_merge($actionList, ActionConstant::INIT_ROLE_ACTION_LIST);
        $refreshed  = vss_model()->getRoleModel()->refreshActions($roleId, $actionList);
        if ($refreshed == false) {
            $this->fail(ResponseCode::BUSINESS_EDIT_FAILED);
        }

        $this->success();
    }
}
