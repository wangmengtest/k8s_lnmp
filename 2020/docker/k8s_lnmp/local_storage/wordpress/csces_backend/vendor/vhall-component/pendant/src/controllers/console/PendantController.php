<?php

namespace vhallComponent\pendant\controllers\console;
use vhallComponent\decouple\controllers\BaseController;


use App\Traits\ServiceTrait;

/**
 * class PendantController extends BaseController
 *
 * @package  vhallComponent\pendant\controllers\console
 *
 * @date     2021/3/18
 * @author   jun.ou@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class PendantController extends BaseController
{
    
    

    /**
     * 
     *
     * @date     2021/3/19
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function getListAction()
    {
        $result = vss_service()->getPendantService()->getList($this->getParam());
        $this->success($result);
    }

    /**
     * 
     *
     * @date     2021/3/19
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function createAction()
    {
        vss_service()->getPendantService()->create($this->getParam());
        $this->success();
    }

    /**
     * 
     *
     * @date     2021/3/19
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function updateAction()
    {
        vss_service()->getPendantService()->update($this->getParam());
        $this->success();
    }

    /**
     * 
     *
     * @date     2021/3/19
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function setDefaultFixedAction()
    {
        vss_service()->getPendantService()->setDefaultFixed($this->getParam());
        $this->success();
    }
    
    /**
     * 
     *
     * @date     2021/3/19
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function deleteAction()
    {
        vss_service()->getPendantService()->delete($this->getParam());
        $this->success();
    }


    /**
     * 
     *
     * @date     2021/3/19
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function getStatsListAction()
    {
        $result = vss_service()->getPendantService()->getStatsList($this->getParam());
        $this->success($result);
    }

    public function pushScreenAction()
    {
        vss_service()->getPendantService()->pushScreen($this->getParam());
        $this->success();
    }
}