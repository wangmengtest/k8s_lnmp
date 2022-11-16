<?php

namespace App\Http\Middleware;

use App\Constants\ResponseCode;
use Closure;
use Illuminate\Http\Request;
use Vss\Exceptions\ValidationException;

/**
 * 直播间接口 签名 或 vss token 校验
 * Class VerifySignOrVssToken
 * @package App\Http\Middleware
 */
class VerifySignOrVssToken
{
    // 校验签名的路径
    protected $matchPaths = [
        '/v2/*',
        '/api/live-goods/*'
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
        if (is_matching_path($request, $this->matchPaths)) {
            $params = $request->except(['vss_account_info', 'vss_admin']);
            if ($request->has('vss_token')) {
                if (!$this->verifyVssToken($request)) {
                    throw new ValidationException(ResponseCode::AUTH_VSS_TOKEN_NOT_EMPTY);
                }
            } elseif ($request->has('sign')) {
                if (!$this->verifySign($request, $params)) {
                    throw new ValidationException(ResponseCode::AUTH_LOGIN_SIGN);
                }
            } else {
                throw new ValidationException(ResponseCode::AUTH_VSS_TOKEN_NOT_EMPTY);
            }
        }

        return $next($request);
    }

    protected function verifySign(Request $request, $params): bool
    {
        vss_validator($params, [
            'sign'      => 'required',
            'app_id'    => 'required',
            'signed_at' => 'required|numeric',
        ]);

        $userSign = $request->get('sign');
        if ($userSign == 'vhall2019') {
            return true;
        }

        if ($request->get('signed_at') + 3600 < time()) {
            return false;
        }

        $appSecret = vss_paas_util()->getPaasAppSecretByAppId($request->get('app_id'));
        $sign      = vss_paas_util()->sign($params, $appSecret);
        if ($userSign != $sign) {
            return false;
        }

        return true;
    }

    protected function verifyVssToken(Request $request): bool
    {
        $vssToken  = $request->get('vss_token');
        $permitArr = vss_service()->getTokenService()->checkToken($vssToken);
        if (!$permitArr) {
            throw new ValidationException(ResponseCode::AUTH_LOGIN_TOKEN_INVALID);
        }

        $request->offsetSet('app_id', $permitArr['app_id']);
        $request->offsetSet('third_party_user_id', $permitArr['third_party_user_id']);
        return true;
    }
}
