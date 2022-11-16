<?php
namespace App\Component\perfctl\src\controllers\api;

use vhallComponent\decouple\controllers\BaseController;

class ConnectctlController extends BaseController
{
    public function queueAddAction()
    {
        //参数列表
        $params = $this->getParam();
        $params['account_id'] = $this->accountInfo['account_id'];
        vss_service()->getConnectctlService()->queueAdd($params);
        $this->success();
    }
}
