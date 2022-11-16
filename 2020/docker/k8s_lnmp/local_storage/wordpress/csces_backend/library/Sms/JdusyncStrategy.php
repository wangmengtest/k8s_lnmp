<?php
/**
 *+----------------------------------------------------------------------
 * @file JdusyncStrategy.php
 * @date 2019/3/29 17:36
 *+----------------------------------------------------------------------
 */

namespace Sms;

/**
 *+----------------------------------------------------------------------
 * Class JdusyncStrategy
 * 京东大学短信接口策略类
 * +----------------------------------------------------------------------
 * @package Sms
 * @author ensong.liu@vhall.com
 * @date 2019-03-29 17:37:45
 * @version v1.0.0
 *+----------------------------------------------------------------------
 */
class JdusyncStrategy extends Strategy
{
    /**
     * 接口地址
     *
     * @var string
     */
    public $baseUri = 'https://pre-jdusync.jd.com';

    /**
     * 接入方标识
     *
     * @var string
     */
    public $appKey = '';

    /**
     * 状态码列表
     *
     * @var array
     */
    private $codeList = [
        '10000' => '发送成功',
        '40000' => 'appkey相关错误',
        '40100' => '手机号相关错误',
        '40200' => '内容相关错误',
        '40300' => '发送失败相关错误',
        '90000' => '其他错误',
    ];

    /**
     * 发送短信
     *
     * @author ensong.liu@vhall.com
     * @date 2019-03-29 17:39:57
     *
     * @param string $mobile
     * @param string $content
     *
     * @return bool
     */
    public function send(string $mobile, string $content):bool
    {
        return true;
    }

    /**
     * 发送验证码短信
     * 仅用于国内手机号发送验证码信息，不能发送通知/促销等信息;
     * 验证码 string，注意：仅验证码，不需要其他提示信息，例如:123456
     *
     * @author ensong.liu@vhall.com
     * @date 2019-03-29 21:30:07
     *
     * @param string $mobile
     * @param string $verifyCode 注意：仅验证码，不需要其他提示信息，例如:123456
     *
     * @return bool
     */
    public function sendVerifyCode(string $mobile, string $verifyCode):bool
    {
        $response = $this->post('/api/sms/sendVerifyCode', [
            'appKey'       => $this->appKey,
            'mobileNumber' => $mobile,
            'verifyCode'   => $verifyCode,
        ]);

        if ($response !== false) {
            $body = \GuzzleHttp\json_decode($response->getBody(), true);
            $this->code = $body['code'];
            $this->msg = $body['msg'];

            return true;
        }

        return false;
    }
}
