<?php

namespace vhallComponent\lottery\services;

use App\Constants\ResponseCode;
use vhallComponent\common\services\UploadFile;
use vhallComponent\lottery\constants\LotteryConstant;
use Vss\Common\Services\WebBaseService;

class LotteryService extends WebBaseService
{
    /**
     * 创建抽奖
     *
     * @param $params
     *
     * @return mixed
     * @throws $error
     */
    public static function add($params)
    {
        $validator     = vss_validator($params, [
            'source_id'        => 'required',
            'lottery_number'   => 'required',
            'lottery_rule'     => 'required',
            'lottery_type'     => 'required',
            'lottery_rule_text'=> '',
            'lottery_user_ids' => '',
            'extension'        => 'string|max:255',
        ]);
        $lotteryNumber = $params['lottery_number'];
        $lotteryRule   = $params['lottery_rule'];
        $extension     = $params['extension'];

        $key = LotteryConstant::LOTTERY_ADD_LOCK . $params['source_id'];
        if (vss_redis()->lock($key, 10)) {
            self::getInstance()->fail(ResponseCode::BUSINESS_REPEAT_SUBMIT);
        }
        $lotteryUsers = self::_getLotteryUsers(
            $params['lottery_user_ids'],
            $params['source_id'],
            $lotteryNumber,
            $lotteryRule
        );
        if (empty($lotteryUsers) || !is_array($lotteryUsers)) {
            self::getInstance()->fail(ResponseCode::COMP_LOTTERY_NOT_MATCH_USER);
        }
        vss_logger()->info('lot-test', ['data' => $lotteryUsers]);
        $params['lottery_users'] = json_encode($lotteryUsers);

        $params['title'] = $params['lottery_rule_text'];

        $data = vss_service()->getPublicForwardService()->lotteryAdd($params);

        if (!empty($data) && is_array($data)) {
            //记录中奖用户
            self::_recordLotteryWinner($data, $lotteryUsers, $lotteryRule);

            vss_service()->getPaasChannelService()->sendMessage($params['source_id'], [
                'type'                     => 'lottery_push',
                'room_id'                  => $data['source_id'],
                'lottery_id'               => $data['id'],
                'lottery_creator_id'       => $data['creator_id'],
                'lottery_creator_avatar'   => $data['creator_avatar'],
                'lottery_creator_nickname' => $data['creator_nickname'],
                'lottery_type'             => $data['lottery_type'],
                'lottery_number'           => $data['lottery_number'],
                'lottery_status'           => $data['lottery_status'],
                'lottery_rule_text'        => $params['lottery_rule_text'],
            ]);

            //extension 记录
            $extensionKey = LotteryConstant::LOTTERY_EXTENSION . $params['source_id'];
            vss_redis()->set($extensionKey, $extension);
            return $data;
        }
        return [];
    }

    /**
     * 记录抽奖信息及中奖用户
     *
     * @param $lotteryData
     * @param $lotteryUsers
     * @param $lotteryRule
     *
     * @throws \Exception
     */
    protected static function _recordLotteryWinner($lotteryData, $lotteryUsers, $lotteryRule)
    {
        $sourceId = $lotteryData['source_id'];
        //中奖用户ID 导入用户为lottery_user表 id
        $winnerIds = array_column($lotteryUsers, 'lottery_user_id');
        if ($lotteryRule == LotteryConstant::LOTTERY_RULE_CUSTOM) {
            //数据表添加中奖标识
            $res = vss_model()->getLotteryUserModel()->updateByIds($winnerIds, ['is_winner' => 1]);
            if (!$res) {
                vss_logger()->error('_recordLotteryWinnerUpdate', [
                    'source_id'     => $sourceId,
                    'lottery_users' => $lotteryUsers,
                    'lottery_rule'  => $lotteryRule
                ]);
            }

            //记录抽奖用户池的key
            $key = LotteryConstant::LOTTERY_RANGE_IMPORT . $sourceId;
        } else {
            //数据表添加中奖标识
            $res = vss_model()->getRoomJoinsModel()->updateByRoomIdAccountIds($sourceId, $winnerIds,
                ['is_lottery_winner' => 1]);
            if (!$res) {
                vss_logger()->error('_recordLotteryWinnerUpdate', [
                    'source_id'     => $sourceId,
                    'lottery_users' => $lotteryUsers,
                    'lottery_rule'  => $lotteryRule
                ]);
            }

            //记录抽奖用户池的key
            $key = LotteryConstant::LOTTERY_RANGE_ACCOUNTS . $sourceId;
        }
        //获取所有抽奖池用户ID
        $userIds = vss_redis()->smembers($key);
        //预中奖用户放回抽奖池
        $userIds = array_merge($userIds, $winnerIds);

        //抽奖信息写入文件记录
        $lotteryId                 = $lotteryData['lottery_id'];
        $content                   = [];
        $content['lotery_data']    = $lotteryData;
        $content['winner_user']    = $lotteryUsers;
        $content['lotery_rule']    = $lotteryRule;
        $content['lotery_user_id'] = $userIds;
        $fileName                  = 'lottery_user.log';
        $module                    = LotteryConstant::USER_NOTE_DIR . $lotteryId;
        vss_service()->getUploadService()->noteLocal($module, $fileName, $content);
        //清除用户抽奖池
        vss_redis()->del($key);
    }

