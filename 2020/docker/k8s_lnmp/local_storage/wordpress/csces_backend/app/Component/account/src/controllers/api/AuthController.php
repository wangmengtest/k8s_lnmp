<?php

namespace App\Component\account\src\controllers\api;

use vhallComponent\decouple\controllers\BaseController;
use Illuminate\Support\Arr;

/**
 * AccountControllerTrait
 *
 * @uses     yangjin
 * @date     2020-07-30
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class AuthController extends BaseController
{
    public function testCreateAction()
    {
        $params = $this->getParam();
        $rule = [
            'username' => 'required',
            'phone'    => 'required',
            'org'      => 'string',
            'dept'     => 'string',
            'role_id'  => 'int'
        ];
        $arr = vss_validator($params, $rule);
        $data = vss_service()->getAccountsService()->createAccount($arr);
        $this->success($data);
    }

    public function getEnvAction()
    {
        print_r($_ENV);
        echo '----------------------------' . PHP_EOL;
        $path = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))));
        $files = file_get_contents($path . '/.env');
        echo "<pre>";
        echo $files;
        echo "</pre>";
    }

    /**
     * 登录
     *
     * @return void
     * @author ensong.liu@vhall.com
     * @date   2019-05-08 16:02:14
     */
    public function autoLoginAction()
    {
        $params = $this->getParam();
        $rule = [
            'username' => 'required',
            'sign'     => 'required',
        ];
        $arr = vss_validator($params, $rule);
        $data = vss_service()->getAccountsService()->autoLogin($arr['username']);
        $this->success($data);
    }

    /**
     * 登录
     *
     * @return void
     * @author ensong.liu@vhall.com
     * @date   2019-05-08 16:02:14
     */
    public function loginAction()
    {
        $params = $this->getParam();
        $rule = [
            'phone'    => 'required',
            'code'     => 'required',
            'nickname' => 'required',
            'type'     => '',
        ];
        $arr = vss_validator($params, $rule);

        $data = vss_service()->getAccountsService()->login($arr);
        $this->success($data);
    }

    /**
     * 观众登录
     *
     * @return void
     * @author ensong.liu@vhall.com
     * @date   2019-05-08 16:02:14
     */
    public function loginWatchAction()
    {
        $params = $this->getParam();
        $rule = [
            'phone'    => 'required',
            'code'     => 'required',
            'nickname' => 'required',
            'type'     => '',
            'il_id'    => '',
        ];
        $arr = vss_validator($params, $rule);

        $checkCode = (env('APP_ENV') == 'production') ? 1 : 0;
        $data = vss_service()->getAccountsService()->login($arr, $checkCode);
        $this->success($data);
    }

    /**
     * 第三方网站调用登陆
     *
     */
    public function thirdLoginAction()
    {
        $data = vss_service()->getAccountsService()->thirdLogin($this->getParam());
        $this->success($data);
    }

    /**
     * 游客
     * @throws Exception
     */
    public function visitorAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'il_id' => 'required',
        ]);
        $this->success(vss_service()->getAccountsService()->visitor($this->getParam('il_id')));
    }

    /**
     * 退出
     *
     * @return void
     * @author ensong.liu@vhall.com
     * @date   2019-05-09 00:38:19
     */
    public function logoutAction()
    {
        if ($this->accountInfo) {
            vss_service()->getAccountsService()->logout($this->accountInfo);
        }
        $this->success();
    }
}
