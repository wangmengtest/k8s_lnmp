<?php

namespace vhallComponent\anchorManage\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class AnchorManageModel
 * @property int    $account_id 账户id
 * @property string $nickname   昵称
 * @property string $real_name  姓名
 * @property string $phone      电话
 * @property string $avatar     头像
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class AnchorManageModel extends WebBaseModel
{

    protected $table      = "anchor_manage";
    protected $primaryKey = 'anchor_id';

    protected $attributes = [
        'account_id'     => 0,
        'nickname'       => '',
        'real_name'      => '',
        'phone'          => '',
        'avatar'         => '',
        'created_at'     => '1970-01-01 00:00:00',
        'updated_at'     => '1970-01-01 00:00:00',
        'deleted_at'     => null,
    ];

}
