<?php

namespace vhallComponent\pendant\controllers\v2;
use vhallComponent\decouple\controllers\BaseController;


use App\Traits\ServiceTrait;

/**
 * class PendantController extends BaseController
 *
 * @package  vhallComponent\pendant\controllers\v2
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
    public function getDefaultFixedAction()
    {
        $result = vss_service()->getPendantService()->getDefaultFixed($this->getParam());
        $this->success($result);
    }

    /**
     * 
     *
     * @date     2021/3/19
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function clickAction()
    {
        $params               = $this->getParam();
        $params['account_id'] = $this->tokenArr['third_party_user_id'];

        vss_service()->getPendantService()->click($params);
        $this->success();
    }


    /**
     * 
     *
     * @date     2021/3/19
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function getPushListAction()
    {
        $result = vss_service()->getPendantService()->getPushList($this->getParam());
        $this->success($result);
    }

    /**
     * 
     *
     * @date     2021/3/19
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function pushScreenAction()
    {
        vss_service()->getPendantService()->pushScreen($this->getParam());
        $this->success();
    }
}