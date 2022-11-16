<?php

namespace vhallComponent\access\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class GroupAccessModel
 *
 * @package App\Models
 * @property int $id
 * @property string  $group_id   组id
 * @property int $access_id
 * @property bool $status     状态 0：开启 1：关闭
 * @property string  $created_at 创建时间
 * @property string  $updated_at 更新时间
 * @property string  $deleted_at
 */
class GroupAccessModel extends WebBaseModel
{
    protected $table      = 'group_access';

    protected $primaryKey = 'id';

    protected $attributes = [
        'group_id'   => '',
        'access_id'  => '',
        'status'     => 0,
        'created_at' => '1970-01-01 00:00:00',
        'updated_at' => '1970-01-01 00:00:00',
    ];

    public function batchCreate($group_id, $data)
    {
        if (is_array($data) && !empty($data)) {
            $act['group_id'] = $group_id;
            foreach ($data as $v) {
                $act['access_id'] = $v;
                /** @var self $result */
                $result = $this->where(['group_id' => $group_id, 'access_id' => $v])->first();
                if ($result) {
                    if ($result->status == 1) {
                        $this->where(['id' => $result->id])->update(['status' => 0]);
                    } elseif ($result->status == 0) {
                        continue;
                    }
                } else {
                    if (!$this->create($act)->toArray()) {
                        vss_logger()->info('access_group_create_error', $act);
                        continue;
                    }
                }
            }

            return true;
        }
        return false;
    }
}
