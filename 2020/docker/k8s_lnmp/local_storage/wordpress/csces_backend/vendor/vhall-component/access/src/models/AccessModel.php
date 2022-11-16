<?php

namespace vhallComponent\access\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class AccessModel
 *
 * @property int $id
 * @property string  $title      权限名称
 * @property int $rule       权限码
 * @property string  $url        路径
 * @property bool $status     状态 0:正常 1:禁止
 * @property bool $type       类型 0：显示 1：操作
 * @property string  $created_at 创建时间
 * @property string  $updated_at 更新时间
 * @property string  $deleted_at
 *
 * @package App\Models
 */
class AccessModel extends WebBaseModel
{
    protected $table      = 'access';

    protected $primaryKey = 'id';

    protected $attributes = [
        'title'      => '',
        'rule'       => '',
        'status'     => 0,
        'type'       => 0,
        'created_at' => '1970-01-01 00:00:00',
        'updated_at' => '1970-01-01 00:00:00',
    ];
}
