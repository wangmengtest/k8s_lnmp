<?php

namespace Vss\Traits;

trait ResponseCodeTrait
{
    /**
     * 获取响应的 code he msg
     * @auther yaming.feng@vhall.com
     * @date 2021/6/8
     *
     * @param string $langKey
     *
     * @return array
     */
    public static function getResponse($langKey, array $replace = [])
    {
        $code = static::getResponseCode($langKey);
        $msg  = static::getResponseMessage($langKey, $replace);

        return ['code' => $code, 'msg' => $msg, 'key' => $langKey, 'replace' => $replace];
    }

    /**
     * 获取错误码对应的文本信息
     * @auther yaming.feng@vhall.com
     * @date 2021/6/1
     *
     * @param string $code
     *
     * @return string
     */
    public static function getResponseMessage($langKey, array $replace = []): string
    {
        $lang     = request()->get('lang', config('app.locale', 'zh'));
        $transMsg = __('code.' . $langKey, $replace, $lang);
        if (strpos($transMsg, 'code.') === false) {
            return $transMsg;
        }

        return "unknown error";
    }

    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/6/8
     *
     * @param string $langKey
     *
     * @return int
     */
    public static function getResponseCode($langKey): int
    {
        $codeMap = config('responsecode');

        foreach ($codeMap as $rule => $code) {
            $rule = rtrim($rule, '.*');
            if (strpos($langKey, $rule) === 0) {
                $module = strtolower(get_module_name());
                // 30002 是 admin 的跳转到登录页面的 code, console 的是 401
                if ($code == 30002 && $module != 'admin') {
                    $code = 401;
                }
                return $code;
            }
        }
        return 50000;
    }
}
