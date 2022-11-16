<?php

namespace vhallComponent\access\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class RoleAccessModel
 *
 * @package App\Models
 * @property int $id
 * @property int $role_id    角色id
 * @property int $access_id  权限id
 * @property bool $status     状态 0：开启 1：关闭
 * @property string  $created_at 创建时间
 * @property string  $updated_at 更新时间
 * @property string  $deleted_at
 */
class RoleAccessModel extends WebBaseModel
{
    protected $table      = 'role_access';

    protected $primaryKey = 'id';

    protected $attributes = [
        'role_id'    => '',
        'access_id'  => '',
        'status'     => 0,
        'created_at' => '1970-01-01 00:00:00',
        'updated_at' => '1970-01-01 00:00:00'
    ];

    public function batchCreate($role_id, $data)
    {
        if (is_array($data) && !empty($data)) {
            $act['role_id'] = $role_id;
            foreach ($data as $v) {
                $act['access_id'] = $v;
                $result           = $this->where(['role_id' => $role_id, 'access_id' => $v])->first();
                if ($result->status == 1) {
                    $this->where(['id' => $result->id])->update(['status' => 0]);
                } elseif ($result->status == 0) {
                    continue;
                } else {
                    if (!$this->create($act)->toArray()) {
                        vss_logger()->info('accessrole_create_error', $act);
                        continue;
                    }
                }
            }
            return true;
        }
        return false;
    }
}
