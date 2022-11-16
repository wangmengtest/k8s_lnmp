<?php

namespace Vss\Exceptions;

use App\Constants\ResponseCode;
use Throwable;

/**
 * Pass 接口异常抛出
 * Class PaasException
 * @package Vss\Exceptions
 */
class PaasException extends ResponseException
{
    public function __construct($code = 200, $message = "", Throwable $previous = null)
    {
        // 保存 paas 服务抛出的 code 和 msg
        $this->setData([
            'code'    => $code,
            'message' => $message
        ]);

        parent::__construct(ResponseCode::BUSINESS_PAAS_SERVICE_FAILED, [], $previous);
    }
}
