<?php

namespace App\Component\room\src\controllers\api;

use Exception;
use vhallComponent\decouple\controllers\BaseController;

/**
 * InavController extends BaseController
 *
 * @uses     yangjin
 * @date     2020-08-12
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class InavController extends BaseController
{
    /**
     * 获取房间信息（api迁移）
     *
     *
     * @throws Exception
     * @author  jin.yang@vhall.com
     * @date    2020-06-12
     */
    public function getAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'il_id'     => 'required',
            'password'  => '',
            'role_name' => 'integer|in:4,5',
            'from'      => 'string|in:js,ios,android'
        ]);
        $ilId     = $params['il_id'];
        $password = $params['password'];
        $rolename = $params['role_name'];
        $from     = $params['from'];

        $data = vss_service()->getInavService()->get($ilId, $this->accountInfo, $password, $rolename, $from);
        $data = vss_service()->getRoomFormatService()->formatDetail($ilId , $data, $this->accountInfo);
        $this->success($data);
    }

    /**
     * 获取主持人信息
     */
    public function getAnchorRenAction()
    {
        $params = $this->getPost();
        vss_validator($params, [
            'room_id'            => 'required',
            'receive_account_id' => 'required',
        ]);
        $account_id = $params['receive_account_id'];
        $room_id    = $params['room_id'];
        $data       = vss_service()->getRoomService()->getDirect($account_id, $room_id);
        if($data->account_id){
            $deptInfo = vss_service()->getAccountsService()->getOrgAndDeptByAccountId($data->account_id);
            if($deptInfo){
                $data->dept_name = $deptInfo['dept_name'] ?? '';
                $data->org_name = $deptInfo['org_name'] ?? '';
            }
        }
        $this->success($data);
    }
}
