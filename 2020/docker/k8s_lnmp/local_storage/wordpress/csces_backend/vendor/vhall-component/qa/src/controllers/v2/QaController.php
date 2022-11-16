<?php
/**
 * Created by PhpStorm.
 * User: lhl
 * Date: 2019/6/11
 * Time: 11:45
 */

namespace vhallComponent\qa\controllers\v2;

use vhallComponent\decouple\controllers\BaseController;

class QaController extends BaseController
{
    /**
     * @return ValidatorUtils
     */
    public function validate($data, $rules, $messages = [], $customAttributes = [])
    {
        return new ValidatorUtils($data, $rules, $messages, $customAttributes);
    }

    public function createAction()
    {
        $this->success(vss_service()->getQaService()->create($this->getParam()));
    }

    public function showAction()
    {
        vss_service()->getQaService()->show($this->getParam());
        $this->success();
    }

    public function answerAction()
    {
        $this->success(vss_service()->getQaService()->answer($this->getParam()));
    }

    public function listsAction()
    {
        $this->success(vss_service()->getQaService()->lists($this->getParam()));
    }

    public function switchAction()
    {
        $params = $this->getParam();

        vss_validator($params, [
            'room_id' => 'required',
            'status'  => 'required'
        ]);
        vss_service()->getQaService()->setQa(
            $params['room_id'],
            $params['status']
        );
        $this->success();
    }

    public function dealAction()
    {
        $this->success(vss_service()->getQaService()->deal($this->getParam()));
    }

    public function dealAnswerAction()
    {
        $this->success(vss_service()->getQaService()->dealAnswer($this->getParam()));
    }

    /**
     * 直播间 同问功能开关
     * @auther yaming.feng@vhall.com
     * @date 2020/12/25
     *
     */
    public function switchAlsoAskAction()
    {
        $params = $this->getParam();
        /**
         * @var ValidatorUtils $validator
         */
        vss_validator($params, [
            'room_id' => 'required',
            'status'  => 'required'
        ]);

        vss_service()->getQaService()->switchQaAlsoAsk(
            $params['room_id'],
            $params['status']
        );
        $this->success();
    }

    /**
     * 观众 同问/取消同问
     * @auther yaming.feng@vhall.com
     * @date 2020/12/28
     *
     */
    public function toggleAlsoAskAction()
    {
        $this->success(vss_service()->getQaService()->toggleAlsoAsk($this->getParam()));
    }
}
