<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Throwable;
use Vss\Exceptions\CallbackException;

/**
 * Paas 回调签名校验
 * Class VerifyCallbackSign
 * @package App\Http\Middleware
 */
class VerifyCallbackSign
{
    // 排除的路由
    protected $except = [];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     * @throws Throwable
     */
    public function handle(Request $request, Closure $next)
    {
        if (!is_matching_path($request, $this->except)) {
            $this->verifySign($request);
        }

        return $next($request);
    }

    /**
     * 校验签名
     * @auther yaming.feng@vhall.com
     * @date 2021/5/8
     *
     * @param Request $request
     *
     * @throws Throwable
     */
    protected function verifySign(Request $request)
    {
        $secretKey = vss_config('paas.apps.lite.appSecret');
        $signature = $request->get('signature');
        $params    = $request->except(['vss_account_info', 'vss_admin', 'signature']);

        throw_if(!$signature, new CallbackException('signature not exist.'));

        $str = '';
        ksort($params);
        $privateKey = md5($secretKey);
        foreach ($params as $k => $v) {
            $str .= $k . '|' . $privateKey . '|' . $v;
        }

        throw_if(md5($str) != $signature, new CallbackException('signature error.'));
    }
}
