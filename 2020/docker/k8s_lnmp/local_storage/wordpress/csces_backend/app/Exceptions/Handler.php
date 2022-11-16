<?php

namespace App\Exceptions;

use App\Constants\ResponseCode;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Throwable;
use Vss\Exceptions\CallbackException;
use Vss\Exceptions\JsonResponseException;
use Vss\Exceptions\PaasException;
use Vss\Exceptions\PublicForwardException;
use Vss\Exceptions\ResponseException;
use Vss\Exceptions\ValidationException;
use Illuminate\Validation\ValidationException as LaravelValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     * 以下异常信息不会被 laravel 的默认日志记录
     * @var array
     */
    protected $dontReport = [
        JsonResponseException::class,
        CallbackException::class,
        PaasException::class,
        PublicForwardException::class,
        ValidationException::class,
        LaravelValidationException::class
    ];

    protected $debug = false;

    public function __construct(Container $container)
    {
        parent::__construct($container);

        $this->debug = config('app.debug', true);
    }

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $registerExceptionRenders = [
            [$this, 'paasExceptionRender'],
            [$this, 'publicForwardExceptionRender'],
            [$this, 'callbackExceptionRender'],
            [$this, 'responseExceptionRender'],
            [$this, 'laravelValidationExceptionRender'],
            [$this, 'phpExceptionRender'],
        ];

        foreach ($registerExceptionRenders as $exceptionRender) {
            $this->renderable($exceptionRender);
        }
    }

    /**
     *  自定义响应异常
     * @auther yaming.feng@vhall.com
     * @date 2021/6/1
     *
     * @param ResponseException $e
     *
     * @return JsonResponse
     */
    protected function responseExceptionRender(ResponseException $e): JsonResponse
    {
        $resp = $e->getResponse(trim_page_list($e->getData()));

        // 检查是否是 jsonp 请求
        $cb = request()->get('callback') ?: request()->get('call_back');
        if ($cb) {
            return response()->jsonp($cb, $resp);
        }
        return response()->json($resp);
    }

    /**
     * paas 服务异常的响应
     * @auther yaming.feng@vhall.com
     * @date 2021/6/1
     *
     * @param PaasException $e
     *
     * @return JsonResponse
     */
    protected function paasExceptionRender(PaasException $e): JsonResponse
    {
        $resp = $e->getResponse();

        if ($this->debug) {
            $resp['error'] = $e->getData();
        } else {
            vss_logger()->error('[pass_error]', $e->getData());
        }
        return response()->json($resp);
    }

    /**
     *  共享服务服务异常的响应
     * @auther yaming.feng@vhall.com
     * @date 2021/6/1
     *
     * @param PublicForwardException $e
     *
     * @return JsonResponse
     */
    protected function publicForwardExceptionRender(PublicForwardException $e): JsonResponse
    {
        $resp = $e->getResponse();

        if ($this->debug) {
            $resp['error'] = $e->getData();
        } else {
            vss_logger()->error('[public_forward]', $e->getData());
        }
        return response()->json($resp);
    }

    /**
     * 回调异常，需要返回文本 success or fail
     * @auther yaming.feng@vhall.com
     * @date 2021/6/1
     *
     * @param CallbackException $e
     *
     * @return Application|ResponseFactory|Response
     */
    protected function callbackExceptionRender(CallbackException $e)
    {
        if ($e->getCode() == 200) {
            vss_logger()->info('[callback]: ' . $e->getMessage(), $this->logContext());
            return response("success");
        }

        vss_logger()->error("[callback]: " . $e->getMessage(), $this->logContext());
        return response('fail');
    }

    /**
     * 参数校验异常渲染
     *
     * @param LaravelValidationException $e
     *
     * @return JsonResponse
     * @since  2021/6/22
     * @author fym
     */
    protected function laravelValidationExceptionRender(LaravelValidationException $e): JsonResponse
    {
        // 获取错误信息
        $errors = $e->validator->errors();
        // 获取第一个参数字段
        $key = $errors->keys()[0];
        // 获取错误提示
        $msg = $errors->first($key);

        // 参数字段转单词, laravel 低层会自动将参数字段转为单词
        $word = str_replace('_', ' ', Str::snake($key));
        // 将提示信息中的单词替换成参数字段
        $msg = str_replace($word, $key, $msg);

        // 构造响应信息
        $resp = [
            'code' => ResponseCode::getResponseCode(ResponseCode::BUSINESS_INVALID_PARAM),
            'key'  => ResponseCode::BUSINESS_INVALID_PARAM,
            'msg'  => $msg,
            'data' => []
        ];

        return response()->json($resp);
    }

    /**
     * PHP 异常响应，兜底异常处理
     * @auther yaming.feng@vhall.com
     * @date 2021/6/1
     *
     * @param Throwable $e
     *
     * @return JsonResponse
     */
    protected function phpExceptionRender(Throwable $e): JsonResponse
    {
        $resp         = ResponseCode::getResponse(ResponseCode::FAILED);
        $resp['data'] = [];

        if ($this->debug) {
            $resp['error'] = [
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
                'trace'   => $e->getTrace()
            ];
        } else {
            vss_logger()->error('[php_exception]: ' . $e->getMessage(), $this->logContext());
        }
        return response()->json($resp);
    }

    /**
     * 日志
     * @auther yaming.feng@vhall.com
     * @date 2021/6/1
     * @return array
     */
    protected function logContext(): array
    {
        return [
            'method' => request()->method(),
            'get'    => request()->query(),
            'post'   => request()->post(),
            'uri'    => request()->getRequestUri()
        ];
    }
}