    /**
     * 搜索符合范围条件的抽奖用户名单
     *
     * @param $params
     *
     * @return mixed
     * @throws $error
     */
    public static function search($params)
    {
        vss_validator($params, [
            'room_id'      => 'required',
            'lottery_type' => 'required',
            'lottery_rule' => 'required',
            'keyword'      => 'required'
        ]);

        if ($params['lottery_rule'] == LotteryConstant::LOTTERY_RULE_CUSTOM) {
            //自定义列表
            $data = self::getLotteryUserByKeyword($params);
        } else {
            $data = self::getLotteryJoinsByKeyword($params);
        }

        return $data;
    }

    /**
     * 获取可以参与抽奖的人数
     *
     * @param $params
     *
     * @return mixed
     * @throws $error
     */
    public static function getCount($params)
    {
        vss_validator($params, [
            'room_id'           => 'required',
            'lottery_type'      => 'required',
            'lottery_rule'      => 'required',
            'winner_out'        => '',
            'lottery_rule_text' => 'required',
        ]);

        if ($params['lottery_rule'] == LotteryConstant::LOTTERY_RULE_CUSTOM) {
            //自定义列表
            $count = self::getLotteryUserData($params);
        } else {
            //指定列表
            $count = self::getOnlineData($params);
        }
        //记录抽奖标题
        $key = LotteryConstant::LOTTERY_RULE_TEXT . $params['room_id'];
        vss_redis()->set($key, $params['lottery_rule_text'], 86400);

        return $count;
    }

    /**
     * 获取抽奖用户信息
     *
     * @param $lotteryUserIds
     * @param $roomId
     * @param $lotteryNumber
     * @param $lotteryRule
     *
     * @return array
     * @throws \Exception
     */
    protected static function _getLotteryUsers($lotteryUserIds, $roomId, $lotteryNumber, $lotteryRule)
    {
        //无预置中奖用户 或 预置中奖用户参数错误默认全部随机
        if (empty($lotteryUserIds)) {
            return self::_getLotteryUsersByRandom($roomId, $lotteryNumber, $lotteryRule);
        }

        //有预置中奖用户
        $lotteryUserIds = explode(',', $lotteryUserIds);
        if (count($lotteryUserIds) > $lotteryNumber) {
            self::getInstance()->fail(ResponseCode::COMP_LOTTERY_LUCK_USER_OVERFLOW);
        }

        $result         = [];
        $lotteryUserIds = array_unique($lotteryUserIds);
        //从集合移除预置中奖用户
        if ($lotteryRule == LotteryConstant::LOTTERY_RULE_CUSTOM) {
            $key = LotteryConstant::LOTTERY_RANGE_IMPORT . $roomId;
        } else {
            $key = LotteryConstant::LOTTERY_RANGE_ACCOUNTS . $roomId;
        }
        //移除预置中奖人
        foreach ($lotteryUserIds as $userId) {
            $res = vss_redis()->srem($key, $userId);
            if ($res) {
                $lotteryNumber--;
            }
        }

        //剩余名额走无预置中奖逻辑
        if ($lotteryNumber > 0) {
            $userList = self::_getLotteryUsersByRandom($roomId, $lotteryNumber, $lotteryRule);
            $result   = array_merge($result, $userList);
        }
        //获取预置中奖用户信息
        $userList = self::_getLotteryUserInfo($roomId, $lotteryUserIds, $lotteryRule, 1);

        $result = array_merge($result, $userList);

        return $result;
    }

