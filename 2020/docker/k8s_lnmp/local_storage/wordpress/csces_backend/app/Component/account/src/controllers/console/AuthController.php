<?php

namespace App\Component\account\src\controllers\console;

use vhallComponent\decouple\controllers\BaseController;
use Illuminate\Support\Arr;

/**
 * AuthController extends BaseController
 */
class AuthController extends BaseController
{
    /**
     * 登录请求
     *
     *
     * @throws Exception
     * @author ensong.liu@vhall.com
     * @date   2019-05-07 16:07:34
     * @string
     */
    public function loginAction()
    {
        $params = $this->getParam();
        $rule = [
            'username' => 'required',
            'password' => 'required|min:6|max:30',
        ];
        $arr = vss_validator($params, $rule);
        $data = vss_service()->getAccountsService()->consoleLoginInfo($arr['username'], $arr['password']);
        $this->success($data);
    }

    /**
     * 退出
     *
     * @throws Exception
     * @author ensong.liu@vhall.com
     * @date   2019-05-07 16:38:52
     */
    public function logoutAction()
    {
        if ($this->accountInfo) {
            vss_service()->getAccountsService()->logout($this->accountInfo);
        }
        $this->success();
    }

    /**
     * @throws Exception
     */
    public function visitorAction()
    {
        $this->success(vss_service()->getAccountsService()->visitor());
    }

    /**
     * 获取用户登录信息
     * @throws Exception
     */
    public function loginInfoAction()
    {
        $info = vss_model()->getAnchorExtendsModel()->getInfoByAccountId($this->accountInfo['account_id']);
        $this->success([
            'account_id'  => $this->accountInfo['account_id'],
            'username'    => $this->accountInfo['username'],
            'phone'       => $this->accountInfo['phone'],
            'connect_num' => $info['connect_num'] ?? '0',
            'nickname'    => $this->accountInfo['nickname'],
            'token'       => $this->accountInfo['token'],
            'app_id'      => vss_service()->getTokenService()->getAppId(),
        ]);
    }
}
