<?php

namespace vhallComponent\room\controllers\api;

use vhallComponent\decouple\controllers\BaseController;

/**
 * RoomController extends BaseController
 *
 * @uses     yangjin
 * @date     2020-08-10
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class RoomController extends BaseController
{
    public function listAction()
    {
        $beginTime = $this->getParam('begin_time');
        $endTime   = $this->getParam('end_time');
        $status    = $this->getParam('status');
        $pageSize  = $this->getParam('pagesize', 20);
        $page      = $this->getParam('page', 1);
        $ilId      = $this->getParam('il_id');

        $liveList = vss_service()->getRoomService()->getList(
            $ilId,
            '',
            $beginTime,
            $endTime,
            $status,
            $page,
            $pageSize,
            $this->accountInfo['account_id']
        );
        $this->success($liveList);
    }
}
