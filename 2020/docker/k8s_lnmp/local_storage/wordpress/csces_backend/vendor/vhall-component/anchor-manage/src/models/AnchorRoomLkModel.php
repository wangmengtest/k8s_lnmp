<?php

namespace vhallComponent\anchorManage\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class AnchorManageModel
 * @property integer $id
 * @property integer $anchor_id       主播id
 * @property integer $il_id           房间id
 * @property string  $created_at
 * @property string  $updated_at
 * @property string  $deleted_at
 */
class AnchorRoomLkModel extends WebBaseModel
{
    protected $table      = "anchor_room_lk";
    protected $primaryKey = 'id';

    protected $attributes = [
        'id'              => 0,
        'anchor_id'       => 0,
        'il_id'           => 0,
        'created_at'      => '1970-01-01 00:00:00',
        'updated_at'      => '1970-01-01 00:00:00',
        'deleted_at'      => null,
    ];

}
