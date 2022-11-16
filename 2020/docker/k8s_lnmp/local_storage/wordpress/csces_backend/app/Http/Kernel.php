<?php

namespace App\Http;

use App\Http\Middleware\AccessLog;
use App\Http\Middleware\AppendRequestId;
use App\Http\Middleware\VerifyAdminAuth;
use App\Http\Middleware\FVerifyApiSign;
use App\Http\Middleware\VerifyApiSign;
use App\Http\Middleware\VerifyCallbackSign;
use App\Http\Middleware\VerifySignOrVssToken;
use App\Http\Middleware\VerifyToken;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        //TrustProxies::class,
        //HandleCors::class,
        //PreventRequestsDuringMaintenance::class,
        //ValidatePostSize::class,
        //TrimStrings::class,
        //ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            //\App\Http\Middleware\EncryptCookies::class,
            //\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            //StartSession::class,
            //// \Illuminate\Session\Middleware\AuthenticateSession::class,
            //\Illuminate\View\Middleware\ShareErrorsFromSession::class,
            ////\App\Http\Middleware\VerifyCsrfToken::class,
            //\Illuminate\Routing\Middleware\SubstituteBindings::class,

            AccessLog::class,
            AppendRequestId::class,
        ],

        'api' => [
            //'throttle:api',
            //\Illuminate\Routing\Middleware\SubstituteBindings::class,
            VerifyApiSign::class,
            VerifyToken::class,
            VerifySignOrVssToken::class, // 主要是对 live-goods 组件使用, TODO 需要规范改组件
        ],

        'v2'       => [
            VerifySignOrVssToken::class,
        ],
        'console'  => [
            VerifyToken::class
        ],
        'admin'    => [
            VerifyAdminAuth::class
        ],
        'callback' => [
            VerifyCallbackSign::class
        ]
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        //'auth'             => Authenticate::class,
        //'auth.basic'       => AuthenticateWithBasicAuth::class,
        //'cache.headers'    => SetCacheHeaders::class,
        //'can'              => Authorize::class,
        //'guest'            => RedirectIfAuthenticated::class,
        //'password.confirm' => RequirePassword::class,
        //'signed'           => ValidateSignature::class,
        //'throttle'         => ThrottleRequests::class,
        //'verified'         => EnsureEmailIsVerified::class,
    ];
}
