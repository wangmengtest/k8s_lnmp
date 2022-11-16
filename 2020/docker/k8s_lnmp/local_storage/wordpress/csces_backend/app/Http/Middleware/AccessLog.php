<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

/**
 * 记录访问日志
 * Class AccessLog
 * @package App\Http\Middleware
 */
class AccessLog
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        vss_logger()->info("access_log:request: ", [
            'uri'    => $request->getRequestUri(),
            'method' => $request->method(),
            'get'    => $request->query(),
            'post'   => $request->post(),
        ]);

        $response = $next($request);

        // 不记录返回内容，只记录返回状态即可，防止返回内容过多，写日志耗费时间
        $content = $response->content();
        $content = json_decode($content, true) ?? $content;
        if (is_array($content)) {
            $content = Arr::only($content, ['code', 'msg', 'request_id']);
        }

        vss_logger()->info("access_log:response: ", [
            'uri'        => $request->getRequestUri(),
            'result'     => $content,
            'cost time:' => (microtime(true) - $startTime)
        ]);

        return $response;
    }
}
