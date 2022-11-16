<?php

namespace App\Http\Middleware;

use App\Constants\ResponseCode;
use Closure;
use Illuminate\Http\Request;
use Vss\Exceptions\ValidationException;

/**
 * API 接口签名检查
 * Class VerifyApiSign
 * @package App\Http\Middleware
 */
class VerifyApiSign
{

    // 必须检查的路径
    protected $mustMatchPath = [
        '/api/auth/third-login',
        //'/api/auth/auto-login',
    ];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->isMatch($request)) {
            $this->verifySign($request);
        }

        return $next($request);
    }

    protected function isMatch(Request $request): bool
    {
        $from = $request->get('from');

        return !in_array($from,['js','ios','android']) || is_matching_path($request, $this->mustMatchPath);
    }

    protected function verifySign(Request $request)
    {
        $params = $request->except(['vss_account_info', 'vss_admin']);
        array_walk($params, function ($value, $key) use (&$params) {
            if (strpos($key, '/') !== false) {
                unset($params[$key]);
            }
        });

        $sign = vss_paas_util()->sign($params, vss_config('paas.apps.lite.appSecret'));
        if ($sign != $params['sign']) {
            throw new ValidationException(ResponseCode::AUTH_LOGIN_SIGN);
        }

        if (!isset($params['signed_at']) || (time() - $params['signed_at']) > 60) {
            throw new ValidationException(ResponseCode::AUTH_LOGIN_SIGN_EXPIRE);
        }
    }
}
