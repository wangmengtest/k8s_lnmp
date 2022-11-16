<?php

namespace vhallComponent\scrolling\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * ScrollingModel
 *
 * @property int $id
 * @property string  $room_id        直播房间id
 * @property bool $scrolling_open 开启状态 0:关闭 1:开启
 * @property string  $text           文本内容
 * @property bool $text_type      文本类型 1：固定文本  2:固定文本+观看者id昵称
 * @property int $alpha          文本不透明度 百分比
 * @property int $size           文字大小
 * @property string  $color          文字颜色
 * @property int $interval       显示间隔时间   时长/秒
 * @property int $speed          文字移动速度:  10000: 慢,  6000:中,  3000:快
 * @property bool $status         是否开启 : 0:关闭 1:开启
 * @property bool $position       位置 1:随机 2:高 3:中 4:低
 * @property string  $created_at     创建时间
 * @property string  $updated_at     更新时间
 * @property string  $deleted_at
 *
 * @uses     yangjin
 * @date     2020-07-09
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class ScrollingModel extends WebBaseModel
{
    protected $table      = 'scrolling';

    protected $primaryKey = 'id';

    protected $attributes = [
        'room_id'        => '',
        'scrolling_open' => 0,
        'text'           => '',
        'text_type'      => 1,
        'alpha'          => 100,
        'size'           => 0,
        'color'          => 0,
        'interval'       => 20,
        'speed'          => 6000,
        'position'       => 1,
        'status'         => 0,
        'created_at'     => '1970-01-01 00:00:00',
        'updated_at'     => '1970-01-01 00:00:00',
    ];

    public function getOne($condition)
    {
        return $this->where($condition)->first();
    }

    public function updateByCondition($condition, $data)
    {
        return $this->where($condition)->update($data);
    }
}
