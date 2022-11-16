<?php

namespace vhallComponent\anchorManage\controllers\api;

use vhallComponent\decouple\controllers\BaseController;

/**
 * Class AnchorManageController
 * @authro wei.yang@vhall.com
 * @date 2021/6/16
 */
class AnchorManageController extends BaseController
{

    /**
     * 发送验证码
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Vss\Exceptions\JsonResponseException
     * @author wei.yang@vhall.com
     * @date   2021/6/16
     */
    public function sendVerifyCodeAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'phone' => 'required',
        ]);
        $ip = $_SERVER["REMOTE_ADDR"];
        vss_service()->getAnchorManageService()->smsRiskControl($ip, $params['phone']);
        vss_service()->getAnchorManageService()->checkAnchorByPhone($params['phone']);
        vss_service()->getAnchorManageService()->roomStatusCheck($params['phone']);
        $ret = vss_service()->getCodeService()->send($params['phone']);
        $this->success($ret);
    }

    /**
     * 登录
     *
     * @throws \Vss\Exceptions\JsonResponseException
     * @author wei.yang@vhall.com
     * @date   2021/6/16
     */
    public function loginAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'phone' => 'required',
            'code'  => 'required'
        ]);
        $ret = vss_service()->getAnchorManageService()->login($params['phone'], $params['code']);
        $this->success($ret);
    }

    /**
     * 退出登录
     *
     * @throws \Vss\Exceptions\JsonResponseException
     * @author wei.yang@vhall.com
     * @date   2021/6/21
     */
    public function logoutAction()
    {
        $token = $this->accountInfo['token'];
        $phone = $this->accountInfo['anchor_phone'];
        $ret = vss_service()->getAnchorManageService()->logout($token, $phone);
        $this->success($ret);
    }

    /**
     * 直播列表
     *
     * @throws \Vss\Exceptions\JsonResponseException
     * @author wei.yang@vhall.com
     * @date   2021/6/16
     */
    public function liveListAction()
    {
        $page     = $this->getParam('page', 1);
        $pageSize = $this->getParam('page_size', 10);
        $phone = $this->accountInfo['anchor_phone'];
        $data = vss_service()->getAnchorManageService()->liveList($phone, $page, $pageSize);
        $this->success($data);
    }

    /**
     * 检查主播和直播间是否还存在关联关系
     *
     * @throws \Vss\Exceptions\JsonResponseException
     * @author wei.yang@vhall.com
     * @date   2021/6/16
     */
    public function checkAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'il_id' => 'required',
        ]);
        $phone = $this->accountInfo['anchor_phone'];
        $bool = vss_service()->getAnchorManageService()->checkRelation($params['il_id'], $phone);
        $this->success($bool);
    }

    /**
     * 主播详情
     *
     * @author wei.yang@vhall.com
     * @date   2021/6/16
     */
    public function getAnchorInfoAction()
    {
        $phone = $this->accountInfo['anchor_phone'];
        $ret = vss_service()->getAnchorManageService()->getAnchorInfoInApp($phone);
        $ret['token'] = $this->accountInfo['token'];
        $this->success($ret);
    }

    /**
     * 主播修改昵称
     *
     * @throws \Vss\Exceptions\JsonResponseException
     * @author wei.yang@vhall.com
     * @date   2021/6/16
     */
    public function updateNicknameAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'nickname' => 'required',
        ]);
        $phone = $this->accountInfo['anchor_phone'];
        $bool = vss_service()->getAnchorManageService()->updateNickname($params['nickname'], $phone);
        $this->success($bool);
    }
}
