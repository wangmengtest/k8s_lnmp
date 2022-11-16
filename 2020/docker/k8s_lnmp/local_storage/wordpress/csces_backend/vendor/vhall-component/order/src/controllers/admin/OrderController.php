<?php
/**
 * Date: 2020/1/16
 * Time: 20:59
 */

namespace vhallComponent\order\controllers\admin;

use vhallComponent\decouple\controllers\BaseController;

class OrderController extends BaseController
{

    public function listsAction()
    {
        $params['app_id'] = vss_service()->getTokenService()->getAppId();
        $params['status'] = $this->getParam('type', 0);
        $start_time       = $this->getParam('start_time', 0);
        $end_time         = $this->getParam('end_time', 0);
        if (!empty($start_time)) {
            $params['start_time'] = $start_time;
        }
        if (!empty($end_time)) {
            $params['end_time'] = $end_time;
        }
        $params['curr_page'] = $this->getParam('curr_page', 1);
        $params['page_size'] = $this->getParam('page_size', 20);

        $this->success(vss_service()->getOrderService()->lists($params));
    }


    public function incomeAction()
    {
        $params['app_id']     = vss_service()->getTokenService()->getAppId();

        $this->success(vss_service()->getIncomeService()->getInfo($params));
    }
}
