<?php

namespace vhallComponent\access\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class AccessOpLogModel
 *
 * @property int $id
 * @property int $operator   操作者
 * @property string  $content    操作内容
 * @property bool $type       操作行为类型:   1:组 2:角色 3:权限
 * @property string  $created_at 操作时间
 * @property string  $updated_at
 * @property string  $deleted_at
 * @package App\Models
 */
class AccessOpLogModel extends WebBaseModel
{
    protected $table      = 'access_op_log';

    protected $primaryKey = 'id';

    protected $attributes = [
        'operator'   => '',
        'content'    => '',
        'type'       => '',
        'created_at' => '1970-01-01 00:00:00',
        'updated_at' => '1970-01-01 00:00:00',
    ];
}
