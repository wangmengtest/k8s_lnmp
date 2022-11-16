<?php

namespace vhallComponent\roomlike\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * RoomLikeModel
 *
 * @property int $id
 * @property string  $room_id    房间id
 * @property int $account_id 用户id
 * @property string  $created_at
 * @property string  $updated_at
 * @property string  $deleted_at
 *
 * @uses     yangjin
 * @date     2020-07-15
 * @author   jin.yang@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class RoomLikeModel extends WebBaseModel
{
    protected $table      = 'room_likes';

    protected $primaryKey = 'id';

    protected $attributes = [
        'room_id'    => '',
        'account_id' => 0,
        'created_at' => '1970-01-01 00:00:00',
        'updated_at' => '1970-01-01 00:00:00'
    ];
}
