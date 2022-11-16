<?php
namespace App\Component\sms\src\controllers\api;

use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;

class CodeController extends BaseController
{
    /**
     * 发送手机验证码
     *
     * @author ensong.liu@vhall.com
     * @date 2019-05-07 14:18:06
     */
    public function sendAction()
    {
        $phone = $this->getParam('phone');
        if (\Helper::checkPhone($phone) === false) {
            $this->fail(ResponseCode::TYPE_PHONE);
        }
        if (vss_service()->getCodeService()->checkInterval($phone) == true) {
            $this->fail(ResponseCode::COMP_SMS_THRESHOLD_WARING);
        }
        vss_service()->getCodeService()->send($phone, 180, 60);
        $this->success();
    }
}
