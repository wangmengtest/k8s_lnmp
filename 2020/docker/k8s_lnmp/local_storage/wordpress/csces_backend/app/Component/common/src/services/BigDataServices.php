<?php
/**
 * 大数据用户行为上报
 */

namespace App\Component\common\src\services;

use Vss\Utils\HttpUtil;
use Vss\Common\Services\WebBaseService;

class BigDataServices extends WebBaseService
{
    /**
     * @return WebBaseService|BIgDataServicesImpl
     */
    public static function getInstance()
    {
        if (!isset(self::$_instance) || !self::$_instance instanceof static) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }

    /**
     * 接收上报基础参数
     *
     * @param $params
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function saveReportData($params)
    {
        return false;
    }

    /**
     * @return mixed
     */
    public function getRand()
    {
        $numbers = range(20, 2000);
        shuffle($numbers);
        $result = array_slice($numbers, 1, 1);
        return $result[0];
    }

    /**
     * 部分异步数据存入redis
     *
     * @param $params
     * @param $order_no
     * @param $type
     *
     * @return mixed
     * @throws \Exception
     */
    public function saveParams($params, $order_no, $type)
    {
        return true;
    }

    /**
     * 基础参数
     * @return array
     * @throws \Exception
     */
    public function baseData($param, $pt = false)
    {
        return [];
    }

    /**
     * 调查问卷(收到单个用户填写的调查问卷)
     *
     * @param $params
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function requestQuestionAnswersParams($params)
    {
        return false;
    }

    /**
     * 用户点击红包
     *
     * @param $params
     * @param $red_Packet_data
     * @param $type
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function requestRedPacketParams($params, $red_Packet_data, $type)
    {
        return false;
    }

    /**
     * 上麦记录
     *
     * @param $data
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function speakRecords($data)
    {
        return true;
    }

    /**
     * 下麦
     *
     * @param $params
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function requestNoSpeakParams($params)
    {
        return false;
    }

    /**
     * 打赏的时候
     *
     * @param $key
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function requestRewardParams($key)
    {
        return false;
    }

    /**
     * 送礼物
     *
     * @param $key
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function requestGiftParams($key)
    {
        return false;
    }

    /**
     * c端用户签到
     *
     * @param $params
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function requestClientSignParams($params)
    {
        return false;
    }

    /**
     * 向单个用户推送调查问卷
     *
     * @param $params
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function requestQuestionPushParams($params)
    {
        return false;
    }

    /**
     * 向单个用户推送考试试卷
     *
     * @param $params
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function requestExamPushParams($params)
    {
        return false;
    }

    /**
     * @param $url
     * @param $data
     *
     * @return bool|mixed
     */
    public function syncData($url, $data, $behavior)
    {
        return false;
    }

    /**
     * @param $uri
     *
     * @return bool|mixed
     */
    public function query($uri, $behavior)
    {
        if ($uri) {
            $uri      = vss_config('vssBigDataUrl') . $uri;
            $response = $this->curlGetRequest($uri);
            if ($response === false) {
                vss_logger()->info('vss_to_bigData_error',
                    ['behavior' => $behavior, 'data' => $response, 'url' => $uri]);
                return false;
            }
            vss_logger()->info('vss_to_bigData_success', ['behavior' => $behavior, 'data' => $response, 'url' => $uri]);
            return true;
        }
        return false;
    }

    /**
     * 发送请求到用户系统
     *
     * @param string $uri
     * @param array  $params
     *
     * @return mixed
     */
    public function request(string $uri, array $params = [])
    {
        $response = HttpUtil::post(vss_config('BUSINESS_CENTER_DOMAIN') . $uri, $params, null, 20);

        if ($response->getCode() != 200) {
            vss_logger()->info('consumer-sys-update-error', compact('uri', 'params', 'response'));
            return false;
        }
        vss_logger()->info('consumer-sys-update-success', compact('uri', 'params', 'response'));
        return true;
    }

    /**
     * 解析vsstoken,获取redis中的信息
     *
     * @param $vss_token
     *
     * @return bool|string
     * @throws \Exception
     */
    public function getDataInfoByToken($vss_token, $pt = false)
    {
        if ($pt) {
            if (isset($vss_token['rewarder_id'])) {
                $account_id = $vss_token['rewarder_id'];
            } elseif (isset($vss_token['gift_user_id'])) {
                $account_id = $vss_token['gift_user_id'];
            }
        } else {
            $account_id = vss_service()->getTokenService()->getTokenInfo($vss_token, 'third_party_user_id');
        }
        $ret = vss_redis()->get('report_' . $account_id);
        vss_logger()->info('redis-get-param', ['key' => 'report_' . $account_id, 'data' => $ret]);
        return $ret ?? false;
    }

    /**
     * get请求
     *
     * @param $url
     *
     * @return bool|string
     */
    public function curlGetRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            vss_logger()->info('Curl failed GET ', ['ERROR' => curl_error($ch), 'url' => $url]);
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        return $result;
    }

    /**
     * 系统更新用户信息
     *
     * @param $basedata
     * @param $params
     *
     * @return bool|mixed
     */
    public function sysUserInfoUpdate($basedata, $params)
    {
        return false;
    }

    /**
     * 更新用户相关信息
     *
     * @param $data
     * @param $params
     *
     * @return mixed
     */
    public function getParamsForUpdate($data, $params)
    {
        return false;
    }

    /**
     * B端用户发起签到
     *
     * @param $params
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function requestServerSignParams($params)
    {
        return false;
    }

    /**
     *  B端用户发起抽奖
     *
     * @param $params
     * @param $data
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function requestServerLotteryParams($params)
    {
        return false;
    }

    /**
     * @param $k
     *
     * @return string
     */
    public function getUrl($k)
    {
        $id  = $s = $this->getRand();
        $url = '?k=' . $k;
        $url .= '&id=' . $id . '&s=' . $s . '&token=';
        return $url;
    }
}
