<?php

namespace vhallComponent\admin\controllers\admin;

use vhallComponent\decouple\controllers\BaseController;

/**
 * AuthController extends BaseController
 *
 * @uses     yangjin
 * @date     2020-09-03
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class AuthController extends BaseController
{
    /**
     * 验证-登录
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 14:25:43
     * @method  GET
     * @request string  admin_name  登录名
     * @request string  password    密码
     */
    public function loginAction()
    {
        $params = $this->getParam();
        //参数列表
        vss_validator($params, [
            'admin_name' => 'required',
            'password'   => 'required',
        ]);

        $adminName = $params['admin_name'];
        $password  = $params['password'];
        $data      = vss_service()->getAdminService()->login($adminName, $password);

        $this->success($data);
    }

    /**
     * 验证-退出
     *
     * @return void
     * @author ensong.liu@vhall.com
     * @date   2019-01-29 14:25:43
     * @method  GET|POST
     */
    public function logoutAction()
    {
        vss_service()->getAdminService()->loginout($this->admin['admin_id']);
        $this->success();
    }
}
