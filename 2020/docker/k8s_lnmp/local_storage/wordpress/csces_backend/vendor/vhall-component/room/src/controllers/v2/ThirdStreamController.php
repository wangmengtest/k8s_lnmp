<?php

namespace vhallComponent\room\controllers\v2;

use vhallComponent\decouple\controllers\BaseController;
use Illuminate\Support\Arr;

/**
 * ThirdStreamController extends BaseController
 *
 * @uses     yangjin
 * @date     2020-08-14
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class ThirdStreamController extends BaseController
{
    public function listAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getThirdStreamService()->lists($params));
    }

    /**
     *
     * @author  jin.yang@vhall.com
     * @date    2020-08-14
     */
    public function startAction()
    {
        $params = $this->getParam();
        $rule = [
            'url'        => 'required',
            'status'     => 'required',
            'app_id'     => 'required',
            'account_id' => 'required',
            'room_id'    => 'required',
        ];
        $data = vss_validator($params, $rule);
        $this->success(vss_service()->getThirdStreamService()->save($data));
    }

    /**
     *
     * @author  jin.yang@vhall.com
     * @date    2020-08-14
     */
    public function stopAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'id' => 'required',
        ]);

        $this->success(vss_service()->getThirdStreamService()->update($params));
    }

    /**
     *
     * @author  jin.yang@vhall.com
     * @date    2020-08-14
     */
    public function delAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'id' => 'required',
        ]);
        $this->success(vss_service()->getThirdStreamService()->delete($params));
    }
}
