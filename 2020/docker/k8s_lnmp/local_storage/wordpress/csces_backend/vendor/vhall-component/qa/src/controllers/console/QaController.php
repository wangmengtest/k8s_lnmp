<?php


namespace vhallComponent\qa\controllers\console;

use vhallComponent\decouple\controllers\BaseController;

/**
 * 控制台使用
 * class AdminControllerTrait
 * @package vhallComponent\qa\controllers\console
 */
class QaController extends BaseController
{
    /**
     * 问答列表导出
     * @auther yaming.feng@vhall.com
     * @date 2021/6/7
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Vss\Exceptions\JsonResponseException
     */
    public function exportAction()
    {
        $params = $this->getParam();
        /**
         * @var ValidatorUtils $validator
         */
        vss_validator($params, [
            'app_id'     => 'required',
            'il_id'      => 'required',
            'begin_time' => 'required|date',
            'end_time'   => 'required|date',
        ], [
            'app_id.required'     => 'APPID不能为空',
            'il_id.required'      => '房间不存在',
            'begin_time.required' => '请选择开始时间',
            'end_time.required'   => '请选择结束时间'
        ]);

        //参数列表
        $appId     = $params['app_id'];
        $ilId      = $params['il_id'];
        $startTime = $params['begin_time'];
        $endTime   = $params['end_time'];
        $accountId = $this->accountInfo['account_id'];

        //Excel文件名
        $fileName = sprintf('%s问答记录%s', $ilId, date('Ymd'));
        vss_service()->getQaService()->createExport($fileName, $appId, $ilId, $accountId, $startTime, $endTime);
        $this->success();
    }
}