    /**
     * 获取中奖用户列表
     *
     * @param $roomId
     * @param $lotteryNumber
     * @param $lotteryRule
     *
     * @return array
     * @throws \Exception
     */
    protected static function _getLotteryUsersByRandom($roomId, $lotteryNumber, $lotteryRule)
    {
        if ($lotteryRule == LotteryConstant::LOTTERY_RULE_CUSTOM) {
            //自定义(导入数据)抽奖列表
            $key   = LotteryConstant::LOTTERY_RANGE_IMPORT . $roomId;
            $idArr = vss_redis()->srandmember($key, $lotteryNumber);

            $userList = self::_getLotteryUserInfo($roomId, $idArr, $lotteryRule);
        } else {
            //直播间随机抽奖列表
            $key = LotteryConstant::LOTTERY_RANGE_ACCOUNTS . $roomId;
            //随机获取用户id (中奖用户)
            $accountIdArr = vss_redis()->srandmember($key, $lotteryNumber);
            //获取中奖用户信息
            $userList = self::_getLotteryUserInfo($roomId, $accountIdArr, $lotteryRule);
        }
        return $userList;
    }

    /**
     * 获取中奖用户信息
     *
     * @param $roomId
     * @param $accountIdArr
     * @param $lotteryRule
     *
     * @return array
     */
    protected static function _getLotteryUserInfo($roomId, $accountIdArr, $lotteryRule, $preset = 0)
    {
        $userList = [];
        if (empty($accountIdArr)) {
            return $userList;
        }

        if ($lotteryRule == LotteryConstant::LOTTERY_RULE_CUSTOM) {
            $list = vss_model()->getLotteryUserModel()->getListByRoomIdAndIds($roomId, $accountIdArr, [
                'id',
                'nickname'
            ]);
            foreach ($list as $user) {
                $userList[$user['id']]['lottery_user_id']       = $user['id'];
                $userList[$user['id']]['lottery_user_nickname'] = $user['nickname'];
                $userList[$user['id']]['preset']                = $preset;
            }
            $userList = array_values($userList);
        } else {
            $accountIdArr = array_chunk($accountIdArr, 1000);
            $accountList  = [];
            //获取中奖用户信息
            foreach ($accountIdArr as $accountIds) {
                $result      = vss_model()->getRoomJoinsModel()->listByRoomIdAccountIds($roomId, $accountIds, [
                    'account_id',
                    'nickname',
                    'avatar'
                ]);
                $accountList = array_merge($accountList, $result);
            }
            //统一中奖用户参数
            foreach ($accountList as $info) {
                $_result = self::_formatLotteryUser($info, $preset);
                if (!empty($_result) && is_array($_result)) {
                    $userList[] = $_result;
                }
            }
        }
        return $userList;
    }

    protected static function _getRandomNumbers($range, $rand)
    {
        if ($range < 1) {
            return [];
        }
        $pool = range(1, $range);
        if ($rand > $range) {
            return array_keys($pool);
        }
        if ($rand > 0) {
            $result = array_rand($pool, $rand);
            if (is_array($result)) {
                return $result;
            }
            return [$result];
        }
        return [];
    }

    protected static function _formatLotteryUser($userInfo, $preset = 0)
    {
        if (!empty($userInfo['account_id'])) {
            $result = ['lottery_user_id' => $userInfo['account_id'], 'preset' => $preset];
            if (!empty($userInfo['nickname'])) {
                $result['lottery_user_nickname'] = $userInfo['nickname'];
            }
            if (!empty($userInfo['avatar'])) {
                $result['lottery_user_avatar'] = $userInfo['avatar'];
            }
            return $result;
        }
        return [];
    }

