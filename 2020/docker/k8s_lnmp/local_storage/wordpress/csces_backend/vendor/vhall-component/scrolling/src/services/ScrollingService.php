<?php

namespace vhallComponent\scrolling\services;

use App\Constants\ResponseCode;
use Illuminate\Support\Arr;
use Vss\Common\Services\WebBaseService;
use vhallComponent\scrolling\models\ScrollingModel;

/**
 * ScrollingServiceTrait
 *
 * @uses     yangjin
 * @date     2020-07-09
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class ScrollingService extends WebBaseService
{
    /**
     * 创建/修改跑马灯
     *
     * @param $params
     *
     * @return mixed|ScrollingModel
     *
     */
    public function save($params)
    {
        $rule = [
            'room_id'        => 'required',
            'scrolling_open' => '',
            'text'           => '',
            'text_type'      => '',
            'alpha'          => '',
            'size'           => '',
            'color'          => '',
            'interval'       => '',
            'speed'          => '',
            'position'       => '',
            'status'         => '',
            'type'           => '',
        ];
        $data = vss_validator($params, $rule);
        unset($data['type']);

        $condition = ['room_id' => $params['room_id']];
        /** @var ScrollingModel $info */
        $info = vss_model()->getScrollingModel()->getOne($condition);
        if ($info && $info->id > 0) {
            if (isset($params['type']) && $params['type'] == 1) {
                $saveData = ['status' => 0];
            } else {
                $saveData = $data;
            }
            $result = vss_model()->getScrollingModel()->updateByCondition(['id' => $info->id], $saveData);
            if ($result) {
                $info = vss_model()->getScrollingModel()->getOne($condition);
                return $info;
            }
            $this->fail(ResponseCode::BUSINESS_CREATE_FAILED);
        }
        return vss_model()->getScrollingModel()->create($data);
    }

    /**
     * 详情
     *
     * @param $params
     *
     * @return array|mixed
     *
     */
    public function info($params)
    {
        vss_validator($params, [
            'room_id' => 'required',
        ]);

        /** @var ScrollingModel $info */
        $info = vss_model()->getScrollingModel()->getOne(['room_id' => $params['room_id']]);
        if ($info && $info->id > 0) {
            return $info->toArray();
        }
        return false;
    }
}
