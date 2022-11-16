<?php

namespace vhallComponent\watchlimit\services;

use App\Constants\ResponseCode;
use Illuminate\Database\Eloquent\Model;
use vhallComponent\account\constants\AccountConstant;
use vhallComponent\watchlimit\constants\WatchlimitConstant;
use Vss\Common\Services\WebBaseService;

/**
 *
 * Class WatchlimitService
 * @package vhallComponent\watchlimit\services
 */
class WatchlimitService extends WebBaseService
{
    /**
     *  白名单模板导出
     * @auther yaming.feng@vhall.com
     * @date 2021/6/7
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportList()
    {
        //Excel文件名
        $fileName = 'WhiteaccountsList' . date('YmdHis');
        vss_service()->getExportProxyService()->init($fileName)->putRow(['用户名', '密码'])->download();
    }

    /**
     * @param array $where
     * @param array $with
     *
     * @return Model|null|static
     */
    public function whiteUserLsit(array $where, $with = [])
    {
        $applyModel = vss_model()->getWhiteAccountsModel()->getRow($where, $with);
        return $applyModel;
    }

    /**
     * 获取参与报名信息
     *
     * @param array $where
     * @param array $with
     *
     * @return Model|null|static
     */
    public function applyUserLsit(array $where, $with = [])
    {
        $applyModel = vss_model()->getApplyUsersModel()->getRow($where, $with);
        return $applyModel;
    }

    /**
     * 获取表单信息 倒敘
     *
     * @param $ilId
     *
     * @return mixed
     */
    public function getApplyorderby($ilId)
    {
        //2、获取房间扩展信息
        $extend = vss_model()->getApplyModel()->where(['il_id' => $ilId])->orderBy(
            'created_at',
            'desc'
        )->first();
        return $extend;
    }

    /**
     * 创建报名表
     *
     * @param $insert
     *
     * @return bool
     * @throws \Exception
     */
    public function applyCreate($insert)
    {
        if (empty($insert['il_id'])) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        $res = vss_model()->getApplyModel()->saveByIlId($insert['il_id'], $insert);
        if (!$res) {
            $this->fail(ResponseCode::BUSINESS_CREATE_FAILED);
        }
        return $res;
    }

    /**
     * 登录
     *
     * @param $ilId
     * @param $password
     * @param $phone
     *
     * @return bool
     */
    public function getLoginWatch($ilId, $password, $phone)
    {
        $dllogin  = ['il_id' => $ilId];
        $liveList = vss_service()->getRoomService()->roomSel($dllogin);
        vss_logger()->info('limit_type', [$liveList['limit_type']]);
        $limitype = [3];
        if (in_array($liveList['limit_type'], $limitype)) {
            if ($liveList['limit_type'] == 3) {
                //获取白名单信息户信息
                $condition = [
                    'whitename' => $phone,
                    'il_id'     => $ilId,
                ];
                $applyInfo = $this->whiteUserLsit($condition);

                vss_logger()->info('limit_typwhitename', [$applyInfo['whitename']]);
                if (empty($applyInfo['whitename'])) {
                    $this->fail(ResponseCode::EMPTY_WHITE_USER);
                } elseif ($applyInfo['whitepaas'] != $password) {
                    $this->fail(ResponseCode::BUSINESS_INVALID_WHITE_PASSWORD);
                }
            }
        }
        return true;
    }

    /**
     * 参与报名人
     *
     * @param $insert
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function applyUsersCreate($insert)
    {
        $ilId  = $insert['il_id'];
        $phone = $insert['phone'];
        $key   = WatchlimitConstant::APPLYUSER_SUBMIT . $ilId . $insert['type'] . $insert['apply_id'] . $phone;
        if (vss_redis()->lock($key, 2)) {
            $this->fail(ResponseCode::BUSINESS_REPEAT_SUBMIT);
        }

        try {
            vss_model()->getApplyUsersModel()->saveByIlIdAndPhone($insert['il_id'], $insert['phone'], $insert);
        } catch (\Exception $e) {
            vss_logger()->error(__CLASS__ . '@' . __FUNCTION__, [
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
            vss_logger()->info('ApplUsery插入失败或者重复插入', [$insert]);
            $this->fail(ResponseCode::BUSINESS_CREATE_FAILED);
        }
        return true;
    }

    /**
     * 白名单列表
     *
     * @param $ilId
     * @param $pageSize
     * @param $page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function whiteaccountslist($ilId, $pageSize, $page)
    {
        //列表数据
        $condition    = [
            'il_id' => $ilId,
        ];
        $documentList = vss_model()->getWhiteAccountsModel()->setPerPage($pageSize)->getList($condition, [], $page,
            ['id', 'whitename', 'whitepaas', 'desc']);
        return $documentList;
    }

    /**
     * 白名单搜索框
     *
     * @param $phone
     *
     * @return array
     */
    public function whiteaccountssearch($phone, $ilId)
    {
        $list = vss_model()->getWhiteAccountsModel()->whitesearch($phone, $ilId);
        return $list;
    }

