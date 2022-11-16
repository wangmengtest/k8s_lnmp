<?php

namespace vhallComponent\broadcast\controllers\v2;

use vhallComponent\decouple\controllers\BaseController;

/**
 * RebroadcastController extends BaseController
 *
 * @uses     yangjin
 * @date     2020-08-13
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class RebroadcastController extends BaseController
{
    /**
     *
     * @author  jin.yang@vhall.com
     * @date    2020-08-13
     */
    public function listsAction()
    {
        $this->success(vss_service()->getRebroadcastService()->lists($this->getParam()));
    }

    /**
     *
     * @author  jin.yang@vhall.com
     * @date    2020-08-13
     */
    public function previewAction()
    {
        $this->success(vss_service()->getRebroadcastService()->preview($this->getParam()));
    }

    /**
     *
     * @author  jin.yang@vhall.com
     * @date    2020-08-13
     */
    public function startAction()
    {
        $this->success(vss_service()->getRebroadcastService()->start($this->getParam()));
    }

    /**
     *
     * @author  jin.yang@vhall.com
     * @date    2020-08-13
     */
    public function stopAction()
    {
        $this->success(vss_service()->getRebroadcastService()->stop($this->getParam()));
    }
}
