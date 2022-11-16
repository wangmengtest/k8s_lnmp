<?php
/**
 * Created by PhpStorm.
 * Date: 2020/1/16
 * Time: 21:12
 */

namespace vhallComponent\order\services;

use Vss\Common\Services\WebBaseService;

class OrderService extends WebBaseService
{
    /**
     * order
     * @param $params
     * @return array
     */
    public function lists($params)
    {
        $query = vss_model()->getOrderDetailModel()->newQuery();

        $status = !empty($params['status']) ? $params['status'] : 0;

        $query->where('status', $status);
        $query->where('app_id', $params['app_id']);
        if (!empty($params['account_id'])) {
            $query->where('account_id', $params['account_id']);
        }

        $page = !empty($params['curr_page']) ? $params['curr_page'] : 1;
        $pagesize = !empty($params['page_size']) ? $params['page_size'] : 20;

        if (!empty($params['start_time'])) {
            $query->where('created_at', '>=', "{$params['start_time']}");
        }

        if (!empty($params['end_time'])) {
            $query->where('created_at', '<=', "{$params['end_time']} 23:59:59");
        }

        $list = $query->selectRaw('*')->orderBy('created_at', 'desc')->paginate($pagesize, ['*'], 'page', $page);
        $list = json_decode(json_encode($list, JSON_UNESCAPED_UNICODE), true);

        return [
            'list' => $list['data'],
            'total' => $list['total'],
            'total_page' => $list['last_page'],
            'curr_page' => $list['current_page'],
        ];
    }
}
