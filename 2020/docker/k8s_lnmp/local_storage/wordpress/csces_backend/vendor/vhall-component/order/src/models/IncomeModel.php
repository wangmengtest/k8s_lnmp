<?php


namespace vhallComponent\order\models;

use vhallComponent\decouple\models\WebBaseModel;

class IncomeModel extends WebBaseModel
{
    protected $table = 'income';

    protected $primaryKey = 'id';

    protected $attributes = [
        'account_id' => '',
        'app_id' => '',
        'total' => '0.00',
        'balance' => '0.00',
        'created_at' => '1970-01-01 00:00:00',
        'updated_at' => '1970-01-01 00:00:00'
    ];
}
