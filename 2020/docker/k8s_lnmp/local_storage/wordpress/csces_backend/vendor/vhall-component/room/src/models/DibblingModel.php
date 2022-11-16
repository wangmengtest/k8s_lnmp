<?php

namespace vhallComponent\room\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * DibblingModel
 *
 * @property int $id
 * @property string  $room_id    PAAS直播房间id
 * @property string  $vod_id     点播ID
 * @property string  $start_time 开始时间
 * @property bool $is_delete  是否失效 0 未失效 1 已失效
 * @property string  $created_at 创建时间
 * @property string  $updated_at 修改时间
 * @property string  $deleted_at 删除时间
 *
 * @uses     yangjin
 * @date     2020-07-15
 * @author   jin.yang@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class DibblingModel extends WebBaseModel
{
    /**
     * @var string
     */
    protected $table        = 'dibbling';

    protected $primaryKey   = 'id';

    public $incrementing = false;

    protected $attributes = [
        'room_id'    => null,
        'vod_id'     => null,
        'start_time' => '0000-00-00 00:00:00',
        'is_delete'  => 0,
        'created_at' => '0000-00-00 00:00:00',
        'updated_at' => '0000-00-00 00:00:00',
        'deleted_at' => '0000-00-00 00:00:00',
    ];

    /**
     * Notes: 获取点播任务列表
     * Author: michael
     * Date: 2019/10/12
     * Time: 15:56
     *
     * @return array
     */
    public function getList()
    {
        $model = $this->where('start_time', '>=', self::START_TIME)
            ->where('is_delete', '<', 1)
            ->select('id', 'room_id', 'vod_id', 'start_time')
            ->get()
            ->toArray();

        return $model;
    }
}
