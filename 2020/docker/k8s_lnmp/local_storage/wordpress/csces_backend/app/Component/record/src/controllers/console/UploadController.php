<?php

namespace App\Component\record\src\controllers\console;

use Exception;
use vhallComponent\decouple\controllers\BaseController;
use App\Component\record\src\constants\RecordConstant;

/**
 * UploadController extends BaseController
 *
 * @uses     yangjin
 * @date     2020-08-06
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class UploadController extends BaseController
{
    /**
     * 列表
     *
     * @throws Exception
     */
    public function ListAction()
    {
        $data['page_num']   = $this->getParam('page_num', RecordConstant::PAGE_NUM);
        $data['start_time'] = $this->getParam('starttime');
        $data['end_time']   = $this->getParam('endtime');
        $data['source']     = $this->getParam('source', '');
        $data['search']     = $this->getParam('search');
        $data['account_id'] = $this->accountInfo['account_id'];
        $data['il_id']      = $this->getParam('il_id', '');
        $data['page_size']  = $this->getParam('page_size', RecordConstant::PAGE_SIZE);
        $data['app_id']     = vss_service()->getTokenService()->getAppId();

        $list = vss_service()->getRecordService()->getList($data);

        $prekey = RecordConstant::RECORD_DOWN_URL;
        foreach ($list as &$value) {
            $downUrl           = vss_redis()->get($prekey . $value['vod_id']);
            $value['down_url'] = $downUrl ? $downUrl : '';
        }

        $this->success($list);
    }

    /**
     * 重命名
     *
     * @throws Exception
     */
    public function renameAction()
    {
        $params    = $this->getParam();
        $validated = vss_validator($params, [
            'name'      => 'required',
            'record_id' => 'required',
        ]);

        $result = vss_service()->getRecordService()->rename($params);
        $this->success($result);
    }

    /**
     * 详情
     *
     * @throws Exception
     */
    public function infoAction()
    {
        $params           = $this->getParam();
        $validated        = vss_validator($params, [
            'record_id' => 'required',
        ]);
        $params['vod_id'] = $params['record_id'];
        $result           = vss_service()->getRecordService()->info($params);
        $this->success($result);
    }

    /**
     * 删除
     *
     * @throws Exception
     */
    public function delAction()
    {
        $params    = $this->getParam();
        $validated = vss_validator($params, [
            'record_id' => 'required',
        ]);
        $result    = vss_service()->getRecordService()->del($params);
        $this->success($result);
    }

    /**
     * 创建回放
     *
     *
     * @author   ming.wang@vhall.com
     * @uses     wangming
     */
    public function createRecordAction()
    {
        $params    = $this->getParam();
        $validated = vss_validator($params, [
            'il_id'     => '',
            'record_id' => 'required',
        ]);

        $params['vod_id'] = $params['record_id'];
        if (empty($params['il_id'])) {
            $params['il_id'] = 0;
        }
        $params['account_id'] = $this->accountInfo['account_id'];
        $result               = vss_service()->getRecordService()->createRecord($params);
        $this->success($result);
    }

    /**
     * 初始化
     *
     * @return void
     */
    public function initialAction()
    {
        $this->success(
            vss_paas_util()->generateParams(
                [],
                vss_service()->getTokenService()->getAppId(),
                vss_config('paas.apps.saas.appSecret')
            )
        );
    }

    /**
     * 下载
     *
     *
     */
    public function downAction()
    {
        $params    = $this->getParam();
        $validated = vss_validator($params, [
            'record_id' => 'required',
        ]);

        $params['id']     = $params['record_id'];
        $params['app_id'] = vss_service()->getTokenService()->getAppId();

        $result = vss_service()->getRecordService()->down($params);
        $this->success($result);
    }

    /**
     *  根据清晰度下载
     *
     *
     */
    public function downQualityAction()
    {
        $params = $this->getParam();

        $validated       = vss_validator($params, [
            'quality'   => 'required',
            'record_id' => 'required',
        ]);
        $data['id']      = $params['record_id'];
        $data['quality'] = $params['quality'];
        $data['app_id']  = vss_service()->getTokenService()->getAppId();

        $result = vss_service()->getRecordService()->downQuality($data);
        $this->success($result);
    }

    /**
     *删除某个清晰度
     *
     *
     */
    public function delVideoAction()
    {
        $params = $this->getParam();

        $validated = vss_validator($params, [
            'vid'       => 'required',
            'record_id' => 'required',
        ]);

        $data['id']       = $params['record_id'];
        $data['video_id'] = $params['vid'];
        $data['app_id']   = vss_service()->getTokenService()->getAppId();

        $result = vss_service()->getRecordService()->videoDel($data);
        $this->success($result);
    }
}
