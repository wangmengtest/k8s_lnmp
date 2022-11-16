<?php

namespace vhallComponent\access\services;

use App\Constants\ResponseCode;
use Vss\Common\Services\WebBaseService;

class GroupService extends WebBaseService
{
    private $status = [1, 0];

    const DELETE_STATUS = 1;

    const NORMAL_STATUS = 0;

    /**
     * 创建组
     * @param $group_name
     * @param $app_id
     * @return mixed|\vhallComponent\access\models\GroupModel
     */
    public function create($group_name, $app_id)
    {
        $params = [];
        $params['group_name'] = $group_name;
        $params['app_id'] = $app_id;
        return vss_model()->getGroupModel()->create($params);
    }

    /**
     * 更新组
     * @param $group_id
     * @param $app_id
     * @param $group_name
     * @return mixed|void
     *
     */
    public function update($group_id, $app_id, $group_name)
    {
        $data = [];
        $data['group_name'] = $group_name;
        if (false === vss_model()->getGroupModel()->forUpdate($group_id, $app_id, $data)) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        return true;
    }

    /**
     * 删除组
     * @param $group_id
     * @param $app_id
     * @return bool|mixed
     *
     */
    public function delete($group_id, $app_id)
    {
        $data = [];
        $data['status'] = self::DELETE_STATUS;
        if (false === vss_model()->getGroupModel()->forUpdate($group_id, $app_id, $data)) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        return true;
    }

    /**
     * 为组添加权限
     * @param $group_id
     * @param $access_id
     * @return bool|mixed
     *
     */
    public function addGroupPermission($group_id, $access_id)
    {
        $data = $this->isJsonString($access_id);
        if (false === $data) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        vss_service()->getAccessService()->checkAccessId($data);
        return vss_model()->getGroupAccessModel()->batchCreate($group_id, $data);
    }

    /**
     * 删除组权限
     * @param $group_id
     * @param $access_id
     * @return int|mixed
     *
     */
    public function deleteGroupPermission($group_id, $access_id)
    {
        $data = $this->isJsonString($access_id);
        if (false === $data) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        return vss_model()->getGroupAccessModel()->where(['group_id' => $group_id])
            ->whereIn('access_id', $data)->update(['status' => self::DELETE_STATUS]);
    }

    /**
     * 添加用户到指定组
     * @param $account_id
     * @param $group_id
     * @param $app_id
     * @return bool|mixed
     *
     */
    public function addPermissionForUser($account_id, $group_id, $app_id)
    {
        $data = $this->isJsonString($account_id);
        if (false === $data) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        return vss_model()->getUserGroupModel()->batchCreate($group_id, $app_id, $data);
    }

    /**
     * 删除组的指定用户
     * @param $group_id
     * @param $account_id
     * @param $app_id
     * @return int|mixed
     *
     */
    public function deletePermissionForUser($group_id, $account_id, $app_id)
    {
        $data = $this->isJsonString($account_id);
        if (false === $data) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        return vss_model()->getUserGroupModel()->where(['group_id' => $group_id])
            ->whereIn('account_id', $data)->update(['status' => self::DELETE_STATUS]);
    }

    /**
     * 通过组id获取组权限列表
     * @param $group_id
     * @param $app_id
     * @return array|mixed
     */
    public function getGroupListById($group_id, $app_id)
    {
        $data = vss_model()->getGroupModel()->newQuery()
            ->leftJoin('group_access', 'group_access.group_id', 'group.id')
            ->where('group.app_id', $app_id)
            ->where('group.id', $group_id)
            ->where('group.status', self::NORMAL_STATUS)
            ->where('group_access.status', self::NORMAL_STATUS)
            ->selectRaw('group_access.access_id,group.group_name')
            ->get()
            ->toArray();
        return !empty($data) ? $data : [];
    }

    /**
     * 获取应用的所有组列表
     * @param $app_id
     * @return array
     *
     */
    public function getAPPList($app_id)
    {
        $list = vss_model()->getGroupModel()->where(['app_id' => $app_id, 'status' => 0])->get(['id', 'group_name'])->toArray();
        return $list;
    }

    /**
     * @param $str
     * @return array|bool|mixed
     */
    public function isJsonString($str)
    {
        $jObject = json_decode($str, true);
        return (is_array($jObject)) ? $jObject : false;
    }
}
