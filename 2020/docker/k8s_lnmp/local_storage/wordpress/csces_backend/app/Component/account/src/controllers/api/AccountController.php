<?php

namespace App\Component\account\src\controllers\api;

use vhallComponent\decouple\controllers\BaseController;
use App\Component\account\src\constants\AccountConstant;

/**
 * AccountController extends BaseController
 *
 * @uses     yangjin
 * @date     2020-07-30
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class AccountController extends BaseController
{
    /**
     * 获取用户信息
     *
     * @author ensong.liu@vhall.com
     * @date 2019-05-09 15:43:36
     * @return void
     */
    public function getInfoAction()
    {
        $data = [];
        $data['account_id'] = $this->accountInfo['account_id'];
        $data['username'] = $this->accountInfo['username'];
        $data['phone'] = $this->accountInfo['phone'];
        $data['nickname'] = $this->accountInfo['nickname'];
        $data['token'] = $this->accountInfo['token'];
        $data['app_id'] = $this->accountInfo['app_id'];

        $this->success($data);
    }

    /**
     * 修改用户昵称
     *
     * @author ensong.liu@vhall.com
     * @date 2019-05-09 15:44:57
     * @return void
     */
    public function setAction()
    {
        $nickname = $this->getParam('nickname');

        vss_service()->getAccountsService()->setNickname($this->accountInfo['account_id'], $nickname);

        //修改昵称后，更新用户信息
        $this->accountInfo['nickname'] = $nickname;
        if (vss_redis()->exists($this->accountInfo['token'])) {
            $accountInfo = json_encode($this->accountInfo);
            vss_redis()->set($this->accountInfo['token'], $accountInfo, AccountConstant::TOKEN_TIME);
        }

        $data = ['nickname' => $nickname];
        $this->success($data);
    }

    /**
     * 获取accessToken
     * 三种用户权限,对应三种权限的token
     * data_collect_manage 在使用数据收集服务SDK 允许管理问卷 时传入此参数，参数值目前只支持传入all
     * data_collect_submit 在使用数据收集服务SDK 允许提交问卷答卷 时传入此参数，参数值目前只支持传入all
     * data_collect_view 在使用数据收集服务SDK 允许浏览问卷信息 时传入此参数，参数值目前只支持传入all
     *
     * @author ensong.liu@vhall.com
     * @date 2019-05-10 13:26:25
     */
    public function getAccessTokenAction()
    {
        $thirdPartUserId = $this->accountInfo['account_id'];
        $data = [
            'data_collect_manage'  => 'all',
            'data_collect_submit'  => 'all',
            'data_collect_view'    => 'all',
            'third_party_user_id' => $thirdPartUserId,
        ];

        $accessToken = vss_service()->getPaasService()->baseCreateAccessToken($data);

        $this->success(['access_token' => $accessToken]);
    }

    /*
     * 删除测试账号
     * */
    public function deleteTestAction(){
        $username = $this->getParam('username');
        $userInfo =  vss_model()->getAccountsModel()->getRow(['username'=>$username, 'user_id'=>0, 'user_type'=>AccountConstant::USER_TYPE_CSCES]);
        if($userInfo){
            $userInfo->delRow($userInfo->account_id, true);
        }
        $this->success();
    }
}
