<?php

namespace vhallComponent\scrolling\controllers\console;

use vhallComponent\decouple\controllers\BaseController;

/**
 * ScrollingController extends BaseController
 *
 * @uses     yangjin
 * @date     2020-07-09
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class ScrollingController extends BaseController
{
    /**
     * 创建
     */
    public function saveAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getScrollingService()->save($params));
    }

    /**
     * 详情
     */
    public function infoAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getScrollingService()->info($params));
    }
}
