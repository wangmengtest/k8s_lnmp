<?php

namespace App\Http\Middleware;

use App\Constants\ResponseCode;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use vhallComponent\admin\models\AdminsModel;
use Vss\Exceptions\ValidationException;

/**
 * Admin 模块身份验证
 * Class VerifyAdminAuth
 * @package App\Http\Middleware
 */
class VerifyAdminAuth
{
    protected $tokenExcept = [
        '/admin/auth/login',
        '/admin/wx/cash-qrcod',
        '/admin/wx/callBack',
    ];

    protected $actionExcept = [
        '/admin/auth/login',
        '/admin/auth/logout',
        '/admin/paas/get-access-token',
        '/admim/wx/cash-qrcod',
        '/admin/wx/call-back',
    ];

    /**
     * 超级管理员ID,不收权限验证限制
     *
     * @var array
     */
    protected $superAdminList = [1];

    /**
     * 超级管理角色ID,不收权限验证限制
     *
     * @var array
     */
    protected $superRoleList = [1];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $admin = $this->verifyToken($request);
        $this->verifyRole($request, $admin);
        $this->verifyAction($request, $admin);

        return $next($request);
    }

    /**
     * token 检查
     * @auther yaming.feng@vhall.com
     * @date 2021/4/26
     *
     * @param Request $request
     *
     * @return array|Model|AdminsModel|null
     */
    protected function verifyToken(Request $request)
    {
        if ($this->isMatch($request, $this->tokenExcept, [])) {
            return [];
        }

        $token = $request->get('token');
        if (!$token) {
            throw new ValidationException(ResponseCode::AUTH_LOGIN_TOKEN_INVALID);
        }

        $condition = ['token' => $token];
        $admin     = vss_model()->getAdminsModel()->getRow($condition);
        if (!$admin) {
            throw new ValidationException(ResponseCode::AUTH_LOGIN_TOKEN_INVALID);
        }

        if (strtotime($admin['token_expire']) < time()) {
            throw new ValidationException(ResponseCode::AUTH_LOGIN_TOKEN_EXPIRE);
        }

        if ($admin['status'] != vss_model()->getAdminsModel()::STATUS_ENABLED) {
            throw new ValidationException(ResponseCode::BUSINESS_ADMIN_DISABLE);
        }

        $request->offsetSet('vss_admin', $admin);

        return $admin;
    }

    /**
     * role 检查
     * @auther yaming.feng@vhall.com
     * @date 2021/4/26
     *
     * @param Request $request
     * @param         $admin
     */
    protected function verifyRole(Request $request, $admin)
    {
        if ($this->isMatch($request, $this->actionExcept, $admin)) {
            return;
        }

        $roleId   = $admin['role_id'] ?: 0;
        $roleInfo = vss_model()->getRoleModel()->getRow(['role_id' => $roleId]);
        if (empty($roleInfo)) {
            throw new ValidationException(ResponseCode::EMPTY_ROLE);
        }

        if ($roleInfo['status'] != vss_model()->getRoleModel()::STATUS_ENABLED) {
            throw new ValidationException(ResponseCode::AUTH_LOGIN_ROLE_DISABLE);
        }
    }

    /**
     * action 检查
     * @auther yaming.feng@vhall.com
     * @date 2021/4/26
     *
     * @param Request $request
     * @param         $admin
     */
    protected function verifyAction(Request $request, $admin)
    {
        if ($this->isMatch($request, $this->actionExcept, $admin)) {
            return;
        }

        //操作信息
        $condition  = [
            'controller_name' => sprintf('%sController', get_controller_name()),
            'action_name'     => sprintf('%sAction', get_action_name()),
        ];
        $actionInfo = vss_model()->getActionsModel()->getRow($condition);
        if (empty($actionInfo)) {
            throw new ValidationException(ResponseCode::EMPTY_ACTION);
        }

        //角色权限是否包括操作权限
        $condition = [
            'role_id'   => $admin['role_id'],
            'action_id' => $actionInfo['action_id'],
        ];

        $roleActionInfo = vss_model()->getRoleActionsModel()->getRow($condition);
        if (empty($roleActionInfo)) {
            throw new ValidationException(ResponseCode::AUTH_NOT_PERMISSION);
        }
    }

    /**
     * 检查忽略条件是否匹配
     * @auther yaming.feng@vhall.com
     * @date 2021/4/26
     *
     * @param Request $request
     * @param         $paths
     * @param         $admin
     *
     * @return bool
     */
    protected function isMatch(Request $request, $paths, $admin): bool
    {
        if (is_matching_path($request, $paths)) {
            return true;
        }

        if ($admin && $this->isSuperRole($admin['role_id'])) {
            return true;
        }

        return false;
    }

    /**
     * 是否超级角色
     *
     * @param $roleId
     *
     * @return bool
     */
    protected function isSuperRole($roleId): bool
    {
        return in_array((int)$roleId, $this->superRoleList, true);
    }

    /**
     * 是否超级管理员
     *
     * @param $adminId
     *
     * @return bool
     */
    protected function isSuperAdmin($adminId): bool
    {
        return in_array((int)$adminId, $this->superAdminList, true);
    }
}
