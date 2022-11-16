<?php

namespace web\services\Account;

use core\common\JsonException;
use core\utils\ConfigUtils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use vhallComponent\account\constants\AccountConstant;
use web\models\AccountsModel;

/**
 * AccountService
 *
 * @uses     yangjin
 * @date     2020-07-31
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class AccountService extends \vhallComponent\account\services\AccountService
{
    /**
     * 用户列表
     *
     * @param $condition
     *
     * @return LengthAwarePaginator
     * @author  jin.yang@vhall.com
     * @date    2020-09-03
     */
    public function getList($condition, $page = 1, $fields = ['account_id', 'phone', 'username', 'nickname', 'sex', 'status', 'account_type', 'third_user_id', 'created_at'])
    {
        return $this->getAccountsModel()->getList($condition, [], $page, $fields);
    }

    /**
     * @param $params
     * @return array
     * @throws JsonException
     * @throws Exception
     * @date    2021-05-31
     */
    public function login($params, $checkCode = 1)
    {
        $ilId        = $params['il_id'] ?? '';
        $phone       = $params['phone'] ?? '';
        $code        = $params['code'] ?? '';
        $nickname    = $params['nickname'] ?? '';
        $username    = $params['username'] ?? '';
        $password    = $params['password'] ?? '';
        $accountType = $params['type'] ?? '';
        $lastToken   = $params['token'] ?? '';
        $thirdUserId = $params['third_user_id'] ?? '';
        $avatar      = $params['avatar'] ?: ConfigUtils::get('application.static.headPortrait.default');
        $accountType = $accountType ? $accountType : AccountConstant::ACCOUNT_TYPE_WATCH;

        if ($checkCode && $this->getCodeService()->checkCode($phone, $code) == false) {
            $this->throwError(21001);
        }

        //如果之前已经登录，则退出之前登录--根据项目需求自行调整
        $accountInfos = $this->getAccountInfoByType($phone, $username, $accountType);
        if (!empty($accountInfos) && !empty($accountInfos['token'])) {
            if ($this->getRedis()->exists($accountInfos['token'])) {
                $this->getRedis()->del($accountInfos['token']);
            }
        }
        # vhallEOF-watchlimit-accountService-login-1-start

        if($ilId > 0 ) {
            $loginwatch = $this->getWatchlimitService()->getLoginWatch($ilId,$password,$phone);
        }

        if($accountType == AccountConstant::ACCOUNT_TYPE_MASTER){
            $this->accountInfo = $this->consoleLoginInfo($username, $password, $avatar, $accountType);
        }else{
            $this->accountInfo = $this->loginInfo($phone, $nickname, $avatar, $accountType, $lastToken, $thirdUserId);
        }

        if ($this->accountInfo['status'] == AccountConstant::STATUS_DISABLED) {
            $this->throwError(21002);
        }
        //登录有效范围
        $this->accountInfo['modules']             = AccountConstant::ALLOW_MODULES;
        $this->accountInfo['app_id']              = $this->getTokenService()->getAppId();
        $this->accountInfo['third_party_user_id'] = $this->accountInfo['account_id'];


        $this->getRedis()->set($this->accountInfo['token'], json_encode($this->accountInfo), AccountConstant::TOKEN_TIME);

        return [
            'account_id'       => $this->accountInfo['account_id'],
            'username'         => $this->accountInfo['username'],
            'phone'            => $this->accountInfo['phone'],
            'nickname'         => $this->accountInfo['nickname'],
            'token'            => $this->accountInfo['token'],
            'account_type'     => $this->accountInfo['account_type'],
            'status'           => $this->accountInfo['status'],
            'app_id'           => $this->getTokenService()->getAppId(),
            'save_user_url'    => $this->getPaasService()
                ->buildPaasRequestUrl(
                    [
                        'third_party_user_id' => $this->accountInfo['account_id'],
                        'nick_name'           => $nickname ?: $username,
                        'avatar'              => $avatar
                    ],
                    '/api/v2/channel/save-user-info'
                ),
        ];
    }

    /**
     * 登录
     *
     * @param string $phone    用户手机号码
     * @param string $nickname 用户昵称
     * @param string $avatar   默认头像
     *
     * @return array|bool
     */
    public function loginInfo($phone, $nickname, $avatar = '', $type = AccountConstant::ACCOUNT_TYPE_WATCH, $lastToken = '', $thirdUserId = '')
    {
        if(empty($phone)){
            $this->throwError(201);
        }
        $token       = self::getToken($phone);
        $lastVisitor = 0;
        if($lastToken){
            $lastAccountInfo = json_decode($this->getRedis()->get($lastToken), true);
            if(!empty($lastAccountInfo)){
                if($lastAccountInfo['account_type'] == AccountConstant::ACCOUNT_TYPE_VISITOR){
                    $lastVisitor = $lastAccountInfo['account_id'];
                }
            }
        }

        $accountInfo = $this->getAccountsModel()->updateToken($phone, $nickname, $token, $type, $lastVisitor, $thirdUserId);

        if (empty($accountInfo)) {
            $insertUserData = [
                'phone'        => $phone,
                'nickname'     => $nickname,
                'token'        => $token,
                'account_type' => $type,
                'last_visitor' => $lastVisitor,
                'third_user_id'=> $thirdUserId ?: Null,
            ];
            
            if ($thirdUserId) {
                $insertUserData['username'] = $thirdUserId;
            }

            $accountInfo = $this->getAccountsModel()->addRow($insertUserData);
        }
        if(intval($lastVisitor) > 0){
            $this->getAccountsModel()->updateRow($lastVisitor, ['last_watch' => $accountInfo->account_id]);
        }
        return $accountInfo->toArray();
    }

    /**
     * 获取用户信息
     * @param $username
     * @param $phone
     *
     * @return bool|string
     */
    public function getAccountInfoByType($phone, $username, $type)
    {
        //获取用户信息
        $condition = [
            'phone'        => $phone,
            'status'       => AccountConstant::STATUS_ENABLED,
            'account_type' => $type,
        ];

        if($type == AccountConstant::TYPE_MASTER){
            unset($condition['phone']);
            $condition['username'] = $username;
        }

        $accountInfo = AccountsModel::getInstance()->getRow($condition);

        if (empty($accountInfo)) {
            return [];
        }

        return $accountInfo->toArray();
    }

    public function consoleLoginInfo($username, $password, $avatar = '', $type = AccountConstant::ACCOUNT_TYPE_WATCH)
    {
        $accountModel = AccountsModel::getInstance()->getRow(['username'=>trim($username), 'account_type'=>$type]);
        if(empty($accountModel)){
            $this->throwError(21006);
        }
        if (!password_verify($password, $accountModel->password)) {
            $this->throwError(21008);
        }

        $token       = $this->getToken($username);
        $nickname    = $username;
        $accountInfo = AccountsModel::getInstance()->updateTokenByUsername($username, $nickname, $token, $type);
        return $accountInfo->toArray();
    }

    /**
     * 获取Token字符串
     *
     * @param $phone
     *
     * @return bool|string
     */
    public function getToken($phone)
    {
        return substr(md5(rand(1000, 9999) . time() . $phone . rand(1000, 9999)), 8, 16);
    }

    /**
     * 修改密码
     *
     * @throws Exception
     * @author yaoming@vhall.com
     * @date   2021-05-28 16:38:52
     */
    public function changePassword($accountInfo, $params)
    {
        $password = isset($params['password']) ? $params['password'] : '';
        $newPassword = isset($params['new_password']) ? $params['new_password'] : '';
        $confirmPassword = isset($params['confirm_password']) ? $params['confirm_password'] : '';
        if(intval($accountInfo['account_type']) !== AccountConstant::ACCOUNT_TYPE_MASTER){
            $this->throwError(110002);
        }
        if($newPassword !== $confirmPassword){
            $this->throwError(110003);
        }
        /*if($password === $newPassword){
            $this->throwError(110004);
        }*/
        $accountInfo = $this->getAccountInfoByType($accountInfo['phone'], $accountInfo['username'], AccountConstant::ACCOUNT_TYPE_MASTER);
        if (password_verify($newPassword, $accountInfo['password'])) {
            $this->throwError(110004);
        }
        if (!password_verify($password, $accountInfo['password'])) {
            $this->throwError(110001);
        }
        $this->getAccountsModel()->updateInfo(['account_id' => $accountInfo['account_id']], ['password' => password_hash($newPassword, PASSWORD_DEFAULT)]);
        return true;
    }

    public function create($username, $nickname, $password, $type = AccountConstant::ACCOUNT_TYPE_MASTER){
        //账号已存在
        if ($this->getAccountsModel()->getCount(['username' => $username, 'account_type'=>$type])) {
            $this->throwError(21011);
        }
        //保存数据
        $attributes  = [
            'username'     => $username,
            'nickname'     => $nickname,
            'password'     => password_hash($password, PASSWORD_DEFAULT),
            'account_type' => $type,
            'sex'          => 1,
        ];
        $accountInfo = $this->getAccountsModel()->addRow($attributes);
        if (!$accountInfo) {
            $this->throwError(21004);
        }
        return $accountInfo;
    }

    public function update($accountId, $username, $nickname, $password, $type = AccountConstant::ACCOUNT_TYPE_MASTER){
        //账号已存在
        $accounts = $this->getAccountsModel()->getList(['username'=>$username, 'account_type'=>$type])->toArray();
        if (count($accounts['data']) > 1) {
            $this->throwError(21011);
        }

        if (count($accounts['data']) == 1 && $accounts['data'][0]['account_id'] != $accountId) {
            $this->throwError(21011);
        }
        //保存数据
        $attributes  = [
            'username'     => $username,
            'nickname'     => $nickname,
            //'password'     => password_hash($password, PASSWORD_DEFAULT),
            'account_type' => $type,
            'sex'          => 1,
        ];
        if(!empty($password)){
            $attributes['password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        $accountInfo = $this->getAccountsModel()->updateInfo(['account_id'=>$accountId], $attributes);
        if (!$accountInfo) {
            $this->throwError(21004);
        }
        return $accountInfo;
    }

    /**
     * 退出登录
     *
     * @param $accountInfoo
     *
     * @return int
     * @throws \Exception
     * @author  jin.yang@vhall.com
     * @date    2020-07-31
     */
    public function logout($accountInfo)
    {
        $this->getRedis()->del($accountInfo['token']);
        if($accountInfo['account_type'] == AccountConstant::ACCOUNT_TYPE_WATCH){
            $visitorId = $accountInfo['last_visitor'];
            $visitorInfo = $this->getAccountsModel()->getInfoByAccountId($visitorId);
            if($this->getRedis()->get($visitorInfo['token'])){
                return $visitorInfo['token'];
            }
        }
        return '';
    }

    /**
     * 游客
     * @throws \Exception
     */
    public function visitor($ilId, $visitorToken = '')
    {
        //控制台登录默认主播
        $avatar = ConfigUtils::get('application.static.headPortrait.default');
        $visitorInfo = [];
        if($visitorToken){
            $visitorInfo = json_decode($this->getRedis()->get($visitorToken), true);
        }
        if ($visitorInfo) {
            $nickname = $visitorInfo['nickname'];
            $phone    = $visitorInfo['phone'];
        } else {
            $nickname = 'visitor_' . mt_rand(1000000, 9999999);
            //$ip=CommonService::getIp();
            $phone = microtime(true) * 10000 . mt_rand(1000, 9999);
            $phone = (int)strrev($phone);
        }
        $params            = [
            'phone'    => $phone,
            'nickname' => $nickname,
            'avatar'   => $avatar,
            'type'     => AccountConstant::ACCOUNT_TYPE_VISITOR
        ];

        $this->accountInfo = $this->login($params,0);
        if ($this->accountInfo['status'] == AccountConstant::STATUS_DISABLED) {
            $this->throwError(21002);
        }

        $this->accountInfo['modules'] = AccountConstant::ALLOW_API_MODULES;
        if ($ilId) {
            $this->accountInfo['ilId'] = $ilId;//房间id
        }
        $this->getRedis()->set($this->accountInfo['token'], json_encode($this->accountInfo), AccountConstant::TOKEN_TIME);
        $userInfo = [
            'account_id'   => $this->accountInfo['account_id'],
            'username'     => $this->accountInfo['username'],
            'nickname'     => $this->accountInfo['nickname'],
            'token'        => $this->accountInfo['token'],
            'account_type' => $this->accountInfo['account_type'],
            'app_id'       => $this->getTokenService()->getAppId(),
        ];
        return $userInfo;
    }
}