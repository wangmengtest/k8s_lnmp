<?php

namespace vhallComponent\account\controllers\api;

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


    #public $accountInfo = null;

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
        
        $data = vss_service()->getAccountsService()->login($arr);
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
