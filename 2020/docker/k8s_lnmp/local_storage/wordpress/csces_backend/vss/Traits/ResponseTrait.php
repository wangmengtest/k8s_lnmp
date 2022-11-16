<?php

namespace Vss\Traits;

use App\Constants\ResponseCode;
use Illuminate\Contracts\Pagination\Paginator;
use Vss\Exceptions\JsonResponseException;

trait ResponseTrait
{
    /**
     * ajax请求成功成功 统一返回
     *
     * @param array  $data      返回值
     * @param array  $rspStruct 返回值数据结构
     * @param string $code      响应码描述符
     *
     * @throws JsonResponseException
     */
    public function success($data = [], $rspStruct = [], string $code = ResponseCode::SUCCESS)
    {
        if ($data instanceof Paginator) {
            $data = [
                'current_page' => $data->currentPage(),
                'data'         => $data->items(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
            ];
        }
        if ($rspStruct && is_array($rspStruct)) {
            $data = vss_data_struct($data, $rspStruct);
        }

        $this->jsonResponse($code, $data);
    }

    /**
     * ajax请求失败 统一返回
     *
     * @param string $code      状态码
     * @param array  $msgParams 提示信息中的变量设置
     *                          eg:
     *                          msg: 请 :minutes 分钟后重试
     *                          msgParams: ['minutes' => 5]
     *                          会自动使用参数中的 minutes 替换 msg 中的同名变量
     *
     * @throws JsonResponseException
     */
    public function fail(string $code = ResponseCode::FAILED, $msgParams = [])
    {
        $this->jsonResponse($code, [], $msgParams);
    }

    /**
     * @param string $code
     * @param array  $data
     * @param array  $msgParams
     *
     * @throws JsonResponseException
     * @author  jin.yang@vhall.com
     * @date    2021-05-20
     */
    public function jsonResponse(string $code, $data = [], $msgParams = [])
    {
        throw (new JsonResponseException($code, $msgParams))->setData($data);
    }
}