    /**
     * 结束抽奖
     *
     * @param $params
     *
     * @return mixed
     */
    public static function end($params)
    {
        $data = vss_service()->getPublicForwardService()->lotteryEnd($params);
        if (!empty($data) && is_array($data)) {
            $roomId       = $data['source_id'];
            $key          = LotteryConstant::LOTTERY_RULE_TEXT . $roomId;
            $ruleText     = vss_redis()->get($key);
            $extensionKey = LotteryConstant::LOTTERY_EXTENSION . $roomId;
            $extension    = vss_redis()->get($extensionKey);
            //中奖信息写入json 给前端调用
            $lotteryId = $data['id'];
            $content   = [
                'lottery_rule_text'        => $ruleText,
                'room_id'                  => $roomId,
                'lottery_id'               => $data['id'],
                'lottery_creator_id'       => $data['creator_id'],
                'lottery_creator_avatar'   => $data['creator_avatar'],
                'lottery_creator_nickname' => $data['creator_nickname'],
                'lottery_type'             => $data['lottery_type'],
                'lottery_number'           => $data['lottery_number'],
                'lottery_status'           => $data['lottery_status'],
                'extension'                => $extension, //$data['extension'],
                'lottery_winners'          => $data['lottery_users'],
            ];
            $fileName  = $lotteryId . '.json';
            $module    = LotteryConstant::WINNERS_NOTE_DIR . $roomId;
            $jsonPath  = vss_service()->getUploadService()->noteLocal($module, $fileName, $content);
            if (empty($jsonPath)) {
                self::getInstance()->fail(ResponseCode::COMP_LOTTERY_EMPTY);
            }
            vss_redis()->set(LotteryConstant::LOTTERY_WINNERS_JSON_URL . $lotteryId, $jsonPath, 86400);

            vss_service()->getPaasChannelService()->sendMessage($roomId, [
                'type'       => 'lottery_end',
                'room_id'    => $roomId,
                'lottery_id' => $lotteryId
            ]);
            //解除锁定
            $key = LotteryConstant::LOTTERY_ADD_LOCK . $params['source_id'];
            vss_redis()->del($key);

            return $data;
        }
        return [];
    }

    /**
     * 公布
     *
     * @param $params
     *
     * @return string
     */
    public static function publish($params)
    {
        vss_validator($params, [
            'lottery_id' => 'required',
            'room_id'    => 'required',
        ]);
        $lotteryId = $params['lottery_id'];
        $roomId    = $params['room_id'];
        $jsonPath  = vss_redis()->get(LotteryConstant::LOTTERY_WINNERS_JSON_URL . $lotteryId);
        if (empty($jsonPath)) {
            self::getInstance()->fail(ResponseCode::COMP_LOTTERY_EMPTY);
        }

        vss_service()->getPaasChannelService()->sendMessage($roomId, [
            'type'                => 'lottery_result_notice',
            'room_id'             => $roomId,
            'lottery_id'          => $lotteryId,
            'lottery_winners_url' => $jsonPath
        ]);
        return $jsonPath;
    }

    /**
     * 领奖信息更新
     *
     * @param $params
     *
     * @return mixed
     */
    public static function award($params)
    {
        vss_validator($params, [
            'lottery_id'          => 'required',
            'lottery_user_id'     => 'required',
            'lottery_user_name'   => 'required|string|max:255',
            'lottery_user_phone'  => 'required|string|max:255',
            'lottery_user_remark' => 'required|string|max:255',
        ]);
        return vss_service()->getPublicForwardService()->lotteryAward($params);
    }

    /**
     * 获取抽奖列表
     *
     * @param $params
     *
     * @return mixed
     */
    public static function gets($params)
    {
        //抽奖列表
        list($offset, $limit) = self::_pageToOffset($params['page'], $params['page_size']);
        $params['offset'] = $params['offset'] ?? $offset;
        $params['limit']  = $params['limit'] ?? $limit;
        $params['sort_type'] = $params['sort_type'] ?: 'desc';
        $params['begin_time'] && $params['start_time'] = $params['begin_time'];

        unset($params['page'], $params['page_size']);
        return vss_service()->getPublicForwardService()->lotteryGets($params);
    }

    /**
     * 获取抽奖中奖人名单
     *
     * @param $params
     *
     * @return mixed
     */
    public static function usersGet($params)
    {
        vss_validator($params, [
            'source_id'  => 'required',
            'lottery_id' => 'required',
        ]);
        list($offset, $limit) = self::_pageToOffset($params['page'], $params['page_size']);
        $params['offset'] = $params['offset'] ?? $offset;
        $params['limit']  = $params['limit'] ?? $limit;
        $params['sort_type'] = $params['sort_type'] ?: 'desc';

        $data = vss_service()->getPublicForwardService()->lotteryUsersGet($params);
        $accountIdArr = array_column($data['list'],'lottery_user_id');
        $accountList = vss_model()->getAccountsModel()->getAccountsByAccountIds($accountIdArr,['account_id','username','phone'],'account_id');

        array_walk($data['list'],function(&$value) use($accountList) {
            $value['username'] = $accountList[$value['lottery_user_id']]['username']??'';
        });
        return $data;
    }

