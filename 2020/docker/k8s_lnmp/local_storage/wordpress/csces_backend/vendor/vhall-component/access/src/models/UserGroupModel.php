<?php

namespace vhallComponent\access\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * UserGroupModel
 *
 * @property int $id
 * @property string  $account_id 用户id
 * @property int $group_id   组id
 * @property string  $app_id     应用id
 * @property bool $status     0:正常 1:删除
 * @property string  $created_at 创建时间
 * @property string  $updated_at 更新时间
 * @property string  $deleted_at
 *
 * @uses     yangjin
 * @date     2020-07-15
 * @author   jin.yang@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class UserGroupModel extends WebBaseModel
{
    protected $table      = 'user_group';

    protected $primaryKey = 'id';

    protected $attributes = [
        'account_id' => '',
        'group_id'   => '',
        'app_id'     => '',
        'status'     => 0,
        'created_at' => '1970-01-01 00:00:00',
        'updated_at' => '1970-01-01 00:00:00'
    ];

    public function batchCreate($group_id, $app_id, $data)
    {
        if (is_array($data) && !empty($data)) {
            $act['group_id'] = $group_id;
            $act['app_id']   = $app_id;
            foreach ($data as $v) {
                $act['account_id'] = $v;
                if (!$this->create($act)->toArray()) {
                    vss_logger()->info('usergroup_create_error', $act);
                    continue;
                }
            }
            return true;
        }
        return false;
    }
}
