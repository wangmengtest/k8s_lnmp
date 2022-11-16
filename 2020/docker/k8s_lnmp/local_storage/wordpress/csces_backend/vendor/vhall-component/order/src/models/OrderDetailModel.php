<?php
/**
 * Date: 2020/1/16
 * Time: 20:44
 */

namespace vhallComponent\order\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * @package App\Models
 */

class OrderDetailModel extends WebBaseModel
{
    protected $table = 'order_detail';

    protected $primaryKey = 'id';

    protected $attributes = [
        'room_id' => '',
        'app_id' => '',
        'account_id' => 0,
        'amount' => '0.00',
        'status' => '0',
        'source' => '0',
        'trade_no' => '',
        'channel' => '0',
        'created_at' => '1970-01-01 00:00:00',
        'updated_at' => '1970-01-01 00:00:00'
    ];
}
