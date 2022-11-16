<?php
/**
 * Date: 2020/1/16
 * Time: 20:59
 */

namespace vhallComponent\order\controllers\v2;

use vhallComponent\decouple\controllers\BaseController;

class OrderController extends BaseController
{

    public function listsAction()
    {
        $this->success(vss_service()->getOrderService()->lists($this->getParam()));
    }


    public function incomeAction()
    {
        $this->success(vss_service()->getIncomeService()->getInfo($this->getParam()));
    }
}
