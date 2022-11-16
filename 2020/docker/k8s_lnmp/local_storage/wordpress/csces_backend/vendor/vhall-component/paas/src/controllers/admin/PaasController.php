<?php

namespace vhallComponent\paas\controllers\admin;

use vhallComponent\decouple\controllers\BaseController;

/**
 * PaasController extends BaseController
 *
 * @uses     yangjin
 * @date     2020-09-02
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class PaasController extends BaseController
{
    /**
     * 获取AccessToken，最长有效期一天
     *
     * @author ensong.liu@vhall.com
     * @date   2019-03-19 11:03:40
     */
    public function getAccessTokenAction()
    {
        try {
            $accessToken = vss_service()->getPaasService()->baseCreateAccessToken(
                ['third_party_user_id' => $this->admin['admin_id']]
            );
        } catch (\Exception $e) {
            $accessToken = '';
        }

        $appId = vss_service()->getTokenService()->getAppId();
        //返回数据
        $data = [
            'app_id'       => $appId,
            'account_id'   => sprintf('admin:%s:%d:%s', $appId, $this->admin['admin_id'], str_random()),
            'access_token' => $accessToken,
        ];
        $this->success($data);
    }
}
