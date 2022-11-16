<?php


namespace vhallComponent\invitecard\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * @package App\Models
 */

class InviteCardModel extends WebBaseModel
{
    protected $table = 'invite_cards';

    protected $primaryKey = 'id';

    protected $attributes = [
        'room_id' => '',
        'title' => '',
        'date' => '',
        'company' => '',
        'desciption' => '',
        'location' => '',
        'welcome_txt' => '',
        'img' => '',
        'show_type' => 1,
        'img_type' => 1,
        'is_show_watermark' => 0,
        'created_at' => '1970-01-01 00:00:00',
        'updated_at' => '1970-01-01 00:00:00'
    ];
}
