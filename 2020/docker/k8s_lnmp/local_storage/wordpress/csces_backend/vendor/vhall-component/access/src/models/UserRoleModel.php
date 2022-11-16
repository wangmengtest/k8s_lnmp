<?php

namespace vhallComponent\access\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * UserRoleModel
 *
 * @property int $id
 * @property string  $account_id 用户id
 * @property int $role_id    角色ID
 * @property string  $app_id     应用id
 * @property bool $status     0：正常 1：删除
 * @property string  $created_at 创建时间
 * @property string  $updated_at 更新时间
 * @property string  $deleted_at
 *
 * @uses     yangjin
 * @date     2020-07-15
 * @author   jin.yang@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class UserRoleModel extends WebBaseModel
{
    protected $table      = 'user_role';

    protected $primaryKey = 'id';

    protected $attributes = [
        'account_id' => '',
        'role_id'    => '',
        'app_id'     => '',
        'status'     => 0,
        'created_at' => '1970-01-01 00:00:00',
        'updated_at' => '1970-01-01 00:00:00'
    ];

    public function batchCreate($role_id, $app_id, $data)
    {
        if (is_array($data) && !empty($data)) {
            $act['role_id'] = $role_id;
            $act['app_id']  = $app_id;
            foreach ($data as $v) {
                $act['account_id'] = $v;
                /** @var self $result */
                $result = $this->where([
                    'role_id'    => $role_id,
                    'account_id' => $v,
                    'app_id'     => $app_id
                ])->first();
                if ($result) {
                    if ($result->status == 1) {
                        $this->where(['id' => $result->id])->update(['status' => 0]);
                    } elseif ($result->status == 0) {
                        continue;
                    }
                } else {
                    if (!$this->create($act)->toArray()) {
                        vss_logger()->info('user_role_create_error', $act);
                        continue;
                    }
                }
                /* $res=$this->updateOrCreate($act, $act);
                 if($res->id<1){
                     vss_logger()->info('role_user_create_error', $act);
                     continue;
                 }*/
            }
            return true;
        }
        return false;
    }
}
