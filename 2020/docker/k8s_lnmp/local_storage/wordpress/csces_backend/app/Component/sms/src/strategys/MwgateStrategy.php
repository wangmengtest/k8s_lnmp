<?php
/**
 *+----------------------------------------------------------------------
 * @file MwgateStrategy.php
 * @date 2019/3/30 00:01
 *+----------------------------------------------------------------------
 */

namespace App\Component\sms\src\strategys;

use Sms\Strategy;

/**
 *+----------------------------------------------------------------------
 * Class MwgateStrategy
 * 梦网短信接口策略类
 *+----------------------------------------------------------------------
 * @package Sms
 * @author ensong.liu@vhall.com
 * @date 2019-03-30 00:03:18
 * @version v1.0.0
 *+----------------------------------------------------------------------
 */
class MwgateStrategy extends Strategy
{
    /**
     * 接口地址
     *
     * @var string
     */
    public $baseUri = 'http://61.135.198.131:8023';

    /**
     * 登录用户ID
     *
     * @var string
     */
    public $userId = '';

    /**
     * 登录用户密码
     *
     * @var string
     */
    public $password = '';

    /**
     * 状态码列表
     *
     * @var array
     */
    private $codeList = [
        '-1'     => '参数为空。信息、电话号码等有空指针，登陆失败',
        '-2'     => '电话号码个数超过100',
        '-10'    => '申请缓存空间失败',
        '-11'    => '电话号码中有非数字字符',
        '-12'    => '有异常电话号码',
        '-13'    => '电话号码个数与实际个数不相等',
        '-14'    => '实际号码个数超过100',
        '-101'   => '发送消息等待超时',
        '-102'   => '发送或接收消息失败',
        '-103'   => '接收消息超时',
        '-200'   => '其他错误',
        '-999'   => 'web服务器内部错误',
        '-10001' => '用户登陆不成功',
        '-10002' => '提交格式不正确',
        '-10003' => '用户余额不足',
        '-10004' => '手机号码不正确',
        '-10005' => '计费用户帐号错误',
        '-10006' => '计费用户密码错',
        '-10007' => '账号已经被停用',
        '-10008' => '账号类型不支持该功能',
        '-10009' => '其它错误',
        '-10010' => '企业代码不正确',
        '-10011' => '信息内容超长',
        '-10012' => '不能发送联通号码',
        '-10013' => '操作员权限不够',
        '-10014' => '费率代码不正确',
        '-10015' => '服务器繁忙',
        '-10016' => '企业权限不够',
        '-10017' => '此时间段不允许发送',
        '-10018' => '经销商用户名或密码错',
        '-10019' => '手机列表或规则错误',
        '-10021' => '没有开停户权限',
        '-10022' => '没有转换用户类型的权限',
        '-10023' => '没有修改用户所属经销商的权限',
        '-10024' => '经销商用户名或密码错',
        '-10025' => '操作员登陆名或密码错误',
        '-10026' => '操作员所充值的用户不存在',
        '-10027' => '操作员没有充值商务版的权限',
        '-10028' => '该用户没有转正不能充值',
        '-10029' => '此用户没有权限从此通道发送信息',
        '-10030' => '不能发送移动号码',
        '-10031' => '手机号码(段)非法',
        '-10032' => '用户使用的费率代码错误',
        '-10033' => '非法关键词',
    ];

    /**
     * 发送短信
     *
     * @author ensong.liu@vhall.com
     * @date 2019-03-29 17:39:57
     *
     * @param string $mobile //多个手机号逗号隔开
     * @param string $content
     *
     * @return bool
     */
    public function send(string $mobile, string $content):bool
    {
        switch ($this->contentSame) {
            case true:
                $response = $this->post('/MWGate/wmgw.asmx/MongateSendSubmit', [
                    'pszSubPort' => '*&',
                    'userId'     => $this->userId,
                    'password'   => $this->password,
                    'pszMobis'   => $mobile,
                    'pszMsg'     => $content,
                    'iMobiCount' => substr_count($mobile, ',') + 1,
                ]);
                break;
            case false:
                //todo:参数无法确认，发送失败
                $response = $this->post('/MWGate/wmgw.asmx/MongateMULTIXSend', [
                    'userId'   => $this->userId,
                    'password' => $this->password,
                    'pszMobis' => $mobile,
                    'multixmt' => $content,
                ]);
                break;
        }
        list($this->code, $this->msg) = (array)simplexml_load_string($response->getBody());
        $this->msg = ($this->code < 0 && $this->msg) ?: $this->codeList[$this->code];

        return $this->code > 0 ? true : false;
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
        return $this->send($mobile, '尊敬的用户:您的验证码为' . $verifyCode . ', 本验证码3分钟内有效, 感谢您使用');
    }
}