    /**
     * 抽奖列表详情 (包含中奖人)
     *
     * @param $params
     *
     * @return mixed
     */
    public static function detailList($params)
    {
        vss_validator($params, [
            'source_id' => 'required',
        ]);

        //抽奖列表
        list($offset, $limit) = self::_pageToOffset($params['page'], $params['page_size']);
        $params['offset'] = $params['offset'] ?? $offset;
        $params['limit']  = $params['limit'] ?? $limit;
        unset($params['page'], $params['page_size']);
        $lotteryList = self::gets($params);

        //查询抽奖用户参数
        $userParams = [
            'source_id' => $params['source_id'],
            'offset'    => 0,
        ];
        foreach ($lotteryList['list'] as $key => &$info) {
            if ($info['lottery_status'] != LotteryConstant::LOTTERY_STATUS_END) {
                continue;
            }
            //中奖人信息
            $uParams               = $userParams;
            $uParams['lottery_id'] = $info['id'];
            $uParams['limit']      = $info['lottery_number'];
            $lotteryUser           = self::usersGet($uParams);
            $info['user']          = $lotteryUser['list'] ?? [];
        }

        return $lotteryList;
    }

    /**
     * 获取在线抽奖用户id
     *
     * @param $params
     *
     * @return mixed
     */
    public static function getOnlineData($params)
    {
        vss_validator($params, [
            'room_id'      => 'required',
            'lottery_type' => 'required',
            'lottery_rule' => 'required',
            'winner_out'   => '',
        ]);
        $roomInfo = vss_model()->getRoomsModel()->findByRoomId($params['room_id']);
        //获取所有在线用户id
        $page           = 1;
        $pagesize       = 1000;
        $keyLotteryUser = LotteryConstant::LOTTERY_RANGE_ACCOUNTS . $params['room_id'];
        vss_redis()->del($keyLotteryUser);
        while (true) {
            $userIdList = vss_service()->getPaasService()->getUserIdList($roomInfo->channel_id, $page, $pagesize);
            if (!$userIdList) {
                break;
            }

            $lotteryAccountIdArr = self::getLotteryAccountIds(
                $params['room_id'],
                $params['lottery_type'],
                $userIdList['list'],
                $params['winner_out']
            );

            if ($lotteryAccountIdArr) {
                vss_redis()->pipeline(function ($pipe) use ($keyLotteryUser, $lotteryAccountIdArr) {
                    foreach ($lotteryAccountIdArr as $accountId) {
                        $pipe->sadd($keyLotteryUser, $accountId);
                    }
                });
            }

            if ($page >= $userIdList['page_all']) {
                break;
            }

            $page += 1;
        }

        $count = vss_redis()->scard($keyLotteryUser);

        return $count;
    }

    /**
     * 获取符合抽奖规则的用户id集合
     *
     * @param $roomId
     * @param $lotteryType //抽奖类型
     * @param $accountIds  //在线用户ID集合
     * @param $winnerOut   //是否排除已中奖人员
     *
     */
    public static function getLotteryAccountIds($roomId, $lotteryType, $accountIds, $winnerOut)
    {
        if (empty($accountIds)) {
            return [];
        }
        //room_joins 直播间用户范围筛选
        $query = vss_model()->getRoomJoinsModel()->newQuery();
        $query->where('room_joins.room_id', $roomId)
            ->whereIn('room_joins.account_id', $accountIds);

        if ($winnerOut) {
            $query->where('room_joins.is_lottery_winner', 0);
        }
        //查询类型 与抽奖共享服务lottery_type字段一致 对应查询范围
        switch ($lotteryType) {
            case 1:
                //全体参会者用户
                $query->where('room_joins.role_name', '>', 1);
                break;
            case 2:
                //参与问卷的参会者
                $query->where('room_joins.role_name', '>', 1)
                    ->where('room_joins.is_answered_questionnaire', 1);
                break;
            case 3:
                //参与签到的参会者
                $query->where('room_joins.role_name', '>', 1)
                    ->where('room_joins.is_signed', 1);
                break;
            case 4:
                //全体观众用户
                $query->where('room_joins.role_name', 2);
                break;
            case 5:
                //已登录的观众
                $query->leftjoin('accounts', 'room_joins.account_id', 'accounts.account_id');
                $query->where('accounts.account_type', 2);
                break;
            case 6:
                //参与问卷的观众
                $query->where('room_joins.role_name', 2)
                    ->where('room_joins.is_answered_questionnaire', 1);
                break;
            case 7:
                //参与签到的观众
                $query->where('room_joins.role_name', 2)
                    ->where('room_joins.is_signed', 1);
                break;
            case 8:
                //参与签到的观众
                $query->where('room_joins.role_name', 2)
                    ->where('room_joins.is_answered_exam', 1);
                break;
            default:
                self::getInstance()->fail(ResponseCode::COMP_LOTTERY_TYPE_INVALID);
                break;
        }
        $lotteryAccountIds = $query->get(['room_joins.account_id'])->toArray();

        //抽奖用户ID 存入缓存集合
        $lotteryAccountIdArr = array_column($lotteryAccountIds, 'account_id');

        return $lotteryAccountIdArr;
    }

