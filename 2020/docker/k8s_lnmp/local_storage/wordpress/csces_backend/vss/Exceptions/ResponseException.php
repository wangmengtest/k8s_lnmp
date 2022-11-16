<?php

namespace Vss\Exceptions;

use App\Constants\ResponseCode;
use Exception;
use Throwable;

class ResponseException extends Exception
{
    protected $data;
    protected $langKey;
    protected $replace;

    public function __construct($langKey = "", array $replace = [], Throwable $previous = null)
    {
        // 获取错误码 和 message
        $responseData = ResponseCode::getResponse($langKey, $replace);
        $message      = $responseData['msg'];
        $code         = $responseData['code'];

        $this->langKey = $langKey;
        $this->replace = $replace;

        parent::__construct($message, $code, $previous);
    }

    final public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    final public function getData()
    {
        return $this->data ?? [];
    }

    final public function getResponse($data = [])
    {
        return [
            'code'    => $this->getCode(),
            'msg'     => $this->getMessage(),
            'key'     => $this->langKey,
            'replace' => $this->replace,
            'data'    => $data,
        ];
    }
}
