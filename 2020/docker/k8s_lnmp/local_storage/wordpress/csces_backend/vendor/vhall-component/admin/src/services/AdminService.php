<?php

namespace vhallComponent\admin\services;

use App\Constants\ResponseCode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use vhallComponent\admin\models\AdminsModel;
use Vss\Common\Services\WebBaseService;

/**
 * AdminService
 *
 * @uses     yangjin
 * @date     2020-08-21
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class AdminService extends WebBaseService
{
    const SUPER_ADMIN_LIST = [1];

    protected $expireToken = 86400;

    /**
     * @param $adminName
     * @param $password
     *
     * @return array
     *
     * @author  jin.yang@vhall.com
     * @date    2020-09-02
     */
    public function login($adminName, $password)
    {
        //验证管理员信息
        $condition  = ['admin_name' => $adminName];
        $adminModel = vss_model()->getAdminsModel();
        $adminInfo  = $adminModel->getRow($condition);
        if (empty($adminInfo) || $adminModel::verifyPassword($password, $adminInfo['password']) == false) {
            $this->fail(ResponseCode::BUSINESS_LOGIN_FAILED);
        }

        //状态
        if ($adminInfo['status'] != $adminModel::STATUS_ENABLED) {
            $this->fail(ResponseCode::BUSINESS_ADMIN_DISABLE);
        }

        //角色信息
        $condition = ['role_id' => $adminInfo['role_id']];
        $with      = ['menues', 'actions'];
        $roleInfo  = vss_model()->getRoleModel()->getRow($condition, $with);
        if (empty($roleInfo)) {
            $this->fail(ResponseCode::EMPTY_ROLE);
        }

        //刷新token
        $adminInfo['token'] = AdminsModel::refreshToken($adminInfo['admin_id'], $this->expireToken);
        AdminsModel::refreshLoginNum($adminInfo['admin_id']);

        //返回数据
        return [
            'admin_id'   => $adminInfo['admin_id'],
            'admin_name' => $adminInfo['admin_name'],
            'nick_name'  => $adminInfo['nick_name'],
            'token'      => $adminInfo['token'],
            'role'       => $roleInfo,
        ];
    }

    /**
     * @param $adminId
     *
     * @return bool
     * @author  jin.yang@vhall.com
     * @date    2020-09-02
     */
    public function loginout($adminId)
    {
        AdminsModel::refreshLoginInfo((int)$adminId);
        AdminsModel::refreshToken((int)$adminId, 0);

        return true;
    }

    /**
     * 管理员-管理员信息
     *
     * @param $adminId
     *
     * @return Model|AdminsModel
     *
     *
     * @request int admin_id    管理员ID
     */
    public function get($adminId)
    {
        //管理员信息
        $condition = [
            'admin_id' => $adminId,
        ];
        $with      = ['role'];
        $adminInfo = vss_model()->getAdminsModel()->getRow($condition, $with);
        if (empty($adminInfo)) {
            $this->fail(ResponseCode::EMPTY_ADMIN);
        }

        //返回数据
        return $adminInfo;
    }

    /**
     * 管理员-列表
     *
     * @param $page
     * @param $keyword
     * @param $beginTime
     * @param $endTime
     * @param $roleId
     * @param $status
     *
     * @return LengthAwarePaginator
     * @request int     page        页码
     * @request string  keyword     关键字
     * @request string  begin_time  开始时间
     * @request string  end_time    结束时间
     * @request int     role_id     角色ID
     * @request int     status      状态
     */
    public function list($page, $keyword, $beginTime, $endTime, $roleId, $status)
    {
        //列表数据
        $condition = [
            'keyword'    => $keyword,
            'begin_time' => $beginTime,
            'end_time'   => $endTime,
            'role_id'    => $roleId,
            'status'     => $status,
        ];
        $with      = ['role'];
        return vss_model()->getAdminsModel()->getList($condition, $with, $page);
    }

    /**
     * 管理员-导出
     *
     * @param $keyword
     * @param $beginTime
     * @param $endTime
     * @param $roleId
     * @param $status
     *
     * @return void
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:31:43
     * @method  GET
     * @request string  keyword     关键字
     * @request string  begin_time  开始时间
     * @request string  end_time    结束时间
     * @request int     role_id     角色ID
     * @request int     status      状态
     */
    public function exportList($keyword, $beginTime, $endTime, $roleId, $status)
    {
        //Excel文件名
        $fileName = 'AdminList' . date('YmdHis');
        $header = ['管理员ID', '登录名', '手机号', '邮箱', '加入时间', '角色', '状态'];
        $exportProxyService = vss_service()->getExportProxyService()->init($fileName)->putRow($header);

        //列表数据
        $page = 1;
        while (true) {
            //当前page下列表数据
            $condition = [
                'keyword'    => $keyword,
                'begin_time' => $beginTime,
                'end_time'   => $endTime,
                'role_id'    => $roleId,
                'status'     => $status,
            ];
            $with      = ['role'];
            $adminList = vss_model()->getAdminsModel()->setPerPage(1000)->getList($condition, $with, $page);
            if (!empty($adminList->items())) {
                foreach ($adminList->items() as $adminItem) {
                    $row = [
                        $adminItem['admin_id'] ?: '-',
                        $adminItem['admin_name'] ?: ' -',
                        $adminItem['mobile'] ?: '-',
                        $adminItem['email'] ?: '-',
                        $adminItem['created_at'] ?: '-',
                        $adminItem['role']['name'] ?: '-',
                        $adminItem['status_str'] ?: '-'
                    ];

                    $exportProxyService->putRow($row);
                }
            }

            //跳出while
            if ($page >= $adminList->lastPage() || $page >= 10) { //10页表示1W上限
                break;
            }
            //下一页
            $page++;
        }

        //下载文件
        $exportProxyService->download();
    }

    /**
     * 管理员-删除
     *
     * @param $adminIds
     *
     * @return array
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:31:43
     * @method  POST
     * @request int admin_id    管理员ID
     */
    public function delete($adminIds)
    {
        //删除管理员记录
        $data        = [];
        $adminIdList = explode(',', $adminIds);
        foreach ($adminIdList as $adminId) {
            if (in_array((string)$adminId, array_map('strval', self::SUPER_ADMIN_LIST), true)) {
                continue;
            }
            $condition = [
                'admin_id' => $adminId,
            ];
            $adminInfo = vss_model()->getAdminsModel()->getRow($condition);
            if ($adminInfo && $adminInfo->delRow($adminId)) {
                array_push($data, $adminInfo['admin_id']);
            }
        }

        return $data;
    }

    /**
     * 管理员-添加
     *
     * @param $adminName
     * @param $nickName
     * @param $password
     * @param $confirmPassword
     * @param $mobile
     * @param $email
     * @param $roleId
     *
     * @return AdminsModel|null
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:31:43
     * @method  POST
     * @request string  admin_name          管理员登录名
     * @request string  nick_name           管理员昵称
     * @request string  password            管理员密码
     * @request string  confirm_password    确认管理员密码
     * @request string  mobile              手机号码
     * @request string  email               邮箱地址
     * @request int     role_id             角色ID
     */
    public function add($adminName, $nickName, $password, $confirmPassword, $mobile, $email, $roleId)
    {
        $adminModel = vss_model()->getAdminsModel();
        //登录名已存在
        if ($adminModel->getCount(['admin_name' => $adminName])) {
            $this->fail(ResponseCode::BUSINESS_USER_NAME_EXIST);
        }

        //昵称已存在
        if ($adminModel->getCount(['nick_name' => $nickName])) {
            $this->fail(ResponseCode::BUSINESS_NICKNAME_EXIST);
        }

        //两次密码是否一致
        if (empty($password) || $password != $confirmPassword) {
            $this->fail(ResponseCode::AUTH_ENTERED_PASSWORDS_DIFFER);
        }

        //保存数据
        $attributes = [
            'admin_name' => $adminName,
            'nick_name'  => $nickName,
            'password'   => $adminModel::hashPassword($password),
            'mobile'     => $mobile,
            'email'      => $email,
            'role_id'    => $roleId,
            'status'     => $adminModel::STATUS_ENABLED,
        ];
        $adminInfo  = $adminModel->addRow($attributes);
        if (!$adminInfo) {
            $this->fail(ResponseCode::BUSINESS_CREATE_FAILED);
        }

        //返回数据
        return $adminInfo;
    }

    /**
     * 管理员-编辑
     *
     * @param $adminId
     * @param $adminName
     * @param $nickName
     * @param $password
     * @param $confirmPassword
     * @param $mobile
     * @param $email
     * @param $roleId
     *
     * @return bool
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:31:43
     * @method  POST
     * @request int     admin_id    管理员ID
     * @request string  admin_name  管理员登录名
     * @request string  nick_name   管理员昵称
     * @request string  password    密码
     * @request string  mobile      手机号码
     * @request string  email       邮箱地址
     * @request int     role_id     角色ID
     */
    public function edit(
        $adminId,
        $adminName,
        $nickName,
        $password,
        $confirmPassword,
        $mobile,
        $email,
        $roleId
    ) {
        //管理员信息
        $adminInfo = vss_model()->getAdminsModel()->getRow(['admin_id' => $adminId]);
        if (empty($adminInfo)) {
            $this->fail(ResponseCode::EMPTY_ADMIN);
        }

        //登录名已存在
        if ($adminInfo['admin_name'] != $adminName
            && vss_model()->getAdminsModel()->getCount(['admin_name' => $adminName])
        ) {
            $this->fail(ResponseCode::BUSINESS_USER_NAME_EXIST);
        }

        //昵称已存在
        if ($adminInfo['nick_name'] != $nickName && vss_model()->getAdminsModel()->getCount(['nick_name' => $nickName])) {
            $this->fail(ResponseCode::BUSINESS_NICKNAME_EXIST);
        }

        //保存数据
        $attributes = [
            'nick_name' => $nickName,
            'mobile'    => $mobile,
            'email'     => $email,
            'role_id'   => $roleId,
        ];
        if (!empty($password)) {
            //两次密码是否一致
            if ($password != $confirmPassword) {
                $this->fail(ResponseCode::AUTH_ENTERED_PASSWORDS_DIFFER);
            }

            $attributes['password'] = AdminsModel::hashPassword($password);
        }
        if ($adminInfo->updateRow($adminId, $attributes) == false) {
            $this->fail(ResponseCode::BUSINESS_EDIT_FAILED);
        }

        return true;
    }

    /**
     * 管理员-修改密码
     *
     * @param $adminId
     * @param $password
     * @param $confirmPassword
     *
     * @return bool
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:31:43
     * @method  POST
     * @request string  password          管理员密码
     * @request string  confirm_password  确认密码
     * @request string
     */
    public function editPassword($adminId, $password, $confirmPassword)
    {
        //两次密码是否一致
        if (empty($password) || $password != $confirmPassword) {
            $this->fail(ResponseCode::AUTH_ENTERED_PASSWORDS_DIFFER);
        }

        //管理员信息
        $adminInfo = vss_model()->getAdminsModel()->getRow(['admin_id' => $adminId]);
        if (empty($adminInfo)) {
            $this->fail(ResponseCode::EMPTY_ADMIN);
        }

        //保存数据
        $attributes = [
            'password' => AdminsModel::hashPassword($password),
        ];
        if ($adminInfo->updateRow($adminId, $attributes) == false) {
            $this->fail(ResponseCode::BUSINESS_EDIT_FAILED);
        }

        return true;
    }

    /**
     * 管理员-修改状态
     *
     * @param $adminId
     * @param $status
     *
     * @return bool
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:31:43
     * @method  POST
     * @request int admin_id    管理员ID
     * @request int status      管理员状态
     */
    public function editStatus($adminId, $status)
    {
        //管理员信息
        $adminInfo = vss_model()->getAdminsModel()->getRow(['admin_id' => $adminId]);
        if (empty($adminInfo)) {
            $this->fail(ResponseCode::EMPTY_ADMIN);
        }

        //保存数据
        $attributes = ['status' => $status];
        if ($adminInfo->updateRow($adminId, $attributes) == false) {
            $this->fail(ResponseCode::BUSINESS_EDIT_FAILED);
        }

        return true;
    }
}