    /**
     * 导入抽奖用户
     *
     */
    public function importUser($params)
    {
        vss_validator($params, [
            'room_id' => 'required',
            'app_id'  => 'required',
        ]);
        $roomInfo = vss_model()->getRoomsModel()->findByRoomId($params['room_id']);

        //1、接收上传文件
        $file = new UploadFile('lottery_users');
        $name = $file->file['name'];
        try {
            //2、上传文件
            $extension = strtolower($file->getClientOriginalExtension());

            $filename = $file->sourceFile;
            // 创建读取对象并加载Excel文件
            $objReader = vss_service()->getUploadService()->getExcelReader($extension);

            $objExcel = $objReader->load($filename);
            // 默认使用第一个工作簿，并获取行数
            $sheet = $objExcel->getSheet(0);
            $rows  = $sheet->getHighestRow();
            //$rows 获取超出真实行数问题限制
            if ($rows > 4000) {
                $this->fail(ResponseCode::BUSINESS_UPLOAD_OVERFLOW);
            }
            if ($rows < 2) {
                $this->fail(ResponseCode::COMP_LOTTERY_NOT_ADD_USER);
            }
            $insertData = [];   //真正出入数据容器

            // 遍历，并读取单元格内容
            $appId   = vss_service()->getTokenService()->getAppId();
            $errData = [];
            for ($i = 2; $i <= $rows; $i++) {
                $username = $sheet->getCell('A' . $i)->getValue();
                $nickname = $sheet->getCell('B' . $i)->getValue();

                //数据验证
                if (!preg_match('/^1\d{10}$/', $username) &&
                    !filter_var($username, FILTER_VALIDATE_EMAIL)) {
                    $errData[$username] = '账号格式错误';
                    continue;
                }

                if (!$nickname || strlen($nickname) > 100) {
                    $errData[$username] = '昵称长度超过长度限制';
                    continue;
                }

                $insertData[$username] = [
                    'il_id'    => $roomInfo['il_id'],
                    'room_id'  => $roomInfo['room_id'],
                    'title'    => $name,
                    'username' => $username,
                    'nickname' => $nickname,
                    'app_id'   => $appId,
                ];
            }
            // 关闭
            $objExcel->disconnectWorksheets();
            unset($objExcel);   //释放资源
            $insertData = array_values($insertData);
            $count      = count($insertData);

            if ($count > 3000) {
                $this->fail(ResponseCode::BUSINESS_UPLOAD_OVERFLOW);
            }

            //删除旧数据写入新数据
            vss_model()->getLotteryUserModel()->getConnection()->beginTransaction();
            vss_model()->getLotteryUserModel()->delByIlId($roomInfo['il_id']);
            if ($count > 0) {
                vss_model()->getLotteryUserModel()->insert($insertData);
            }
            vss_model()->getLotteryUserModel()->getConnection()->commit();
        } catch (\Exception $e) {
            vss_model()->getLotteryUserModel()->getConnection()->rollBack();
            throw $e;
        }
        $key = LotteryConstant::LOTTERY_IMPORT_FILENAME . $roomInfo['room_id'];
        vss_redis()->set($key, $name, 86400);

        $data              = [];
        $data['file_name'] = $name;
        $data['count']     = $count;
        $data['errcount']  = count($errData);
        return $data;
    }

