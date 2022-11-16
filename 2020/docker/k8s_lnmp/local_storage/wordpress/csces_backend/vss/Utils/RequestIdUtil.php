<?php

namespace Vss\Utils;

class RequestIdUtil
{
    protected static $traceId;

    public static function get()
    {
        if (property_exists(vss_request(), 'requestId')) {
            return vss_request()->requestId;
        }

        $requestId = vss_request()->header('request-id');
        if (!$requestId) {
            $requestId = md5(uniqid(mt_rand(), true));
        }
        return vss_request()->requestId = $requestId;
    }

    public static function getTraceId(): string
    {
        if (property_exists(vss_request(), 'traceId')) {
            return vss_request()->traceId;
        }

        $traceIdSw8 = vss_request()->header('HTTP_SW8');
        if ($traceIdSw8) {
            $traceIdSw8 = explode('-', $traceIdSw8);
            isset($traceIdSw8[1]) && vss_request()->traceId = base64_decode($traceIdSw8[1]);
        }
        return vss_request()->traceId ?? '';
    }
}
