<?php

namespace App\Component\account\src\services;

use App\Component\room\src\constants\RoomConstant;
use App\Constants\MessageConstant;
use App\Constants\ResponseCode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use App\Component\account\src\constants\AccountConstant;
use App\Component\account\src\models\AccountsModel;
use Vss\Common\Services\WebBaseService;

/**
 * AccountService
 * @date     2021-08-19
 */
class AccountService extends WebBaseService
{
    public $accountInfo;

    public function getOrgAndDeptByAccountId($accountId){
        $accountInfo = vss_model()->getAccountsModel()->find($accountId);
        $orglist = vss_service()->getAccountOrgService()->orgDataByCode();
        $data = [];
        $data['org_name'] = $orglist[$accountInfo->org] ?? '';
        $data['dept_name'] = $orglist[$accountInfo->dept] ?? '';
        return $data;
    }

    /**
     * 中建跳转登录
     */
    public function autoLogin($username, $password = '')
    {
        return $this->consoleLoginInfo($username, $password);
    }

    /**
     * 中建账号登录
     */
    public function consoleLoginInfo($username, $password)
    {
        $condition = ['username'=>trim(addslashes($username)), 'user_type'=>AccountConstant::USER_TYPE_CSCES];
        if(\Helper::checkPhone($username)){
            $condition = ['phone'=>trim(addslashes($username)), 'user_type'=>AccountConstant::USER_TYPE_CSCES];
        }
        $accountModel = vss_model()->getAccountsModel()->getRow($condition);
        if(empty($accountModel)){
            $this->fail(ResponseCode::BUSINESS_LOGIN_FAILED);
        }
        if(intval($accountModel->status) !== 0){
            $this->fail(ResponseCode::EMPTY_ACCOUNT);
        }
        if (!$this->verifyPassword($accountModel, $password)) {
            $this->fail(ResponseCode::BUSINESS_LOGIN_FAILED);
        }
        //检查是否有在推流中的房间
        $this->checkStreamStatus($accountModel->account_id);
        //如果之前已经登录，则退出之前登录--根据项目需求自行调整
        if (vss_redis()->exists($accountModel->token)) {
            //发消息通知token失效
            $this->sendMessage($accountModel->token, $accountModel->account_id);
            vss_redis()->del($accountModel->token);
        }
        //token更新到db
        $token       = $this->getToken($username);
        $this->accountInfo = vss_model()->getAccountsModel()->updateTokenByAccountId($accountModel->account_id, $token);

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
            //'phone'         => $this->accountInfo['phone'],
            'nickname'      => $this->accountInfo['nickname'],
            'token'         => $this->accountInfo['token'],
            'account_type'  => $this->accountInfo['account_type'],
            'user_type'     => $this->accountInfo['user_type'],
            'role_id'       => $this->accountInfo['role_id'],
            'status'        => $this->accountInfo['status'],
            'app_id'        => vss_service()->getTokenService()->getAppId(),
            'save_user_url' => vss_service()->getPaasService()
                ->buildPaasRequestUrl(
                    [
                        'third_party_user_id' => $this->accountInfo['account_id'],
                        'nick_name'           => $this->accountInfo['nickname'],
                        'avatar'              => vss_config('application.static.headPortrait.default'),
                    ],
                    '/api/v2/channel/save-user-info'
                ),
        ];
    }

    /*
     * 创建测试账号
     * */
    public function createAccount($params){
        $condition = ['username'=>trim(addslashes($params['username'])), 'user_type'=>AccountConstant::USER_TYPE_CSCES];
        $accountModel = vss_model()->getAccountsModel()->getRow($condition);
        if(empty($accountModel)){
            $account = vss_model()->getAccountsModel()->addRow([
                'phone'        => $params['phone'],
                'nickname'     => $params['username'],
                'username'     => $params['username'],
                'token'        => '',
                'account_type' => '0',
                'user_type'    => '2',
                'user_id'      => '0',
                'third_user_id' => Null,
                'org' => $params['org'] ?: '0001A110000000002ZK4',
                'dept' => $params['dept'] ?: '1001A1100000009THVWW',
                'role_id' => $params['role_id'] ?: '1',
            ]);
            $password = "U_U++--V" . md5($account->c_user_id . '123456');
            vss_model()->getAccountsModel()->updateRow($account->account_id, ['password'=>$password]);
        }else{
            $password = "U_U++--V" . md5($accountModel->c_user_id . '123456');
            vss_model()->getAccountsModel()->updateRow($accountModel->account_id, ['password' => $password, 'phone'=>$params['phone'], 'org' => $params['org'] ?: '0001A110000000002ZK4', 'dept'=>$params['dept'] ?: '', 'role_id'=>$params['role_id'] ?: '1']);
        }
    }

    /*
     * 检查房间某个人是否在推流
     * */
    protected function checkStreamStatus($accountId){
        //获取开播的房间ilIds
        $startIlids = vss_redis()->smembers(RoomConstant::ROOMS_START_IDS_CACHE . date('Y-m-d'));
        if(empty($startIlids)){
            return;
        }
        //获取房间信息
        $joins = [];
        array_walk($startIlids, function ($ilId) use (&$joins){
            $roomInfo = vss_model()->getRoomsModel()->getInfoByIlId($ilId);
            if($roomInfo['status'] != RoomConstant::STATUS_START){
                return;
            }
            $joins[] = ['account_id'=>$roomInfo['account_id'], 'room_id'=>$roomInfo['room_id']];
        });
        vss_logger()->info('csces-login', ['action'=>'checkStreamStatus', 'result' => ['room_list'=>$joins]]); //日志
        if(empty($joins)){
            return;
        }
        $hostJoins = $guestJoins = [];
        array_walk($joins, function ($join) use (&$hostJoins, &$guestJoins, $accountId){
            if($join['account_id'] == $accountId){
                $hostJoins[] = $join;
            }else{
                $guestJoins[] = $join;
            }
        });
        //检查主持人状态
        $this->checkHostStreamStatus($accountId, $hostJoins);
        //检查嘉宾状态
        $this->checkGuestStreamStatus($accountId, $guestJoins);
    }

    /*
     * 检查嘉宾状态
     * */
    protected function checkGuestStreamStatus($accountId, $joins){
        if(empty($joins)){
            return;
        }
        array_walk($joins, function ($join) use ($accountId){
            $speakerList = vss_service()->getInavService()->getSpeakerList($join['room_id']);
            vss_logger()->info('csces-login', ['action'=>'checkStreamStatus', 'result' => ['speaker_list'=>$speakerList]]); //日志
            if($speakerList){
                if(in_array($accountId, array_column($speakerList, 'account_id'))){
                    $isOnline = vss_service()->getCheckOnlineService()->isUserOnlineByRoomIds([$join['room_id']], $accountId);
                    if($isOnline){
                        $this->fail(ResponseCode::AUTH_LOGIN_CHECK_STREAM_STATUS);
                    }
                }
            }
        });
    }

    /*
     * 检查主持人状态
     * */
    protected function checkHostStreamStatus($accountId, $joins){
        if(empty($joins)){
            return;
        }
        $roomIds      = implode(',', array_column($joins, 'room_id'));
        $streamStatus = vss_service()->getPaasService()->getStreamStatus($roomIds);
        vss_logger()->info('csces-login', ['action'=>'checkStreamStatus', 'result' => ['stream_list'=>$streamStatus]]); //日志
        array_walk($streamStatus, function ($join)use ($streamStatus, $accountId){
            if($join['stream_status'] == RoomConstant::STATUS_START){
                $roomIds = array_column($streamStatus, 'room_id');
                $isOnline = vss_service()->getCheckOnlineService()->isUserOnlineByRoomIds($roomIds, $accountId);
                vss_logger()->info('csces-login', ['action'=>'isUserOnlineByRoomIds', 'params'=>['room_ids'=>$roomIds, 'account_id'=>$accountId], 'result' => ['isUserOnlineByRoomIds'=>$isOnline]]); //日志
                if($isOnline){
                    $this->fail(ResponseCode::AUTH_LOGIN_CHECK_STREAM_STATUS);
                }
            }
        });
    }

    /*
     * 通知消息-之前token失效啦
     * */
    protected function sendMessage($token, $accountId){
        $joins = vss_model()->getRoomJoinsModel()->where('account_id', $accountId)->orderBy('updated_at', 'desc')->limit(1)->get(['room_id','account_id'])->toArray();
        array_walk($joins, function ($join)use($token){
            $activity = vss_model()->getRoomsModel()->findByRoomId($join['room_id']);
            vss_service()->getPaasChannelService()->sendMessageByChannel(
                $activity->channel_id,
                ['module' => 'auth', 'type' => MessageConstant::MESSAGE_TYPE_LOGOUT, 'token' => $token],
                null,
                'service_custom'
            );
        });
    }

    /*
     * 验证密码
     * */
    protected function verifyPassword($accountInfo, $password){
        if(empty($password)){
            return true;
        }
        //("U_U++--V" + MD5Utils.getMD5( wUsers.get(0).getCUserId() + StringUtils.stripToEmpty(loginUserVo.getPassword())
        $md5Pwd = "U_U++--V" . md5($accountInfo['c_user_id'] . $password);
        return $accountInfo->password === $md5Pwd;
    }

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
        $thirdUserId = $params['third_user_id'] ?? '';
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

        $this->accountInfo = $this->loginInfo($phone, $nickname, $avatar, $accountType,$thirdUserId);

        if ($this->accountInfo['status'] == AccountConstant::STATUS_DISABLED) {
            $this->fail(ResponseCode::AUTH_LOGIN_ACCOUNT_DISABLE);
        }

        //如果之前已经登录，则退出之前登录--根据项目需求自行调整
        if (!empty($this->accountInfo['last_token']) && vss_redis()->exists($this->accountInfo['last_token'])) {
            //发消息通知token失效
            $this->sendMessage($this->accountInfo['last_token'], $this->accountInfo['account_id']);
            vss_redis()->del($this->accountInfo['last_token']);
        }

        //登录有效范围
        $this->accountInfo['modules']             = AccountConstant::ALLOW_MODULES;
        $this->accountInfo['app_id']              = vss_service()->getTokenService()->getAppId();
        $this->accountInfo['third_party_user_id'] = $this->accountInfo['account_id'];
        vss_redis()->set($this->accountInfo['token'], json_encode($this->accountInfo), AccountConstant::TOKEN_TIME);

        return [
            'account_id'    => $this->accountInfo['account_id'],
            'username'      => $this->accountInfo['username'],
            //'phone'         => $this->accountInfo['phone'],
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
    public function loginInfo($phone, $nickname, $avatar = '', $type = AccountConstant::ACCOUNT_TYPE_WATCH, $thirdUserId = '')
    {
        $token       = self::getToken($phone);
        $username    = $phone ?: $nickname;
        $accountInfo = vss_model()->getAccountsModel()->updateToken($phone, $nickname, $token, $type);

        if (empty($accountInfo)) {
            $accountInfo = vss_model()->getAccountsModel()->addRow([
                'phone'        => $phone,
                'nickname'     => $nickname,
                'username'     => $username,
                'token'        => $token,
                'account_type' => $type,
                'third_user_id' => $thirdUserId ?: Null,
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
    public function getList($condition, $page = 1, $pageSize = 15)
    {
        $model = vss_model()->getAccountsModel();
        if ($condition['order_by']) {
            $model = $model->setKeyName($condition['order_by']);
        }
        return $model->setPerPage($pageSize)->getList($condition, [], $page);
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
     * @return \App\Component\account\src\models\AccountsModel
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

        if($accountInfo->token && $status == AccountConstant::STATUS_DISABLED){
            $cacheAccount = vss_redis()->get($accountInfo->token);
            if($cacheAccount){
                $cacheAccount = json_decode($cacheAccount, true);
                $cacheAccount['status'] = $status;
                vss_redis()->set($accountInfo->token, json_encode($cacheAccount), AccountConstant::TOKEN_TIME);
            }
            //vss_redis()->del($accountInfo->token);
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

    /**
     * 自适应查询部门|组织权限
     * @param $accountInfo
     * @param string $permissionId
     * @param string $permissionField in [dept, org]
     * @return mixed|string
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function adaptDeptPermission($accountInfo, $permissionId="", $permissionField="dept")
    {
        $userDefaultPermission = $accountInfo[$permissionField];
        if($userDefaultPermission){
            //获取组织架构的org_id和code码，有缓存，可以直接查
            $orgIdByCode = (array)vss_service()->getAccountOrgService()->orgIdByCode();
            $userDefaultPermission = $orgIdByCode[$userDefaultPermission] ?: '';
        }

        // 未查询部门或查询部门与自身相同，返回查询结果
        if (!$permissionId or $userDefaultPermission == $permissionId) {
            return $permissionId;
        }

        // 查询部门是否在自身权限体系下
        $deptChildren = vss_service()->getAccountFormatService()->getDeptsOrOrgs($permissionId);
        $deptChildrenField = $permissionField."s";

        if(!isset($deptChildren[$deptChildrenField])) {
            return '';
        }

        foreach((array)$deptChildren[$deptChildrenField] as $org => $orgMsg) {
            // 权限相符
            if (!empty($orgMsg) && $orgMsg == $permissionId) {
                return $permissionId;
            }
            /*if (isset($orgMsg['org_id']) && $orgMsg['org_id'] == $permissionId) {
                return $permissionId;
            }*/
        }

        return '';
    }
}
