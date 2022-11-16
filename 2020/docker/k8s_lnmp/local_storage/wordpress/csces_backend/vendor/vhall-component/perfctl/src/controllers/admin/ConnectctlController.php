<?php
namespace vhallComponent\perfctl\controllers\admin;

use vhallComponent\decouple\controllers\BaseController;

class ConnectctlController extends BaseController
{
    public function setConnectNumAction()
    {
        //参数列表
        $params = $this->getParam();
        vss_logger()->info('setConnectNumAction', ['params'=>$params]);
        $info      = vss_service()->getConnectctlService()->setConnectNum($params);
        $this->success($info);
    }

    public function getConnectNumAction()
    {
        $params = $this->getParam();
        $num      = vss_service()->getConnectctlService()->getConnectNum($params);
        $this->success($num);
    }
}
