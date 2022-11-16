<?php

namespace vhallComponent\access\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class GroupModel
 *
 * @package App\Models
 * @property int $id
 * @property string  $group_name 组名称
 * @property string  $app_id     应用id
 * @property bool $status     状态 0：正常 1：删除
 * @property string  $created_at 创建时间
 * @property string  $updated_at 更新时间
 * @property string  $deleted_at
 */
class GroupModel extends WebBaseModel
{
    protected $table         = 'group';

    protected $primaryKey    = 'id';

    protected $forceDeleting = true;

    protected $attributes = [
        'group_name' => '',
        'app_id'     => '',
        'status'     => 0,
        'created_at' => '1970-01-01 00:00:00',
        'updated_at' => '1970-01-01 00:00:00',
    ];

    public function forUpdate($id, $app_id, $data)
    {
        $stream = $this->where(['id' => $id, 'app_id' => $app_id])->first();
        if ($stream) {
            $stream->update($data);

            return true;
        }

        return false;
    }
}
