<?php

namespace vhallComponent\config\services;

use App\Constants\ResponseCode;
use Vss\Common\Services\WebBaseService;
use vhallComponent\config\constants\ConfigInfoConstant;
use vhallComponent\config\models\ConfigInfoModel;
use vhallComponent\room\models\RoomsModel;

/**
 * ConfigInfoService
 *
 * @uses     yangjin
 * @date     2020-08-25
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class ConfigInfoService extends WebBaseService
{
    /**
     * 初始化房间多级分类
     *
     * @param $value
     *
     * @return int|ConfigInfoModel
     *
     * @author  jin.yang@vhall.com
     * @date    2020-08-25
     */
    public function initRoomCategory($value)
    {
        $model = vss_model()->getConfigInfoModel()->getRow(['key' => ConfigInfoConstant::ROOM_CATEGORY]);
        if (!empty($model)) {
            $this->fail(ResponseCode::EMPTY_CONFIG);
        }

        return $this->setConfig(ConfigInfoConstant::ROOM_CATEGORY, $value);
    }

    /**
     * 获取房间多级分类
     *
     * @return mixed
     * @author  jin.yang@vhall.com
     * @date    2020-08-25
     */
    public function getRoomCategory()
    {
        $value = vss_model()->getConfigInfoModel()->getValue(ConfigInfoConstant::ROOM_CATEGORY);
        return json_decode($value, true);
    }

    /**
     * 初始化房间自定义字段
     *
     * @param $value
     *
     * @return int|ConfigInfoModel
     *
     * @author  jin.yang@vhall.com
     * @date    2020-08-25
     */
    public function initRoomColumn($value)
    {
        $model = vss_model()->getConfigInfoModel()->getRow(['key' => ConfigInfoConstant::ROOM_COLUMN]);
        if (!empty($model)) {
            $this->fail(ResponseCode::EMPTY_CONFIG);
        }

        return $this->setConfig(ConfigInfoConstant::ROOM_COLUMN, $value);
    }

    /**
     * 获取房间字段
     *
     * @return mixed
     *         [{"column" : "cl", "name" : "xx信息", "type" : "string" , "default" : ""}]
     * @author  jin.yang@vhall.com
     * @date    2020-08-25
     */
    public function getRoomColumn()
    {
        $value = vss_model()->getConfigInfoModel()->getValue(ConfigInfoConstant::ROOM_COLUMN);
        return json_decode($value, true);
    }

    /**
     * 设置配置信息
     *
     * @param     $key
     * @param     $value
     * @param int $id
     *
     * @return int|ConfigInfoModel
     * @author  jin.yang@vhall.com
     * @date    2020-08-25
     */
    public function setConfig($key, $value, $id = 0)
    {
        if (empty($id)) {
            return vss_model()->getConfigInfoModel()->create(['key' => $key, 'value' => $value]);
        }

        return vss_model()->getConfigInfoModel()->setConfig($id, $key, $value);
    }

    /**
     * 获取房间扩展字段
     *
     * @return mixed
     * @author  jin.yang@vhall.com
     * @date    2020-08-27
     */
    public function getRoomExtendColumn()
    {
        $json    = $this->getRoomColumn();
        $jsonArr = json_decode($json, true);

        return array_diff(
            array_column($jsonArr, 'column'),
            array_keys(RoomsModel::getInstance()->getAttributes())
        );
    }
}