    /**
     * 导入文件名
     *
     * @param $roomId
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function importTitle($roomId)
    {
        if (empty($roomId)) {
            return '';
        }
        $key      = LotteryConstant::LOTTERY_IMPORT_FILENAME . $roomId;
        $filename = vss_redis()->get($key);
        if (empty($filename)) {
            $info     = vss_model()->getLotteryUserModel()->findLotteryUserInfo(['room_id' => $roomId], ['title']);
            $filename = $info['title'];
            vss_redis()->set($key, $filename, 86400);
        }
        return $filename;
    }

    /**
     * 抽奖用户上传模板
     */
    public function importTemplate()
    {
        //Excel文件名
        $fileName = 'lottery_user_list' . date('YmdHis');
        vss_service()->getExportProxyService()->init($fileName)->putRow(['账号', '昵称'])->download();
    }

    /**
     * 获取可抽奖用户数据
     *
     * @param $roomId
     *
     * @return int
     */
    public static function getLotteryUserData($params)
    {
        $modelLotteryUser     = vss_model()->getLotteryUserModel();
        $condition            = [];
        $condition['room_id'] = $params['room_id'];
        if ($params['winner_out']) {
            $condition['is_winner'] = LotteryConstant::IS_WINNER_NO;
        }
        // 获取抽奖用户id列表 存入缓存
        $userList = $modelLotteryUser->getListForColumns($condition, [], ['id']);
        if (empty($userList)) {
            return 0;
        }
        $userList = $userList->toArray();
        $idArr    = array_column($userList, 'id');

        //$idArr为抽奖用户范围 存入缓存
        $keyLotteryUser = LotteryConstant::LOTTERY_RANGE_IMPORT . $params['room_id'];
        vss_redis()->del($keyLotteryUser);
        if ($idArr) {
            $pipe = vss_redis()->pipeline(function ($pipe) use ($idArr, $keyLotteryUser) {
                foreach ($idArr as $id) {
                    $pipe->sadd($keyLotteryUser, $id);
                }
            });
        }

        $count = vss_redis()->scard($keyLotteryUser);

        return $count;
    }

    /**
     * 通过keyword 查询抽奖导入名单用户列表
     *
     * @param $params
     *
     * @return array
     */
    public static function getLotteryUserByKeyword($params)
    {
        $keyword  = $params['keyword'];
        $userList = vss_model()->getLotteryUserModel()->where(function ($query) use ($keyword) {
            $query->where('nickname', $keyword)
                ->orWhere('username', $keyword);
        })->where('room_id', $params['room_id'])->get(['id', 'username', 'nickname'])->toArray();

        $data           = [];
        $keyLotteryUser = LotteryConstant::LOTTERY_RANGE_IMPORT . $params['room_id'];
        foreach ($userList as $user) {
            $isMember = vss_redis()->sismember($keyLotteryUser, $user['id']);
            if (!$isMember) {
                continue;
            }
            $data[] = $user;
        }
        return $data;
    }

    /**
     * 通过keyword 查询直播间抽奖用户列表
     *
     * @param $params
     *
     * @return array
     */
    public static function getLotteryJoinsByKeyword($params)
    {
        $lotteryType = $params['lottery_type'];
        $roomId      = $params['room_id'];
        $keyword     = $params['keyword'];
        $model       = vss_model()->getRoomJoinsModel()->where(function ($query) use ($keyword) {
            $query->where('room_joins.nickname', $keyword)
                ->orWhere('room_joins.username', $keyword);
        })->where('room_joins.room_id', $roomId);

        //查询类型 对应查询范围
        switch ($lotteryType) {
            case 1:
                //全体参会者用户
                $model->where('room_joins.role_name', '>', 1);
                break;
            case 2:
                //参与问卷的参会者
                $model->where('room_joins.role_name', '>', 1)
                    ->where('room_joins.is_answered_questionnaire', 1);
                break;
            case 3:
                //参与签到的参会者
                $model->where('room_joins.role_name', '>', 1)
                    ->where('room_joins.is_signed', 1);
                break;
            case 4:
                //全体观众用户
                $model->where('room_joins.role_name', 2);
                break;
            case 5:
                //已登录的观众
                $model->leftjoin('accounts', 'room_joins.account_id', 'accounts.account_id');
                $model->where('accounts.account_type', 2);
                break;
            case 6:
                //参与问卷的观众
                $model->where('room_joins.role_name', 2)
                    ->where('room_joins.is_answered_questionnaire', 1);
                break;
            case 7:
                //参与签到的观众
                $model->where('room_joins.role_name', 2)
                    ->where('room_joins.is_signed', 1);
                break;
            default:
                self::getInstance()->fail(ResponseCode::COMP_LOTTERY_RANGE_ERROR);
                break;
        }
        $joinList       = $model->get([
            'room_joins.account_id',
            'room_joins.username',
            'room_joins.nickname',
            'room_joins.avatar'
        ])->toArray();
        $keyLotteryUser = LotteryConstant::LOTTERY_RANGE_ACCOUNTS . $roomId;
        $data           = [];

        foreach ($joinList as $join) {
            $isMember = vss_redis()->sismember($keyLotteryUser, $join['account_id']);
            if (!$isMember) {
                continue;
            }
            $data[] = $join;
        }
        return $data;
    }

