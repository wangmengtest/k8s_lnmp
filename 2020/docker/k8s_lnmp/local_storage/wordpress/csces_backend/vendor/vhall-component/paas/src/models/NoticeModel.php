<?php


namespace vhallComponent\paas\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * @package App\Models
 */

class NoticeModel extends WebBaseModel
{
    protected $table = 'notices';

    protected $primaryKey = 'id';

    protected $attributes = [
        'room_id' => '',
        'app_id' => '',
        'account_id' => 0,
        'content' => '',
        'created_at' => '1970-01-01 00:00:00',
        'updated_at' => '1970-01-01 00:00:00',
        'type' => 0,
        'red_packet_uuid' => '',
    ];
}
