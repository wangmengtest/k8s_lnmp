<?php
/**
 * 权限操作
 *Created by PhpStorm.
 *DATA: 2019/9/24 15:55
 */

namespace vhallComponent\access\services;

use App\Constants\ResponseCode;
use Vss\Common\Services\WebBaseService;

class AccessService extends WebBaseService
{
    const NORMAL_STATUS = 0;

    const DEL_STATUS    = 1;

    const PAGE_SIZE     = 20;

    /**
     * accessid验证
     *
     * @param $data
     *
     */
    public function checkAccessId($data)
    {
        foreach ($data as $v) {
            $result = vss_model()->getAccessModel()->where('id', $v)->get()->first();
            if (!$result || !$result['id']) {
                $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
            }
        }
    }

    /**
     * 列表
     *
     * @param $page
     * @param $pagesize
     *
     * @return mixed|void
     *
     */
    public function getList($page, $pageSize)
    {
        $pageSize = $pageSize ?: self::PAGE_SIZE;
        $list     = vss_model()->getAccessModel()->where(['status' => self::NORMAL_STATUS])->skip(($page - 1) * $pageSize)->limit($pageSize)->get()->toArray();
        return $list ?? [];
    }

    /**
     * 获取用户的操作记录
     *
     * @param $account_id
     * @param $page
     *
     * @return array|mixed
     *
     */
    public function getAccessLog($account_id, $page)
    {
        $data             = [];
        $data['operator'] = $account_id;
        $page             = $page ? $page : 1;
        $list             = vss_model()->getAccessOpLogModel()->where($data)->orderBy(
            'created_at',
            'desc'
        )->skip(($page - 1) * self::PAGE_SIZE)->limit(self::PAGE_SIZE)->get([
            'operator',
            'content',
            'created_at',
            'type'
        ])->toArray();
        return $list;
    }

    /**
     * 增加操作日志
     *
     * @param $account_id
     * @param $type
     * @param $content
     *
     * @return \vhallComponent\access\models\AccessOpLogModel|null
     */
    public function addLog($account_id, $type, $content)
    {
        return vss_model()->getAccessOpLogModel()->create([
            'operator' => $account_id,
            'content'  => $content,
            'type'     => $type
        ]);
    }

    /**
     * 获取当前用户所属组权限
     *
     * @param $account_id
     * @param $app_id
     *
     * @return bool|mixed
     */
    protected function getAccessInfoByGroupId($account_id, $app_id)
    {
        $group_id = vss_model()->getUserGroupModel()->where(['account_id' => $account_id, 'app_id' => $app_id])
            ->where(['status' => 0])
            ->get(['group_id'])->toArray();
        if (!$group_id) {
            vss_logger()->info('get-access-group-first', ['account_id' => $account_id, 'app_id' => $app_id]);
            return false;
        }

        $list = vss_service()->getGroupService()->getGroupListById(current($group_id)['group_id'], $app_id);
        if (empty($list)) {
            vss_logger()->info('get-access-group-second', ['group_id' => $group_id, 'app_id' => $app_id]);
            return false;
        }
        return $list;
    }

    /**
     * 获取当前用户所属角色权限
     *
     * @param $account_id
     * @param $app_id
     *
     * @return bool|mixed
     */
    protected function getAccessInfoByRoleId($account_id, $app_id)
    {
        $role_id = vss_model()->getUserRoleModel()->where(['account_id' => $account_id, 'app_id' => $app_id])
            ->where(['status' => 0])
            ->get(['role_id'])->toArray();
        if (!$role_id) {
            vss_logger()->info('get-access-role-first', ['account_id' => $account_id, 'app_id' => $app_id]);
            return false;
        }
        $list = vss_service()->getRolesService()->getAccessListById($role_id[0]['role_id'], $app_id);
        if (empty($list)) {
            vss_logger()->info('get-access-role-second', ['role_id' => $role_id, 'app_id' => $app_id]);
            return false;
        }
        return $list;
    }

    /**
     * 获取当前用户的权限集
     *
     * @param $account_id
     * @param $app_id
     *
     * @return array|mixed
     */
    public function getAccessListByUid($account_id, $app_id)
    {
        $group_access_list = $this->getAccessInfoByGroupId($account_id, $app_id);
        $role_access_list  = $this->getAccessInfoByRoleId($account_id, $app_id);
        if (!$group_access_list && !$role_access_list) {
            return [];
        }
        if ($group_access_list && $role_access_list) {
            $access_ids = array_values(array_intersect(
                array_column($group_access_list, 'access_id'),
                array_column($role_access_list, 'access_id')
            ));
            if (!$access_ids) {
                vss_logger()->info('get-access-ids_empty', [
                    'group_access_id' => array_column($group_access_list, 'access_id'),
                    'role_access_id'  => array_column($role_access_list, 'access_id'),
                    'app_id'          => $app_id
                ]);
            }
            return $access_ids;
        }
        return $group_access_list ? array_column($group_access_list, 'access_id') : array_column(
            $role_access_list,
            'access_id'
        );
    }

    /**
     * 获取用户的权限码
     *
     * @param $account_id
     * @param $app_id
     * @param $role_name
     *
     * @return array|mixed
     *
     */
    public function getAccessCodeByUid($account_id, $app_id, $role_name)
    {
        $list      = vss_model()->getAccessModel()->where(['status' => self::NORMAL_STATUS])->get()->toArray();
        $accessArr = array_column($list, 'rule');

        switch ($role_name) {
            case 1:
                break;
            case 2:
                $accessArr = array_diff($accessArr, [12005,23002, 23003, 23004]);
                break;
            case 3:
                $accessArr = array_diff($accessArr, [11004]);
                break;
            case 4:
                $accessArr = array_diff($accessArr, [23002]);
                break;
            default:
                $accessArr = [];
        }

        return array_values($accessArr);
    }

    /**
     * @param $data
     *
     * @return bool|mixed
     */
    public function add($data)
    {


        /*foreach ($data as $k => $v) {
           // vss_model()->getAccessModel()->create(['rule' => $k, 'title' => $v]);
            vss_model()->getAccessModel()->create(['rule' => $v, 'title' => $k]);
        }*/
        return true;
    }
}
