<?php
/**
 * Created by PhpStorm.
 * User: liuxiangliang
 * Date: 2020/11/20
 * Time: 15:55
 */

namespace vhallComponent\diypage\services;

use App\Constants\ResponseCode;
use Vss\Common\Services\WebBaseService;

class DiypageService extends WebBaseService
{
    /**
     * 保存自定义标签
     *
     * @param $ilId
     * @param $customTag
     *
     * @return mixed
     *
     * @author  jin.yang@vhall.com
     * @date    2020-08-13
     */
    public function updateCustomTag($ilId, $customTag, $accountId)
    {
        $room = vss_model()->getRoomsModel()->getRow(['il_id' => $ilId, 'account_id' => $accountId]);
        if (empty($room)) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }

        $data = [
            'custom_tag' => $customTag,
        ];
        $res  = vss_model()->getRoomSupplyModel()->saveByIlId($ilId, $data);
        if (!$res) {
            $this->fail(ResponseCode::BUSINESS_CREATE_FAILED);
        }
        $custom_tag = $res['custom_tag'] ?? '';
        $info       = ['il_id' => $ilId, 'custom_tag' => $custom_tag];
        return $info;
    }

    /**
     * 获取房间自定义标签
     *
     * @param $roomId
     *
     * @return mixed
     *
     * @author  jin.yang@vhall.com
     * @date    2020-08-13
     */
    public function getCustomTag($ilId)
    {
        $model      = vss_model()->getRoomSupplyModel()->getInfoByIlId($ilId);
        $custom_tag = $model->custom_tag ?? '';
        return ['il_id' => $ilId, 'custom_tag' => $custom_tag];
    }
}
