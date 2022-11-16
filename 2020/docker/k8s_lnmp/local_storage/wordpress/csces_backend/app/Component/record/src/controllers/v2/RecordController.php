<?php

namespace App\Component\record\src\controllers\v2;

use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;

/**
 * RecordController extends BaseController
 *
 * @uses     yangjin
 * @date     2020-08-12
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class RecordController extends BaseController
{

    /**
     * @return mixed
     * @author  jin.yang@vhall.com
     * @date    2020-08-12
     */

    /**
     * 列表
     *
     */
    public function listAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'account_id' => 'required',
        ]);
        $data = vss_service()->getRecordService()->getList($params);
        $this->success($data);
    }

    /**
     * 重命名
     */
    public function renameAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'name'      => 'required',
            'record_id' => 'required',
        ]);
        $data = vss_service()->getRecordService()->rename($params);
        $this->success($data);
    }

    /**
     * 详情
     *
     */
    public function infoAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'record_id' => 'required',
        ]);
        $params['vod_id'] = $params['record_id'];
        $data             = vss_service()->getRecordService()->info($params);
        $this->success($data);
    }

    /**
     * 下载
     */
    public function downAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'id' => 'required',
        ]);
        $data = vss_service()->getRecordService()->down($params);
        $this->success($data);
    }

    /**
     * 删除
     *
     */
    public function delAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'record_id' => 'required',
        ]);
        $this->success(vss_service()->getRecordService()->del($params));
    }

    /**
     * 创建记录
     */
    public function createAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getRecordService()->createRecord($params));
    }

    /**
     * quality下载
     */
    public function downQualityAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'quality' => 'required',
            'id'      => 'required',
        ]);
        $data = vss_service()->getRecordService()->downQuality($params);
        $this->success($data);
    }

    /**
     * video
     */
    public function delVideoAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'id'       => 'required',
            'video_id' => 'required',
        ]);
        $this->success(vss_service()->getRecordService()->videoDel($params));
    }

    /**
     *
     * 获取房间下音频
     *
     */
    public function getRoomVodListAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'il_id'      => 'required',
            'account_id' => 'required',
        ]);
        $this->success(vss_model()->getRecordModel()->getList($params));
    }

    /**
     * 获取直播间回放总时长
     */
    public function getDurationSumAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'il_id' => 'required',
        ]);
        $this->success(vss_service()->getRecordService()->getRecordDurationSum($params));
    }

    //+++++++++++++++++++++++++++++++++++++++==============================20191101+++++++++++++++++++++++++++++++++++++++

    /**
     * rtmp
     *
     * @throws Exception
     */
    public function pullStreamAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'status'    => 'required',
            'room_id'   => 'required',
            'config_id' => 'required',
        ]);
        if ($params['status'] == 3) {
            $this->fail(ResponseCode::BUSINESS_PULL_STREAM_FAILED);
        }
        $key = 'pull_stream_' . $params['room_id'];
        vss_redis()->set($key, $params['config_id']);
        vss_redis()->persist($key);
        if (!vss_service()->getRoomService()->startLive($params['room_id'], 4)) {
            $this->fail(ResponseCode::BUSINESS_START_LIVE_FAILED);
        }
        $this->success();
    }
}
