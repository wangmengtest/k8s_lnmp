<?php

namespace vhallComponent\pay\services;

use Vss\Utils\HttpUtil;
use DateTime;
use Vss\Common\Services\WebBaseService;
use vhallComponent\pay\constants\PayConstant;

class PayService extends WebBaseService
{
    public static function makeBizOrderNO($prefix = '')
    {
        return $prefix . (new DateTime())->format('YmdHisu') . mt_rand(1000, 9999);
    }

    public static function getPayment($params)
    {
        if (empty($params['open_id'])) {
            $params['open_id'] = '';
        }
        $params['app_id']    = vss_config('paas.apps.lite.appId');
        $params['timestamp'] = date('Y-m-d H:i:s');
        $params['nonce_str'] = md5('vss_pay_nonce_str_' . microtime(true) . '_' . mt_rand(0, 99999));
        $params['notify_url']      = vss_config('application.url') . '/api/pay/notify';

        $fakePay = vss_config('pay.fakePay');
        if ($fakePay) {
            unset($params['notify_url']);
            $params['signed_at']      = time();
            $params['sign']      = self::makeSign($params);
            HttpUtil::post(vss_config('application.url') . '/api/pay/notify', $params, null, 20);
            return '';
        }

        return vss_service()->getPublicForwardService()->payGetPayment($params);
    }

    public static function checkSign($param)
    {
        return !empty($param) && is_array($param) && !empty($param['sign']) && self::makeSign($param) == $param['sign'];
    }

    /**
     * 交易信息存入缓存    该方法只在测试环境试验
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public static function setTradeNoCache($data)
    {
        //配置未开启或缺少参数不再继续
        $ifPay = vss_config('pay.fakePay');
        if (!$ifPay || empty($data['trade_no'])) {
            return false;
        }

        vss_logger()->info('setTradeNoCache', ['data'=>$data]);
        $res = vss_redis()->hmset(PayConstant::TRADE_NO . $data['trade_no'], $data);
        vss_redis()->expire(PayConstant::TRADE_NO . $data['trade_no'], 60);
        return true;
    }

    /**
     * 获取交易信息缓存  该方法只在测试环境试验
     * @param $tradeNo
     * @return bool
     */
    public static function getTradeNoCache($tradeNo)
    {
        //配置未开启或缺少参数不再继续
        $ifPay = vss_config('pay.fakePay');
        if (!$ifPay || empty($tradeNo)) {
            return false;
        }
        return vss_redis()->hgetall(PayConstant::TRADE_NO . $tradeNo);
    }

    private static function makeSign($param)
    {
        if (is_array($param)) {
            $secretKey = vss_config('paas.apps.lite.appSecret');
            if (array_key_exists('sign', $param)) {
                // 去除因重复调用可能产生的sign字段
                unset($param['sign']);
            }

            // 按键名称排序
            ksort($param);

            // 初始化签名字串
            $str = '';
            // 将键值组合连接到签名字串上
            foreach ($param as $k => $v) {
                $str .= $k . $v;
            }
            // 将签名字串前后两端加上秘钥
            $str = $secretKey . $str . $secretKey;

            // 返回MD5运算后的结果
            return md5($str);
        }
        return '';
    }
}
