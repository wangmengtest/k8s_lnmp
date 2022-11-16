<?php

namespace App\Component\account\src\controllers\console;
use App\Component\account\src\constants\AccountConstant;
use App\Component\account\src\constants\AccountOrgConstant;
use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;

/**
 * AccountController extends BaseController
 */
class AccountController extends BaseController
{
    /**
     * 用户-信息
     *
     * @return void
     * @author  ensong.liu@vhall.com
     * @date    2019-02-16 13:47:11
     * @method GET
     * @request int account_id    用户ID
     */
    public function getAction()
    {
        //参数列表
        $accountId = $this->getParam('account_id');
        $data      = vss_service()->getAccountsService()->getOne(['account_id' => $accountId]);
        if (empty($data)) {
            $this->fail(ResponseCode::EMPTY_USER);
        }
        $this->success($data);
    }

    /**
     * 用户-列表
     *
     * @return void
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:31:43
     * @method  GET
     * @request int     page        页码
     * @request string  keyword     关键字
     * @request string  begin_time  开始时间
     * @request string  end_time    结束时间
     * @request int     status      状态
     */
    public function listAction()
    {
        //参数列表
        $page      = $this->getParam('page');
        $keyword   = trim($this->getParam('keyword'));
        $beginTime = $this->getParam('begin_time');
        $endTime   = $this->getParam('end_time');
        $status    = $this->getParam('status', AccountConstant::STATUS_ENABLED);
        //$type      = $this->getParam('type', 2);
        $dept      = $this->getParam('dept', '');
        $org       = $this->getParam('org', '');
        $pageSize  = $this->getParam('pagesize', '20');

        // dept 权限控制
        $dept     = vss_service()->getAccountsService()->adaptDeptPermission($this->accountInfo, $dept, "dept");
        $org     = vss_service()->getAccountsService()->adaptDeptPermission($this->accountInfo, $org, "org");

        if($org > AccountOrgConstant::ORG_VIRTUAL_ID){
            $this->success([]);
        }
        vss_service()->getAccountFormatService()->getCommonDeptOrOrg($dept, $org, $this->accountInfo, 'account');

        $data = vss_service()->getAccountsService()->getList([
            'keyword'      => $keyword,
            'begin_time'   => $beginTime,
            'end_time'     => $endTime,
            'status'       => $status,
            'user_type'    => AccountConstant::USER_TYPE_CSCES,
            'dept_ids'     => $dept,
            'org_ids'      => $org,
            'order_by'     => 'user_id',
        ], $page, $pageSize);

        $this->success(vss_service()->getAccountFormatService()->formatList($data));
    }

    /**
     * 用户-导出
     *
     * @return void
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:25:37
     * @method  GET
     * @request string  keyword 关键字
     * @request string  begin_time  开始时间
     * @request string  end_time    结束时间
     * @request int     status      状态
     */
    public function exportListAction()
    {
        //参数列表
        $keyword   = $this->getParam('keyword');
        $beginTime = $this->getParam('begin_time');
        $endTime   = $this->getParam('end_time');
        $status    = $this->getParam('status');
        $type      = $this->getParam('type');

        vss_service()->getAccountsService()->exportList($keyword, $beginTime, $endTime, $status, $type);
    }

    /**
     * 用户-新增
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:25:37
     * @method  POST
     * @request string  phone       手机号码
     * @request string  username    登录名
     * @request string  nickname    昵称
     * @request int     sex         性别
     */
    public function addAction()
    {
        //参数列表
        $phone    = $this->getParam('phone');
        $username = $this->getParam('username');
        $nickname = $this->getParam('nickname');
        $sex      = $this->getParam('sex');
        $type     = $this->getParam('type', 2);

        //返回数据
        $data = vss_service()->getAccountsService()->add($phone, $username, $nickname, $sex, $type);
        $this->success($data);
    }

    /**
     * 用户-编辑
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:25:37
     * @method  POST
     * @request int     account_id  用户ID
     * @request string  phone       手机号码
     * @request string  username    登录名
     * @request string  nickname    昵称
     * @request int     sex         性别
     */
    public function editAction()
    {
        //参数列表
        $accountId = $this->getParam('account_id');
        $phone     = $this->getParam('phone');
        $username  = $this->getParam('username');
        $nickname  = $this->getParam('nickname');
        $sex       = $this->getParam('sex');

        //返回数据
        $data = vss_service()->getAccountsService()->edit($accountId, $phone, $username, $nickname, $sex);
        $this->success($data);
    }

    /**
     * 用户-修改状态
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:25:37
     * @method  POST
     * @request int account_id  用户ID
     * @request int status      用户状态
     */
    public function editStatusAction()
    {
        //参数列表
        $accountId = $this->getParam('user_id');
        $status    = $this->getParam('status');

        //返回数据
        $data = vss_service()->getAccountsService()->editStatus($accountId, $status);
        $this->success($data);
    }
}
