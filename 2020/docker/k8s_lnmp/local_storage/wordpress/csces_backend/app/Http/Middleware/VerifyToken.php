<?php

namespace App\Http\Middleware;

use App\Component\account\src\constants\AccountConstant;
use App\Constants\ResponseCode;
use Closure;
use Illuminate\Http\Request;
use Vss\Exceptions\ValidationException;

/**
 * 登录 Token 校验
 * Class VerifyToken
 * @package App\Http\Middleware
 */
class VerifyToken
{
    // 不做校验的路径
    protected $except = [
        '/*/auth/auto-login',
        '/*/auth/login',
        '/*/auth/logout',
        '/*/auth/login-watch',
        '/*/auth/visitor',
        '/*/code/send',
        '/console/verify/login',
        '/console/verify/watchlogin',
        '/console*/upload/images',
        '/console/watchlimit/limit',
        '/console/watchlimit/get',
        '/console/watchlimit/regapply',
        '/console/watchlimit/whitelogin',
        '/console/diypage/customTag',
        '/console/question/submitAnswer',

        '/api/auth/third-login',
        '/api/auth/test-create',
        '/api/v1/Inav/get',
        '/api/inav/get-anchor-ren',
        '/api/inav/custom-tag',
        '/api/pay/notify',
        '/api/anchor-manage/sendverifycode',
        '/api/anchor-manage/login',
        '/console/room/check-room-password'
    ];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     * @throws ValidationException
     */
    public function handle(Request $request, Closure $next)
    {
        $this->verifyToken($request);
        return $next($request);
    }

    protected function isMatch(Request $request): bool
    {
        return !is_matching_path($request, $this->except);
    }

    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/6/11
     * @param Request $request
     *
     * @throws ValidationException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function verifyToken(Request $request)
    {
        $token       = $request->get('token');
        if(empty($token) && $this->isMatch($request)){
            throw new ValidationException(ResponseCode::AUTH_LOGIN_TOKEN_EXPIRE);
        }

        $accountInfo = $token ? json_decode(vss_redis()->get($token), true) : null;
        if (!$accountInfo && $this->isMatch($request)) {
            throw new ValidationException(ResponseCode::AUTH_LOGIN_ALREADY);
        }

        if($accountInfo && $accountInfo['status'] == AccountConstant::STATUS_DISABLED) {
            throw new ValidationException(ResponseCode::AUTH_LOGIN_ACCOUNT_DISABLE_VERIFYTOKEN);
        }

        if (!$accountInfo) {
            return;
        }

        $appId     = vss_service()->getTokenService()->getAppId();
        $accountId = $accountInfo['account_id'];

        $request->offsetSet('app_id', $appId);
        $request->offsetSet('account_id', $accountId);
        $request->offsetSet('vss_account_info', $accountInfo);
    }
}
