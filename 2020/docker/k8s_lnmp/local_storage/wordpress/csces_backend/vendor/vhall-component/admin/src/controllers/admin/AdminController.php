<?php

namespace vhallComponent\admin\controllers\admin;

use vhallComponent\decouple\controllers\BaseController;

/**
 * AdminController extends BaseController
 *
 * @uses     yangjin
 * @date     2020-08-21
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class AdminController extends BaseController
{
    /**
     * 管理员-管理员信息
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-02-16 13:47:11
     * @method GET
     * @request int admin_id    管理员ID
     */
    public function getAction()
    {
        //参数列表
        $adminId = $this->getParam('admin_id');

        //返回数据
        $data = vss_service()->getAdminService()->get($adminId);
        $this->success($data);
    }

    /**
     * 管理员-列表
     *
     * @return void
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:31:43
     * @method  GET
     * @request int     page        页码
     * @request string  keyword     关键字
     * @request string  begin_time  开始时间
     * @request string  end_time    结束时间
     * @request int     role_id     角色ID
     * @request int     status      状态
     */
    public function listAction()
    {
        //参数列表
        $page      = $this->getParam('page');
        $keyword   = $this->getParam('keyword');
        $beginTime = $this->getParam('begin_time');
        $endTime   = $this->getParam('end_time');
        $roleId    = $this->getParam('role_id');
        $status    = $this->getParam('status');

        //返回数据
        $data = vss_service()->getAdminService()->list($page, $keyword, $beginTime, $endTime, $roleId, $status);
        $this->success($data);
    }

    /**
     * 管理员-导出
     *
     * @return void
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:31:43
     * @method  GET
     * @request string  keyword     关键字
     * @request string  begin_time  开始时间
     * @request string  end_time    结束时间
     * @request int     role_id     角色ID
     * @request int     status      状态
     */
    public function exportListAction()
    {
        //参数列表
        $keyword   = $this->getParam('keyword');
        $beginTime = $this->getParam('begin_time');
        $endTime   = $this->getParam('end_time');
        $roleId    = $this->getParam('role_id');
        $status    = $this->getParam('status');

        vss_service()->getAdminService()->exportList($keyword, $beginTime, $endTime, $roleId, $status);
    }

    /**
     * 管理员-删除
     *
     * @return void
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:31:43
     * @method  POST
     * @request int admin_id    管理员ID
     */
    public function deleteAction()
    {
        //参数列表
        $adminIds = $this->getParam('admin_ids');

        //删除管理员记录
        $data = vss_service()->getAdminService()->delete($adminIds);
        $this->success($data);
    }

    /**
     * 管理员-添加
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:31:43
     * @method  POST
     * @request string  admin_name          管理员登录名
     * @request string  nick_name           管理员昵称
     * @request string  password            管理员密码
     * @request string  confirm_password    确认管理员密码
     * @request string  mobile              手机号码
     * @request string  email               邮箱地址
     * @request int     role_id             角色ID
     */
    public function addAction()
    {
        //参数列表
        $adminName       = $this->getParam('admin_name');
        $nickName        = str_random(10);//$this->getParam('nick_name');
        $password        = $this->getParam('password');
        $confirmPassword = $this->getParam('confirm_password');
        $mobile          = $this->getParam('mobile');
        $email           = $this->getParam('email');
        $roleId          = $this->getParam('role_id');

        //返回数据
        $data = vss_service()->getAdminService()->add(
            $adminName,
            $nickName,
            $password,
            $confirmPassword,
            $mobile,
            $email,
            $roleId
        );
        $this->success($data);
    }

    /**
     * 管理员-编辑
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:31:43
     * @method  POST
     * @request int     admin_id    管理员ID
     * @request string  admin_name  管理员登录名
     * @request string  nick_name   管理员昵称
     * @request string  password    密码
     * @request string  mobile      手机号码
     * @request string  email       邮箱地址
     * @request int     role_id     角色ID
     */
    public function editAction()
    {
        //参数列表
        $adminId         = $this->getParam('admin_id');
        $adminName       = $this->getParam('admin_name');
        $nickName        = str_random(10);//$this->getParam('nick_name');
        $password        = $this->getParam('password');
        $confirmPassword = $this->getParam('confirm_password');
        $mobile          = $this->getParam('mobile');
        $email           = $this->getParam('email');
        $roleId          = $this->getParam('role_id');

        vss_service()->getAdminService()->edit(
            $adminId,
            $adminName,
            $nickName,
            $password,
            $confirmPassword,
            $mobile,
            $email,
            $roleId
        );

        $this->success();
    }

    /**
     * 管理员-修改密码
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:31:43
     * @method  POST
     * @request string  password          管理员密码
     * @request string  confirm_password  确认密码
     * @request string
     */
    public function editPasswordAction()
    {
        //参数列表
        $adminId         = $this->getParam('admin_id');
        $password        = $this->getParam('password');
        $confirmPassword = $this->getParam('confirm_password');

        vss_service()->getAdminService()->editPassword($adminId, $password, $confirmPassword);
        $this->success();
    }

    /**
     * 管理员-修改状态
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:31:43
     * @method  POST
     * @request int admin_id    管理员ID
     * @request int status      管理员状态
     */
    public function editStatusAction()
    {
        //参数列表
        $adminId = $this->getParam('admin_id');
        $status  = $this->getParam('status');

        vss_service()->getAdminService()->editStatus($adminId, $status);
        $this->success();
    }
}
