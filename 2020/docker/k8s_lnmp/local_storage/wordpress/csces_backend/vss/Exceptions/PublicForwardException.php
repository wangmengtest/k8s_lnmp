<?php

namespace Vss\Exceptions;

use App\Constants\ResponseCode;
use Throwable;

/**
 * 共享服务异常类
 * Class PublicForwardException
 * @package Vss\Exceptions
 */
class PublicForwardException extends ResponseException
{
    public function __construct($code = 202, $message = "", Throwable $previous = null)
    {
        // 保存抛出的 code 和 msg
        $this->setData([
            'code'    => $code,
            'message' => $message
        ]);

        parent::__construct(ResponseCode::BUSINESS_PUBLIC_FORWARD_SERVICE_FAILED, [], $previous);
    }
}
