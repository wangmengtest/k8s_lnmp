<?php
/**
 * Created by PhpStorm.
 * User: zhangxz
 * Date: 2018/8/7
 * Time: 上午10:35
 */

namespace Vss\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\GuzzleException;
use Vss\Traits\SingletonTrait;
use Vss\Utils\Dto\GuzzleResponse;

class HttpUtil
{
    use SingletonTrait;

    /**
     * 发送http get 请求
     *
     * @param       $uri
     * @param array $parameters 参数
     * @param null  $headers    头
     * @param int   $timeOut    超时时间
     *
     * @return GuzzleResponse
     */
    public static function get($uri, array $parameters = [], $headers = null, int $timeOut = 0): GuzzleResponse
    {
        $client         = new Client([
            "base_uri"             => $uri,
            "timeout"              => $timeOut,
            RequestOptions::VERIFY => false,
        ]);
        $logger         = vss_logger();
        $guzzleResponse = new GuzzleResponse();
        try {
            $beginTime = microtime(true);

            //设置request_id
            if (!isset($headers['request-id'])) {
                $headers['request-id'] = RequestIdUtil::get();
            }
            
            // 打印info 级别日志
            $logger->info("发送get请求 开始 !", [
                'uri'        => $uri,
                'headers'    => $headers,
                'parameters' => $parameters
            ]);

            $response = $client->request("get", $uri, [
                RequestOptions::FORCE_IP_RESOLVE => 'v4', // 由于国内ipv6 网络不完善，所以强制使用ipv4
                RequestOptions::VERIFY           => false,
                RequestOptions::HEADERS          => $headers,
                RequestOptions::QUERY            => $parameters
            ]);

            $endTime = microtime(true);

            // 封装返回对象
            $guzzleResponse->setSuccess(true);
            $guzzleResponse->setHeaders($response->getHeaders());
            $guzzleResponse->setCode($response->getStatusCode());
            $guzzleResponse->setData($response->getBody()->getContents());
            // 打印日志，包括返回数据，
            // 响应时间，其中响应时间是指从发送请求开始到接受到response 对象结束
            @$logger->info("发送get请求 结束 ! 耗时 " . bcsub($endTime, $beginTime, 3) . "秒", [
                'response' => $guzzleResponse->getData()
            ]);
            // 返回类型是json，直接解析json字符串返回
            if (!is_null(json_decode($guzzleResponse->getData()))) {
                $guzzleResponse->setData(json_decode($guzzleResponse->getData(), true));
            }
        } catch (GuzzleException $e) {
            // 出错 打印错误详细日志，错误可以定位到指定文件，指定行
            @$logger->error("发送get请求 出错! " . $e->getTraceAsString());
            $guzzleResponse->setData([]);
            $guzzleResponse->setCode($e->getCode());
            $guzzleResponse->setSuccess(false);
            $guzzleResponse->setMessage($e->getMessage());
        }
        return $guzzleResponse;
    }

    /**
     * 发送http post 请求
     * 默认post 使用 application/x-www-form-urlencoded
     * 如需修改，在 $headers 里添加 Content-Type
     *
     * @param       $uri
     * @param array $parameters
     * @param null  $headers
     * @param int   $timeOut
     *
     * @return GuzzleResponse
     */
    public static function post($uri, array $parameters = [], $headers = null, int $timeOut = 0): GuzzleResponse
    {
        $client         = new Client([
            "base_uri"             => $uri,
            "timeout"              => $timeOut,
            RequestOptions::VERIFY => false,
        ]);
        $logger         = vss_logger();
        $guzzleResponse = new GuzzleResponse();
        try {
            $beginTime = microtime(true);

            //设置request_id
            if (!isset($headers['request-id'])) {
                $headers['request-id'] = RequestIdUtil::get();
            }

            // 打印info 级别日志
            $logger->info("发送post请求 开始 !", [
                'uri'        => $uri,
                'parameters' => $parameters
            ]);

            // 添加默认post 格式
            if (empty($headers['Content-Type'])) {
                $headers['Content-Type'] = 'application/x-www-form-urlencoded';
            }

            $requestBody = [
                RequestOptions::FORCE_IP_RESOLVE => 'v4', // 由于国内ipv6 网络不完善，所以强制使用ipv4
                RequestOptions::VERIFY           => false,
                RequestOptions::HEADERS          => $headers,
                RequestOptions::FORM_PARAMS      => $parameters
            ];

            if (!is_array($parameters)) {
                $jsonParameters = json_decode($parameters);
                if (($jsonParameters && is_object($jsonParameters)) ||
                    (is_array($jsonParameters) && !empty($jsonParameters))) {
                    unset($requestBody[RequestOptions::FORM_PARAMS]);
                    $requestBody[RequestOptions::BODY] = $parameters;
                }
            }

            $response = $client->request("POST", $uri, $requestBody);

            $endTime = microtime(true);

            // 封装返回对象
            $guzzleResponse->setSuccess(true);
            $guzzleResponse->setHeaders($response->getHeaders());
            $guzzleResponse->setCode($response->getStatusCode());
            $guzzleResponse->setData($response->getBody()->getContents());
            // 打印日志，包括返回数据，
            // 响应时间，其中响应时间是指从发送请求开始到接受到response 对象结束
            $logger->info("发送post请求 结束 ! 耗时 " . bcsub($endTime, $beginTime, 3) . "秒", [
                'response' => $guzzleResponse->getData()
            ]);
            // 返回类型是json，直接解析json字符串返回
            if (!is_null(json_decode($guzzleResponse->getData()))) {
                $guzzleResponse->setData(json_decode($guzzleResponse->getData(), true));
            }
        } catch (GuzzleException $e) {
            // 出错 打印错误详细日志，错误可以定位到指定文件，指定行
            @$logger->error("发送post请求 出错! " . $e->getTraceAsString());
            $guzzleResponse->setData([]);
            $guzzleResponse->setCode($e->getCode());
            $guzzleResponse->setSuccess(false);
            $guzzleResponse->setMessage($e->getMessage());
        }

        return $guzzleResponse;
    }
}
