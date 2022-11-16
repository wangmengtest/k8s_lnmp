<?php
/**
 *+----------------------------------------------------------------------
 * @file SmsStrategy.php
 * @date 2019/3/29 16:32
 *+----------------------------------------------------------------------
 */

namespace Sms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 *+----------------------------------------------------------------------
 * Class Strategy
 * SMS抽象策略类
 *+----------------------------------------------------------------------
 * @package Sms
 * @author ensong.liu@vhall.com
 * @date 2019-03-29 23:32:08
 * @version v1.0.0
 *+----------------------------------------------------------------------
 */
abstract class Strategy
{
    /**
     * 接口地址
     *
     * @var string
     */
    public $baseUri = '';

    /**
     * 状态码
     *
     * @var string
     */
    protected $code = '';

    /**
     * 状态码信息
     *
     * @var string
     */
    protected $msg = '';

    /**
     * 内容是否相同
     *
     * @var bool
     */
    protected $contentSame = true;

    /**
     * JdusyncStrategy constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->initConfig($config);
    }

    /**
     * 初始化配置
     *
     * @author ensong.liu@vhall.com
     * @date 2019-03-31 20:04:50
     *
     * @param array $config
     *
     * @return bool
     */
    protected function initConfig($config = [])
    {
        return array_walk($config, function ($value, $key) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        });
    }

    /**
     * 发送短信
     *
     * @author ensong.liu@vhall.com
     * @date 2019-03-29 17:15:27
     *
     * @param string $mobile
     * @param string $content
     *
     * @return bool
     */
    abstract public function send(string $mobile, string $content):bool;

    /**
     * 发送验证码短信
     *
     * @author ensong.liu@vhall.com
     * @date 2019-03-29 21:26:58
     *
     * @param string $mobile
     * @param string $verifyCode
     *
     * @return bool
     */
    abstract public function sendVerifyCode(string $mobile, string $verifyCode):bool;

    /**
     * 设置内容是否相同
     *
     * @author ensong.liu@vhall.com
     * @date 2019-03-30 00:27:16
     *
     * @param bool $same
     *
     * @return $this
     */
    public function setContentSame(bool $same = true)
    {
        $this->contentSame = $same;

        return $this;
    }

    /**
     * 发送http请求
     *
     * @author ensong.liu@vhall.com
     * @date 2019-03-29 22:01:56
     *
     * @param $method
     * @param string $uri
     * @param array $options
     *
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface
     */
    protected function request($method, $uri = '', array $options = [])
    {
        try {
            $response = (new Client([
                'base_uri' => $this->baseUri,
                'timeout'  => 30,
            ]))->request($method, $uri, $options);

            if ($response->getStatusCode() != 200) {
                throw new RequestException($response->getReasonPhrase(), $response->getStatusCode());
            }
        } catch (RequestException $e) {
            vss_logger()->error(__METHOD__, $e->getTrace());
            $response = false;
        }

        return $response;
    }

    /**
     * 发送get请求
     *
     * @author ensong.liu@vhall.com
     * @date 2019-03-29 20:56:30
     *
     * @param string $uri
     * @param array $query
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    protected function get(string $uri, array $query = [])
    {
        return $this->request('GET', $uri, [
            'query' => $query,
        ]);
    }

    /**
     * 发送post请求
     *
     * @author ensong.liu@vhall.com
     * @date 2019-03-29 20:56:33
     *
     * @param string $uri
     * @param array $body
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    protected function post(string $uri, array $body = [])
    {
        return $this->request('POST', $uri, [
            'form_params' => $body,
        ]);
    }

    /**
     * 获取状态码
     *
     * @author ensong.liu@vhall.com
     * @date 2019-03-29 23:27:26
     * @return string
     */
    final public function getCode()
    {
        return $this->code;
    }

    /**
     * 获取状态码信息
     *
     * @author ensong.liu@vhall.com
     * @date 2019-03-29 23:27:39
     * @return string
     */
    final public function getMsg()
    {
        return $this->msg;
    }

    protected function curlPost($url, $postFields){
        $postFields = json_encode($postFields);
        $ch = curl_init ();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8'   //json版本需要填写  Content-Type: application/json;
            )
        );
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); //若果报错 name lookup timed out 报错时添加这一行代码
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt( $ch, CURLOPT_TIMEOUT,60);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec ( $ch );
        if (false == $ret) {
            $result = curl_error(  $ch);
        } else {
            $rsp = curl_getinfo( $ch, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                $result = "请求状态 ". $rsp . " " . curl_error($ch);
            } else {
                $result = $ret;
            }
        }
        curl_close ( $ch );
        return $result;
    }
}
