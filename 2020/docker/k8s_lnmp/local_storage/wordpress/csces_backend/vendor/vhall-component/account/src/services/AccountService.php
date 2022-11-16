<?php

namespace vhallComponent\account\services;

use App\Constants\ResponseCode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use vhallComponent\account\constants\AccountConstant;
use vhallComponent\account\models\AccountsModel;
use Vss\Common\Services\WebBaseService;

/**
 * AccountServiceTrait
 *
 * @uses     yangjin
 * @date     2020-07-31
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class AccountService extends WebBaseService
{
    public $accountInfo;

    /**
     * @param     $params
     * @param int $checkCode
     *
     * @return array
     *
     * @throws \Vss\Exceptions\JsonResponseException
     * @author  jin.yang@vhall.com
     * @date    2020-05-19
     */
    public function login($params, $checkCode = 1)
    {
        $phone       = $params['phone'] ?? '';
        $code        = $params['code'] ?? '';
        $nickname    = $params['nickname'] ?? '';
        $accountType = $params['type'] ?? '';
        $avatar      = $params['avatar'] ?: vss_config('application.static.headPortrait.default');
        $accountType = $accountType ?: AccountConstant::ACCOUNT_TYPE_WATCH;

        if ($checkCode && vss_service()->getCodeService()->checkCode($phone, $code) == false) {
            $this->fail(ResponseCode::AUTH_VERIFICATION_CODE_ERROR);
        }

        # vhallEOF-watchlimit-accountService-login-1-start
        
        //观看限制登录
        $ilId     = $params["il_id"] ?? "";
        $password = isset($params["password"]) ? $params["password"] :0;
        if($ilId > 0 ) {
             $loginwatch = vss_service()->getWatchlimitService()->getLoginWatch($ilId,$password,$phone);
        }

        # vhallEOF-watchlimit-accountService-login-1-end

        $this->accountInfo = $this->loginInfo($phone, $nickname, $avatar, $accountType);

        if ($this->accountInfo['status'] == AccountConstant::STATUS_DISABLED) {
            $this->fail(ResponseCode::AUTH_LOGIN_ACCOUNT_DISABLE);
        }

        //登录有效范围
        $this->accountInfo['modules']             = AccountConstant::ALLOW_MODULES;
        $this->accountInfo['app_id']              = vss_service()->getTokenService()->getAppId();
        $this->accountInfo['third_party_user_id'] = $this->accountInfo['account_id'];
        vss_redis()->set($this->accountInfo['token'], json_encode($this->accountInfo), AccountConstant::TOKEN_TIME);

        return [
            'account_id'    => $this->accountInfo['account_id'],
            'username'      => $this->accountInfo['username'],
            'phone'         => $this->accountInfo['phone'],
            'nickname'      => $this->accountInfo['nickname'],
            'token'         => $this->accountInfo['token'],
            'account_type'  => $this->accountInfo['account_type'],
            'status'        => $this->accountInfo['status'],
            'app_id'        => vss_service()->getTokenService()->getAppId(),
            'save_user_url' => vss_service()->getPaasService()
                ->buildPaasRequestUrl(
                    [
                        'third_party_user_id' => $this->accountInfo['account_id'],
                        'nick_name'           => $nickname,
                        'avatar'              => $avatar
                    ],
                    '/api/v2/channel/save-user-info'
                ),
        ];
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
        return vss_redis()->del($accountInfo['token']);
    }

    /**
     * 游客
     * @throws \Exception
     */
    public function visitor($ilId)
    {
        //控制台登录默认主播
        $avatar = vss_config('application.static.headPortrait.default');

        if (!empty($_COOKIE['visitor'])) {
            $arr      = unserialize($_COOKIE['visitor']);
            $nickname = $arr['nickname'];
            $phone    = $arr['phone'];
        } else {
            $nickname = 'visitor_' . mt_rand(1000000, 9999999);
            $phone = microtime(true) * 10000 . mt_rand(1000, 9999);
            $phone = (int)strrev($phone);
        }
        $params = [
            'phone'    => $phone,
            'nickname' => $nickname,
            'avatar'   => $avatar,
            'type'     => AccountConstant::ACCOUNT_TYPE_VISITOR
        ];

        $this->accountInfo = $this->login($params, 0);
        if ($this->accountInfo['status'] == AccountConstant::STATUS_DISABLED) {
            $this->fail(ResponseCode::AUTH_LOGIN_ACCOUNT_DISABLE);
        }

        $this->accountInfo['modules'] = AccountConstant::ALLOW_API_MODULES;
        if ($ilId) {
            $this->accountInfo['ilId'] = $ilId;//房间id
        }
        vss_redis()->set($this->accountInfo['token'], json_encode($this->accountInfo), AccountConstant::TOKEN_TIME);
        $user_info = [
            'account_id' => $this->accountInfo['account_id'],
            'username'   => $this->accountInfo['username'],
            'nickname'   => $this->accountInfo['nickname'],
            'token'      => $this->accountInfo['token'],
            'app_id'     => vss_service()->getTokenService()->getAppId(),
        ];
        setcookie('visitor', serialize(['phone' => $phone, 'nickname' => $nickname]));
        return $user_info;
    }

    /**
     * 三方网站用户同步数据
     *
     * @param $params
     *
     * @return array
     *
     */
    public function thirdLogin($params)
    {
        $rule        = [
            'third_user_id' => 'required',
            'username'      => 'required',
            'nickname'      => 'required',
            'profile_photo' => '',
            'phone'         => '',
        ];
        $data        = vss_validator($params, $rule);
        $thirdUserId = $params['third_user_id'];

        //获取用户信息
        $accountInfo = vss_model()->getAccountsModel()->getInfoByThirdUserId($thirdUserId);
        vss_logger()->info('account_third_login', ['data' => $accountInfo]);
        if (!empty($accountInfo)) {
            $accountInfo = $accountInfo->toArray();
            if ($accountInfo['status'] == AccountConstant::STATUS_DISABLED) {
                $this->fail(ResponseCode::AUTH_LOGIN_ACCOUNT_DISABLE);
            }

            //token 缓存未过期直接使用缓存信息 否则更新token
            $exist = vss_redis()->exists($accountInfo['token']);
            if (!$exist) {
                $data['token'] = self::getToken($thirdUserId);
            } else {
                $this->accountInfo = json_decode(vss_redis()->get($accountInfo['token']), true);
            }

            //数据是否有修改
            $intersect = array_intersect_assoc($data, $this->accountInfo);
            //新传入的数据有修改，数据同步到数据库   TODO 可优化为队列异步处理
            if (count($intersect) != count($data)) {
                $accountInfo = vss_model()->getAccountsModel()->updateInfo(
                    ['third_user_id' => $accountInfo['third_user_id']],
                    $data
                );
                vss_logger()->info(
                    'account_third_login1',
                    ['data' => $data, 'intersect' => $intersect, 'account' => $accountInfo]
                );

                $this->accountInfo = $accountInfo->toArray();
                //登录有效范围
                $this->accountInfo['modules']             = AccountConstant::ALLOW_API_MODULES;
                $this->accountInfo['app_id']              = vss_service()->getTokenService()->getAppId();
                $this->accountInfo['third_party_user_id'] = $this->accountInfo['account_id'];
                vss_redis()->set(
                    $this->accountInfo['token'],
                    json_encode($this->accountInfo),
                    AccountConstant::TOKEN_TIME
                );
            }

            return $this->accountInfo;
        }
        $data['token']        = self::getToken($thirdUserId);
        $data['account_type'] = AccountConstant::ACCOUNT_TYPE_WATCH;
        $accountInfo          = vss_model()->getAccountsModel()->addRow($data);
        $this->accountInfo    = $accountInfo->toArray();
        //登录有效范围
        $this->accountInfo['modules']             = AccountConstant::ALLOW_API_MODULES;
        $this->accountInfo['app_id']              = vss_service()->getTokenService()->getAppId();
        $this->accountInfo['third_party_user_id'] = $this->accountInfo['account_id'];
        vss_redis()->set($this->accountInfo['token'], json_encode($this->accountInfo), AccountConstant::TOKEN_TIME);

        return $this->accountInfo;
    }

    /**
     * 获取用户信息
     *
     * @param $phone
     * @param $type
     *
     * @return array
     */
    public function getAccountInfo($phone, $type)
    {
        //获取用户信息
        $condition = [
            'phone'        => $phone,
            'status'       => AccountConstant::STATUS_ENABLED,
            'account_type' => $type,
        ];

        $accountInfo = vss_model()->getAccountsModel()->getRow($condition);

        if (empty($accountInfo)) {
            return [];
        }

        return $accountInfo->toArray();
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
    public function loginInfo($phone, $nickname, $avatar = '', $type = AccountConstant::ACCOUNT_TYPE_WATCH)
    {
        $token       = self::getToken($phone);
        $username    = $phone ?: $nickname;
        $accountInfo = vss_model()->getAccountsModel()->updateToken($phone, $nickname, $token, $type);

        //游客登录信息更新
        if (empty($accountInfo) && !empty($_COOKIE['visitor'])) {
            $arr         = unserialize($_COOKIE['visitor']);
            $condition   = [
                'nickname' => $arr['nickname'],
                'phone'    => $arr['phone']
            ];
            $update      = [
                'nickname'     => $nickname,
                'phone'        => $phone,
                'username'     => $username,
                'token'        => $token,
                'account_type' => $type,
            ];
            $accountInfo = vss_model()->getAccountsModel()->updateInfo($condition, $update);
            if ($accountInfo) {
                setcookie('visitor', '');
            }
        }
        if (empty($accountInfo)) {
            $accountInfo = vss_model()->getAccountsModel()->addRow([
                'phone'        => $phone,
                'nickname'     => $nickname,
                'username'     => $username,
                'token'        => $token,
                'account_type' => $type,
            ]);
        }

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
     * 获取用户
     *
     * @param $condition
     *
     * @return Model|AccountsModel|null
     * @author  jin.yang@vhall.com
     * @date    2020-09-03
     */
    public function getOne($condition)
    {
        return vss_model()->getAccountsModel()->getRow($condition);
    }

    /**
     * 用户列表
     *
     * @param $condition
     *
     * @return LengthAwarePaginator
     * @author  jin.yang@vhall.com
     * @date    2020-09-03
     */
    public function getList($condition, $page = 1)
    {
        return vss_model()->getAccountsModel()->getList($condition, [], $page);
    }

    /**
     * 用户导出
     *
     * @param $keyword
     * @param $beginTime
     * @param $endTime
     * @param $status
     *
     * @author  jin.yang@vhall.com
     * @date    2020-09-03
     */
    public function exportList($keyword, $beginTime, $endTime, $status, $type)
    {
        //Excel文件名
        $fileName           = 'AccountList' . date('YmdHis');
        $header             = ['ID', '账号', '昵称', '手机号', '加入时间', '状态'];
        $exportProxyService = vss_service()->getExportProxyService()->init($fileName)->putRow($header);

        //列表数据
        $page = 1;
        while (true) {
            //当前page下列表数据
            $condition   = [
                'keyword'      => $keyword,
                'begin_time'   => $beginTime,
                'end_time'     => $endTime,
                'status'       => $status,
                'account_type' => $type,
            ];
            $with        = [];
            $accountList = vss_model()->getAccountsModel()->setPerPage(1000)->getList($condition, $with, $page);
            if (!empty($accountList->items())) {
                foreach ($accountList->items() as $accountItem) {
                    $row = [
                        $accountItem['account_id'] ?: '-',
                        $accountItem['username'] ?: ' -',
                        $accountItem['nickname'] ?: ' -',
                        $accountItem['phone'] ?: '-',
                        $accountItem['created_at'] ?: '-',
                        $accountItem['status_str'] ?: '-',
                    ];
                    $exportProxyService->putRow($row);
                }
            }
            //跳出while
            if ($page >= $accountList->lastPage() || $page >= 10) { //10页表示1W上限
                break;
            }
            //下一页
            $page++;
        }

        //下载文件
        $exportProxyService->download();
    }

    /**
     * @param $phone
     * @param $username
     * @param $nickname
     * @param $sex
     *
     * @return \vhallComponent\account\models\AccountsModel
     *
     * @author  jin.yang@vhall.com
     * @date    2020-09-03
     */
    public function add($phone, $username, $nickname, $sex, $type)
    {
        //手机号已存在
        if (vss_model()->getAccountsModel()->getCount(['phone' => $phone])) {
            $this->fail(ResponseCode::AUTH_PHONE_ALREADY_EXIST);
        }

        //保存数据
        $attributes  = [
            'phone'        => $phone,
            'username'     => $username,
            'nickname'     => $nickname,
            'sex'          => $sex,
            'account_type' => $type,
        ];
        $accountInfo = vss_model()->getAccountsModel()->addRow($attributes);
        if (!$accountInfo) {
            $this->fail(ResponseCode::BUSINESS_CREATE_FAILED);
        }

        return $accountInfo;
    }

    /**
     * @param $accountId
     * @param $phone
     * @param $username
     * @param $nickname
     * @param $sex
     *
     * @return bool
     *
     * @author  jin.yang@vhall.com
     * @date    2020-09-03
     */
    public function edit($accountId, $phone, $username, $nickname, $sex)
    {
        //用户信息
        $condition   = [
            'account_id' => $accountId,
        ];
        $accountInfo = vss_model()->getAccountsModel()->getRow($condition);
        if (empty($accountInfo)) {
            $this->fail(ResponseCode::EMPTY_USER);
        }

        //手机号已存在
        if ($phone != $accountInfo['phone']
            && vss_model()->getAccountsModel()->getCount(['phone' => $phone])
        ) {
            $this->fail(ResponseCode::AUTH_PHONE_ALREADY_EXIST);
        }

        //保存数据
        $attributes = [
            'phone'    => $phone,
            'username' => $username,
            'nickname' => $nickname,
            'sex'      => $sex,
        ];
        if (!$accountInfo->updateRow($accountId, $attributes)) {
            $this->fail(ResponseCode::BUSINESS_EDIT_FAILED);
        }

        return true;
    }

    /**
     * 用户修改
     *
     * @param $accountId
     * @param $status
     *
     * @return bool
     *
     * @author  jin.yang@vhall.com
     * @date    2020-09-03
     */
    public function editStatus($accountId, $status)
    {
        //用户信息
        $condition   = [
            'account_id' => $accountId,
        ];
        $accountInfo = vss_model()->getAccountsModel()->getRow($condition);
        if (empty($accountInfo)) {
            $this->fail(ResponseCode::EMPTY_USER);
        }

        //保存数据
        $attributes = ['status' => $status];
        if ($accountInfo->update($attributes) == false) {
            $this->fail(ResponseCode::BUSINESS_EDIT_FAILED);
        }

        return true;
    }

    /**
     * 设置昵称
     *
     * @param $accountId
     * @param $nickname
     */
    public function setNickname($accountId, $nickname)
    {
        $attributes = [
            'nickname' => $nickname,
        ];

        if (!vss_model()->getAccountsModel()->updateRow($accountId, $attributes)) {
            $this->fail(ResponseCode::BUSINESS_EDIT_FAILED);
        }
        //更新微吼云昵称
        vss_service()->getPaasChannelService()->saveUserInfo(
            $accountId,
            $nickname,
            'http://static01-open.e.vhall.com/static/v1/1.2.0/images/yun/logo.png'
        );
    }
}
