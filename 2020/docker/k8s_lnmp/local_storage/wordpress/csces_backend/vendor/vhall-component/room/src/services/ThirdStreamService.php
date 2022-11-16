<?php

namespace vhallComponent\room\services;

use App\Constants\ResponseCode;
use Vss\Common\Services\WebBaseService;

/**
 * ThirdStreamServiceTrait
 *
 * @uses     yangjin
 * @date     2020-08-14
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class ThirdStreamService extends WebBaseService
{
    /**
     *
     * @param $params
     *
     * @return array|void
     *
     */
    public function save($params)
    {
        $detail = vss_model()->getThirdStreamModel()->create($params);
        if ($detail) {
            $pushData = [
                'sessionid' => $detail->id,
                'command'   => 'start',
                'vhallid'   => $params['room_id'],
                '3rdstream' => $params['url'],
            ];
            $result   = $this->pushstream($pushData);
            if ($result['code'] == 100) {
                vss_model()->getThirdStreamModel()->where(['id' => $detail->id])->update(['status' => 1]);
                $info = vss_model()->getThirdStreamModel()->where(['id' => $detail->id])->first();
                return $info->toArray();
            }
            vss_logger()->info(
                'push-error',
                ['url' => $params['url'], 'param' => $params, 'result' => $result]
            );
            $this->fail(ResponseCode::BUSINESS_PUSH_STREAM_FAILED);
        }
    }

    /**
     *
     * @param $params
     *
     * @return array
     */
    public function lists($params)
    {
        $query = vss_model()->getThirdStreamModel()->newQuery();

        vss_model()->getThirdStreamModel()->where(['room_id' => $params['room_id']]);
        $query->where('app_id', $params['app_id']);
        $query->where('account_id', $params['account_id']);
        $query->where('status', 1);

        $page     = !empty($params['curr_page']) ? $params['curr_page'] : 1;
        $pagesize = !empty($params['page_size']) ? $params['page_size'] : 20;

        $list = $query->selectRaw('*')->orderBy('created_at', 'asc')->paginate($pagesize, ['*'], 'page', $page);
        $list = json_decode(json_encode($list, JSON_UNESCAPED_UNICODE), true);

        return [
            'list' => $list['data'],
        ];
    }

    /**
     * @param $params
     *
     * @return bool| void
     *
     */
    public function delete($params)
    {
        $info = vss_model()->getThirdStreamModel()->where(['id' => $params['id']])->first();
        if ($info && $info->status != 1) {
            $pushData = [
                'sessionid' => $info->id,
                'command'   => 'stop',
                'vhallid'   => $info->room_id,
                '3rdstream' => $info->url,
            ];
            $this->pushstream($pushData);
            return $info->forceDelete();
        }

        $this->fail(ResponseCode::BUSINESS_PUSH_STREAMING_NOT_DELETE);
    }

    /**
     * @param $params
     *
     * @return int | void
     *
     */
    public function update($params)
    {
        $info = vss_model()->getThirdStreamModel()->where(['id' => $params['id']])->first();
        if ($info) {
            $pushData = [
                'sessionid' => $info->id,
                'command'   => 'stop',
                'vhallid'   => $info->room_id,
                '3rdstream' => $info->url,
            ];
            $result   = $this->pushstream($pushData);
            if (in_array($result['code'], [100, 101, 102])) {
                return vss_model()->getThirdStreamModel()->where(['id' => $info->id])->update(['status' => 2]);
            }
            vss_logger()->info(
                'push-stop_error',
                ['url' => $params['url'], 'param' => $params, 'result' => $result]
            );
            $this->fail(ResponseCode::BUSINESS_PUSH_STREAM_STOP_FAILED);
        }
        $this->fail(ResponseCode::BUSINESS_EDIT_FAILED);
    }

    /**
     * @param $params
     *
     * @return bool|mixed
     *
     */
    public function pushstream($params)
    {
        vss_logger()->info('pushstream', $params);
        $params['type'] = 'push';
        $params['bu']   = 1;
        $url            = vss_config('thirdStreamUrl') . '/api/3rdstream';

        //调用流媒体接口进行转播
        $client = new Client();
        $result = $client->request('post', $url, [
            'headers' => [
                'content-type' => 'application/json'
            ],
            'body'    => json_encode($params)
        ]);
        $result = json_decode($result->getBody()->getContents(), true);
        vss_logger()->info('pushresult', ['url' => $url, 'param' => $params, 'result' => $result]);
        return $result;
    }
}
