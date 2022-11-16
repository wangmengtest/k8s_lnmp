<?php
/**
 * Created by PhpStorm.
 * User: zhangxz
 * Date: 2018/8/7
 * Time: 下午1:49
 */

namespace Vss\Utils\Dto;

/**
 * httpUtils返回对象
 *
 * Class GuzzleResponse
 * @package core\utils\dto
 */
class GuzzleResponse
{

    // 业务成功状态吗
    const SUCCESSS_CODE = 200;

    /**
     * @var bool 请求是否成功
     */
    public $success;

    /**
     * @var int http返回代码
     */
    public $code;

    /**
     * @var mixed 返回数据
     */
    public $data;

    /**
     * @var string 错误信息
     */
    public $message;

    public $headers;

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @param bool $success
     */
    public function setSuccess(bool $success)
    {
        $this->success = $success;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode(int $code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param mixed $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function getHeader($key)
    {
        return $this->getHeaders()[$key] ?? null;
    }
}