    /**
     * 白名单批量删除
     *
     * @param $Ids
     *
     * @return int
     */
    public function deleteByIds($Ids)
    {
        return vss_model()->getWhiteAccountsModel()->deleteByIds($Ids);
    }

    /**
     * 观看限制判断
     *
     * @param $white
     * @param $role
     *
     * @return mixed
     */
    public function watchdecide($white)
    {
        if ($white['room_info']['accs_type'] == 1) {
            return $white;
        }

        if ($white['type'] == 3 || $white['type'] == 4) {
            return $white;
        }

        $limitype = [
            WatchlimitConstant::ACCOUNT_TYPE_APPEAR,
            WatchlimitConstant::ACCOUNT_TYPE_APPROVE,
            WatchlimitConstant::ACCOUNT_TYPE_WHITE
        ];
        if (!in_array($white['room_info']['limit_type'], $limitype)) {
            return $white;
        }

        if ($white['room_info']['limit_type'] == WatchlimitConstant::ACCOUNT_TYPE_APPEAR) {
            if ($white['room_info']['accs_type'] == 3) {
                return $white;
            }
            $condition = [
                'phone' => $white['room_info']['phone'],
                'il_id' => $white['room_info']['il_id'],
            ];
            $applyInfo = $this->applyUserLsit($condition);
            if (empty($applyInfo['phone'])) {
                $white['room_info']['enroll'] = 0;
                $this->success($white);
            } else {
                $white['room_info']['enroll'] = 1;
            }
        } elseif ($white['room_info']['limit_type'] == WatchlimitConstant::ACCOUNT_TYPE_WHITE) {
            $condition = [
                'whitename' => $white['room_info']['phone'],
                'il_id'     => $white['room_info']['il_id'],
            ];
            $applyInfo = $this->whiteUserLsit($condition);
            if (empty($applyInfo['whitename'])) {
                $white['room_info']['enroll'] = 0;
                $this->success($white);
            } else {
                $white['room_info']['enroll'] = 1;
            }
        } elseif ($white['room_info']['limit_type'] == WatchlimitConstant::ACCOUNT_TYPE_APPROVE) {
            //游客
            $white['room_info']['visitor'] = $white['room_info']['accs_type'] == AccountConstant::ACCOUNT_TYPE_VISITOR ? 1 : 0;
        }

        return $white;
    }

    /**
     * 更新房间
     *
     * @param array $params
     *
     * @return bool|Model|null|static
     */
    public function update(array $params)
    {
        $model = vss_model()->getRoomsModel()->getRow(['il_id' => $params['il_id']]);
        if ($model) {
            $model->setAttribute('limit_type', $params['limit_type']);
            $model->update();
        }
        return $model ? $model : false;
    }

    /**
     * 更新答題id
     *
     * @param array $params
     *
     * @return bool|Model|null|static
     */
    public function updateApplyUser(array $params)
    {
        $model = vss_model()->getApplyUsersModel()->getRow([
            'phone'    => $params['phone'],
            'il_id'    => $params['il_id'],
            'apply_id' => $params['apply_id']
        ]);
        if ($model) {
            $model->setAttribute('answer_id', $params['answer_id']);
            $model->update();
        }
        return $model ? $model : false;
    }

    /**
     * 更新表单id
     *
     * @param array $params
     *
     * @return bool|Model|null|static
     */
    public function updateApply(array $params)
    {
        $model = vss_model()->getApplyModel()->getRow(['il_id' => $params['il_id']]);
        if ($model) {
            $model->setAttribute('source_id', $params['source_id']);
            $model->update();
        }
        return $model ? $model : false;
    }

    /**
     * 更新密码id
     *
     * @param array $params
     *
     * @return bool|Model|null|static
     */
    public function updateWhiteAccounts(array $params)
    {
        $model = vss_model()->getWhiteAccountsModel()->getRow([
            'whitename' => $params['whitename'],
            'il_id'     => $params['il_id']
        ]);
        if ($model) {
            $model->setAttribute('whitepaas', $params['whitepaas']);
            $model->update();
        }
        return $model ? $model : false;
    }

    /**
     * 数据去重
     *
     * @param $arr
     * @param $key
     *
     * @return mixed
     */
    public function dataUnique(&$arr, $key)
    {
        array_multisort($arr, SORT_DESC);
        $tmp_arr = [];
        foreach ($arr as $k => $v) {
            if (in_array($v[$key], $tmp_arr)) {
                unset($arr[$k]);
            } else {
                $tmp_arr[] = $v[$key];
            }
        }
        return $arr;
    }
}
