<?php

namespace vhallComponent\room\models;

use vhallComponent\decouple\models\WebBaseModel;

class ThirdStreamModel extends WebBaseModel
{
    protected $table      = 'third_stream';

    protected $primaryKey = 'id';

    protected $attributes = [
        'url'        => '',
        'status'     => 0,
        'created_at' => '1970-01-01 00:00:00',
        'updated_at' => '1970-01-01 00:00:00',
        'account_id' => 0,
        'app_id'     => '',
        'room_id'    => '',
    ];
}
