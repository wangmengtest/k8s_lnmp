<?php

namespace vhallComponent\room\services;

use App\Constants\ResponseCode;
use Exception;
use Vss\Common\Services\WebBaseService;
use vhallComponent\room\constants\TokenConstant;
use vhallComponent\room\models\RoomJoinsModel;

/**
 * TokenServiceTrait
 *
 * @uses     yangjin
 * @date     2020-08-06
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class TokenService extends WebBaseService
{
    public static $_account_id = 0;

    public static $_app_id     = 'af314787';

    /**
     * 创建token
     *
     * @param $params ['app_id', 'third_party_user_id', 'expire_time']
     *
     * @return array
     *
     * @throws Exception
     */
    public function create($params)
    {
        // 时间差值
        $expireTime = strtotime($params['expire_time']) - time();
        return [
            'access_token' => self::setTokenPermit($params['app_id'], $params['third_party_user_id'], $expireTime)
        ];
    }

    /**
     * 清除AccessToken
     *
     * @param $params ['app_id', 'access_token']
     *
     * @return array
     *
     * @throws Exception
     */
    public function destroyAccessToken($params)
    {
        $accessToken = $params['access_token'];
        $tokenExist  = self::getTokenInfo($accessToken);
        if ($tokenExist && $tokenExist['app_id'] == $params['app_id']) {
            vss_redis()->del($accessToken);
        } else {
            $this->fail(ResponseCode::EMPTY_TOKEN);
        }

        return [
            'destroy-token' => $accessToken,
        ];
    }

    /**
     * 设置访问权限
     *
     * @param      $appId
     * @param      $thirdPartyUserId
     * @param bool $expireTime
     *
     * @return string
     * @throws Exception
     */
    public function setTokenPermit($appId, $thirdPartyUserId, $expireTime = false)
    {
        $expireTime = $expireTime ?: 3600 * 24;

        $accessToken = self::createAccessToken($appId, $thirdPartyUserId);
        $permitMsg   = [
            'app_id'              => $appId,
            'third_party_user_id' => $thirdPartyUserId,
            'expire_time'         => date('Y-m-d H:i:s', time() + $expireTime),
        ];

        // 权限记录
        vss_redis()->set($accessToken, json_encode($permitMsg), $expireTime);
        return $accessToken;
    }

    /**
     * 生成TOKEN
     *
     * @param $appId
     * @param $thirdPartyUserId
     *
     * @return string
     */
    public function createAccessToken($appId, $thirdPartyUserId)
    {
        return TokenConstant::TOKEN_PERMIT_PREFIX . $appId . TokenConstant::TOKEN_PARTITION . substr(
            md5($appId . $thirdPartyUserId . self::getMsUnixTime()),
            8,
            16
        );
    }

    /**
     * 获取毫秒级别时间戳
     * @return int
     */
    public function getMsUnixTime()
    {
        list($microTime, $time) = explode(' ', microtime());
        $time = intval((floatval($time) + floatval($microTime)) * 1000);
        return $time;
    }

    /**
     * 获取token对应信息
     *
     * @param        $accessToken
     * @param string $fields
     *
     * @return bool|string
     * @throws Exception
     */
    public function getTokenInfo($accessToken, $fields = '')
    {
        $token = vss_redis()->get($accessToken);
        if (empty($token)) {
            return false;
        }
        $token = json_decode($token, true);
        if ($fields) {
            return $token[$fields];
        }
        return $token;
    }

    /**
     * 验证
     *
     * @param $accessToken
     * @param $controller
     * @param $action
     *
     * @return bool
     * @throws Exception
     */
    public function __checkToken($accessToken, $controller, $action)
    {
        $permitArr = self::getTokenInfo($accessToken);
//        $permit            = $permitArr['permit'];
        self::$_account_id = $permitArr['third_party_user_id'];
        self::$_app_id     = $permitArr['app_id'];

        return $permitArr;
    }

    /**
     * @return mixed
     */
    public function getAccountId()
    {
        return self::$_account_id;
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return vss_config('paas.apps.lite.appId');
    }

    /**
     * @param $room_id
     *
     * @return RoomJoinsModel
     *
     * @author  jin.yang@vhall.com
     * @date    2020-08-06
     */
    public function getCurrentJoinUser($room_id)
    {
        vss_validator([
            'room_id' => $room_id
        ], [
            'room_id' => 'required'
        ]);

        $join_user = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId($this->getAccountId(), $room_id);
        empty($join_user) && $this->fail(ResponseCode::BUSINESS_INVALID_USER);
        return $join_user;
    }

    /**
     * @param $room_id
     * @param $account_id
     *
     * @return array
     *
     * @author  jin.yang@vhall.com
     * @date    2020-08-06
     */
    public function getCurrentJoinUsers($room_id, $account_id)
    {
        vss_validator([
            'room_id' => $room_id
        ], [
            'room_id' => 'required'
        ]);

        $join_user = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId($account_id, $room_id);
        empty($join_user) && $this->fail(ResponseCode::BUSINESS_INVALID_USER);
        return $join_user->toArray();
    }

    /**
     * @param $accessToken
     *
     * @return bool|mixed|string
     * @throws Exception
     * @author  jin.yang@vhall.com
     * @date    2020-08-07
     */
    public function checkToken($accessToken)
    {
        $permitArr = self::getTokenInfo($accessToken);
        if (!$permitArr) {
            return false;
        }
        self::$_account_id = $permitArr['third_party_user_id'];
        self::$_app_id     = $permitArr['app_id'];
        return $permitArr;
    }

    /**
     *
     * @param $room_id
     * @param $account_id
     *
     * @return mixed|RoomJoinsModel
     *
     */
    public function getCurrentJoinUserByRoomId($room_id, $account_id)
    {
        vss_validator([
            'room_id' => $room_id
        ], [
            'room_id' => 'required'
        ]);

        vss_validator([
            'account_id' => $account_id
        ], [
            'account_id' => 'required',
        ]);
        $join_user = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId($account_id, $room_id);
        empty($join_user) && $this->fail(ResponseCode::BUSINESS_INVALID_USER);
        return $join_user;
    }
}
