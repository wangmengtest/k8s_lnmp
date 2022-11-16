<?php

namespace App\Component\sms\src\services;

use Exception;
use Sms\Sms;
use Vss\Common\Services\WebBaseService;

/**
 *+----------------------------------------------------------------------
 * Class CodeService
 * 手机验证码服务类
 *+----------------------------------------------------------------------
 *
 * @package App\Services
 * @author  ensong.liu@vhall.com
 * @date    2019年05月07日11:54:15
 * @version v1.0.0
 *+----------------------------------------------------------------------
 */
class CodeService extends WebBaseService
{
    /**
     * @const string
     */
    const CODE_KEY = 'enjoy:phone:code:';

    const INTERVAL_KEY = 'enjoy:phone:code:interval:';

    /**
     * 发送手机验证码
     *
     * @param string $phone    手机号码
     * @param int    $expires  验证码过期时间/秒
     * @param int    $interval 发送间隔时间/秒
     *
     * @return bool
     * @throws Exception
     * @author ensong.liu@vhall.com
     * @date   2019-05-07 12:10:59
     *
     */
    public function send($phone, int $expires = 180, int $interval = 60)
    {
        $code = rand(100000, 999999);
        vss_redis()->set(sprintf('%s%s', self::CODE_KEY, $phone), $code, $expires);
        vss_redis()->set(sprintf('%s%s', self::INTERVAL_KEY, $phone), true, $interval);
        $sms = new Sms();
        return $sms->sendVerifyCode($phone, $code);
    }

    /**
     * 检验手机验证码
     *
     * @param string $phone
     * @param string $code
     *
     * @return bool
     * @throws Exception
     * @author ensong.liu@vhall.com
     * @date   2019-05-07 12:17:01
     *
     */
    public function checkCode($phone, $code)
    {
        if (empty(vss_redis()->get(sprintf('%s%s', self::CODE_KEY, $phone)))) {
            return false;
        }

        if (vss_redis()->get(sprintf('%s%s', self::CODE_KEY, $phone)) != $code) {
            return false;
        }

        vss_redis()->del(sprintf('%s%s', self::CODE_KEY, $phone));
        vss_redis()->del(sprintf('%s%s', self::INTERVAL_KEY, $phone));

        return true;
    }

    /**
     * 检验发送间隔时间
     * 间隔时间内返回true，否则返回false
     *
     * @param string $phone 手机号码
     *
     * @return bool
     * @throws Exception
     * @author ensong.liu@vhall.com
     * @date   2019-05-07 12:29:01
     *
     */
    public function checkInterval($phone)
    {
        return vss_redis()->get(sprintf('%s%s', self::INTERVAL_KEY, $phone)) == true;
    }
}