    /**
     * 抽奖导出信息保存
     * @param $params
     * @return \vhallComponent\export\models\ExportModel
     * @author  xiangliang.liu
     * @date   2021/6/17
     */

    public function exportLottery($params)
    {
        vss_validator($params, [
            'il_id'      => 'required',
            'room_id'    => 'required',
            'account_id' => 'required',
            'begin_time' => '',
            'end_time'   => '',
            'sort_type'  => '',
        ]);
        $params['sort_type'] = $params['sort_type'] ?? 'desc';

        $fileName = 'ilId_' . $params['il_id'] . '_lottery_list_' . date('Ymd_His');
        $header = ['抽奖时间','房间ID', '抽奖ID', '账号','昵称','抽奖标题','姓名','手机号码','备注'];
        $insert = [
            'export'     => LotteryConstant::EXPORT_LOTTERY,
            'il_id'      => $params['il_id'],
            'account_id' => $params['account_id'],
            'file_name'  => $fileName,
            'title'      => $header,
            'params'     => json_encode($params),
            'callback'   => 'lottery:getLotteryExportData',
        ];
        return vss_model()->getExportModel()->create($insert);
    }

    /**
     * 抽奖导出
     * @param $export
     * @param $filePath
     * @return bool
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @author  xiangliang.liu
     * @date   2021/6/17
     */
    public function getLotteryExportData($export, $filePath)
    {
        $params = json_decode($export['params'], true);
        $header = json_decode($export['title'], true);
        $file   = $filePath . $export['file_name'];

        // 根据 id 做分页查询

        $exportProxyService = vss_service()->getExportProxyService()->init($file)->putRow($header);
        $params['source_id'] = $params['room_id'];
        $params['page'] = 1;
        $params['page_size'] = 200;
        while (true) {

            $data = self::detailList($params);

            foreach($data['list'] as $value){
                foreach ($value['user'] as $user){
                    $row_lottery = [];
                    $row_lottery[] = $value['created_at'];  //抽奖时间
                    $row_lottery[] = $params['il_id'];  //房间ID
                    $row_lottery[] = $value['id'];  //抽奖ID
                    $row_lottery[] = $user['username'];  //账号
                    $row_lottery[] = $user['lottery_user_nickname'];  //昵称
                    $row_lottery[] = $value['title'];  //中奖规则
                    $row_lottery[] = $user['lottery_user_name'];  //姓名
                    $row_lottery[] = $user['lottery_user_phone'];  //手机号码
                    $row_lottery[] = $user['lottery_user_remark'];  //备注
                    $exportProxyService->putRows($row_lottery);
                }
            }

            if($data['count'] <= ($data['offset']+$data['limit'])){
                break;
            }
            $params['page'] += 1;
        }

        $exportProxyService->close();

        //修改导出表状态
        vss_model()->getExportModel()->getInstance()->where('id', $export['id'])->update(['status' => 3]);

        return true;
    }

    private static function _pageToOffset($page, $pageSize)
    {
        $page     = $page >= 1 ? $page : 1;
        $pageSize = $pageSize > 0 ? $pageSize : 10;
        $offset   = ($page - 1) * $pageSize;
        $limit    = $pageSize;
        $result   = [
            $offset,
            $limit,
        ];
        return $result;
    }
}
