<?php
/**
 *+----------------------------------------------------------------------
 * Class Context
 * SMS策略环境访问器
 *+----------------------------------------------------------------------
 * @method bool send(string $mobile, string $content)
 * @method bool sendVerifyCode(string $mobile, string $verifyCode)
 * @method string getCode()
 * @method string getMsg()
 * @method Strategy setContentSame(bool $same = true)
 *+----------------------------------------------------------------------
 * @package Sms
 * @author ensong.liu@vhall.com
 * @date 2019-03-29 23:40:37
 * @version v1.0.0
 *+----------------------------------------------------------------------
 */
class Sms
{
    /**
     * 策略对象
     *
     * @var object
     */
    private $strategy;

    /**
     * Context constructor.
     *
     * @param \Sms\Strategy $strategy
     * @param array $config
     */
    public function __construct(\Sms\Strategy $strategy, array $config = [])
    {
        $this->strategy = new $strategy($config);
    }

    /**
     * 访问策略方法
     *
     * @author ensong.liu@vhall.com
     * @date 2019-03-29 23:39:31
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->strategy, $name], $arguments);
    }
}
