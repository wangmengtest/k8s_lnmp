<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Vss\Utils\RequestIdUtil;

/**
 * 向响应结果中追加 request-id
 * Class AppendRequestId
 * @package App\Http\Middleware
 */
class AppendRequestId
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /**
         * @var Response $response
         */
        $response = $next($request);

        $data    = $response->content();
        $content = json_decode($data, true) ?? $data;
        if (is_array($content)) {
            $content['request_id'] = RequestIdUtil::get();
            $data                  = json_encode($content);
        }

        return $response->setContent($data);
    }
}
