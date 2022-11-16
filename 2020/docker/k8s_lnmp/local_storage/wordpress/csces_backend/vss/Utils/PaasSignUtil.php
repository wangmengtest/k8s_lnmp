<?php
/**
 * Created by PhpStorm.
 * User: zhangxz
 * Date: 2018/8/18
 * Time: 下午3:06
 */

namespace Vss\Utils;

use JsonException;
use Vss\Traits\SingletonTrait;

class PaasSignUtil
{
    use SingletonTrait;

    public function generateParams($param, $appId, $secret)
    {
        $param['signed_at'] = time();
        $param['app_id']    = $appId;
        $param['sign']      = $this->sign($param, $secret);
        return $param;
    }

    public function sign(array $arr, $secretKey)
    {
        // 去除因重复调用可能产生的sign字段
        unset($arr['sign'], $arr['token']);
        if (!empty($arr['document'])) {
            unset($arr['document']);
        }
        // 按键名称排序
        ksort($arr);

        // 初始化签名字串
        $str = '';

        // 将键值组合连接到签名字串上
        foreach ($arr as $k => $v) {
            $str .= $k . $v;
        }
        // 将签名字串前后两端加上秘钥
        $str = $secretKey . $str . $secretKey;

        // 返回MD5运算后的结果
        return md5($str);
    }

    /**
     * @param array $data
     * @param       $privateKey
     *
     * @return bool
     * @throws
     */
    public function checkCallbackSign(array $data, $privateKey): bool
    {
        if (empty($data['signature'])) {
            throw new JsonException("sign.miss", 403);
        }
        $sign = self::makeCallbackSignature($data, $privateKey);
        if ($sign != $data['signature']) {
            throw new JsonException("sign.error", 403);
        }
        return true;
    }

    public function makeCallbackSignature(array $data, $privateKey): string
    {
        unset($data['signature']);
        ksort($data);
        $str        = '';
        $privateKey = md5($privateKey);
        foreach ($data as $k => $v) {
            $str .= $k . '|' . $privateKey . '|' . $v;
        }
        return md5($str);
    }

    /**
     * @param $appName
     *
     * @return mixed
     */
    public function getPaasAppSecret($appName)
    {
        return vss_config('paas.apps.' . $appName . '.appSecret');
    }

    /**
     * @param $appId
     *
     * @return mixed
     */
    public function getPaasAppSecretByAppId($appId)
    {
        foreach (vss_config('paas.apps') as $v) {
            if ($v['appId'] == $appId) {
                return $v['appSecret'];
            }
        }
        return "";
    }
}
