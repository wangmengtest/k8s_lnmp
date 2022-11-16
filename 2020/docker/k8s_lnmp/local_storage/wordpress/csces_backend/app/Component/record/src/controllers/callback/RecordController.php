<?php

namespace App\Component\record\src\controllers\callback;

use Vss\Common\Controllers\CallbackBaseController;
use App\Component\record\src\constants\RecordConstant;
use vhallComponent\room\constants\RoomConstant;

/**
 * RecordController extends CallbackBaseController
 *
 * @uses     yangjin
 * @date     2020-08-12
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class RecordController extends CallbackBaseController
{
    /**
     * Notes: 点播转直播开始
     * Author: michael
     * Date: 2019/10/14
     * Time: 16:38
     *
     */
    public function eventVODToLiveStart()
    {
        $data           = $this->params;
        $data['status'] = $data['status'] == 1 ? RoomConstant::STATUS_START : 0;
        $this->liveInfo($data);
        vss_service()->getRoomService()->startLive($data['room_id']);
    }

    /**
     * Notes: 点播转直播播放结束
     * Author: michael
     * Date: 2019/10/14
     * Time: 16:38
     *
     */
    public function eventVODToLiveStop()
    {
        $data           = $this->params;
        $data['status'] = RoomConstant::STATUS_STOP;
        $this->liveInfo($data);
        vss_service()->getRoomService()->endLive($data['room_id'], 4, ['dianbo' => true]);
    }

    /**
     * Notes: 更新房间直播状态
     * Author: michael
     * Date: 2019/10/14
     * Time: 16:39
     *
     * @param $data
     *
     * @return bool
     */
    public function liveInfo($data)
    {
        // 获取房间信息
        $roomInfo = vss_model()->getRoomsModel()->findByRoomId($data['room_id']);
        if (empty($roomInfo)) {
            vss_logger()->info('room-empty', ['params' => $data]);
        }
        // 更新房间直播状态
        $roomInfo->status = $data['status'];
        $saved            = $roomInfo->save();
        if (!$saved) {
            vss_logger()->info('live-save-fail', ['params' => $data]);
        } else {
            return true;
        }
    }

    /**
     * 下载回调
     * @throws Exception
     */
    public function eventMediaPackageComplete()
    {
        $params = $this->params;
        $data['status']=$params['status'];
        $data['vod_id']=$params['vod_id'];
        $data['app_id']=$params['app_id'];
        $data['download_url']=$params['download_url'];
        $key=$params['quality'] ? RecordConstant::RECORD_DOWN_QA_URL . $data['vod_id'] . ':' . $params['quality'] : RecordConstant::RECORD_DOWN_URL . $data['vod_id'];
        if (!empty($params['quality'])) {
            $data['quality']=$params['quality'];
        }
        if (!empty($data['download_url'])) {
            vss_redis()->set($key, $data['download_url'], 86400 * 6);
        }
        vss_logger()->info('event-down-end', $data);
    }


    public function eventCreateRecordComplete()
    {
        $params = $this->params;
        if ($params['status'] == 1) {
            $res = vss_model()->getRecordModel()->where('vod_id', $params['vod_id'])->first();

            if ($res) {
                $data['vod_id'] = $params['vod_id'];
                $data['app_id']    = vss_config('paas.apps.lite.appId');
                $result            = vss_service()->getRecordService()->info($data);
                if (!empty($result) && !empty($result['vod_info'])) {
                    $vod_info             = $result['vod_info'];
                    $res->duration = $vod_info['duration'] ? $vod_info['duration'] : 0;
                    $res->storage = $vod_info['storage'] ? $vod_info['storage'] : 0;
                    $res->update();
                }
            }
            vss_logger()->info('event-my', [$params]);
        }
    }


    public function eventSingleTranscodeComplete()
    {
    }

    /**
     * 转码成功
     * @return int
     */
    public function eventAllTranscodeComplete()
    {
        $params = $this->params;
        if ($params) {
            $res = vss_model()->getRecordModel()->where('vod_id', $params['vod_id'])->first();
            if ($res) {
                $res->transcode_status = $params['status'];


                $data['vod_id'] = $params['vod_id'];
                $data['app_id']    = vss_config('paas.apps.lite.appId');
                $result            = vss_service()->getRecordService()->info($data);
                //---------------------------------------
                if (!empty($result) && !empty($result['vod_info'])) {
                    $vod_info         = $result['vod_info'];
                    $res->duration = $vod_info['duration'] ? $vod_info['duration'] : 0;
                }
                $res->update();
                return true;
            }
            vss_logger()->info('event-alltrans-record', [$params]);
        }
    }

    /**
     *点播转直播播放结束
     */
    public function eventVODToLiveEnd()
    {
        $params = $this->params;
        vss_service()->getRoomService()->endLive($params['room_id'], 4, ['dianbo_end' => true]);
    }

    /**
     * 下载链接生成成功回调 download/created-success
     *
     * @see http://www.vhallyun.com/docs/show/1150.html
     * @throws Exception
     */
    public function eventDownloadCreatedSuccess()
    {
        $params = $this->params;

        if (isset($params['record_id']) && isset($params['download_url'])) {
            $key = 'records_' . $params['record_id'];
            vss_redis()->set($key, $params, 6*86400);
        }

        return true;
    }

    /**
     * 转码成功回调 record/trans-over
     *
     * @see http://www.vhallyun.com/docs/show/1059.html
     */
    public function eventRecordTransOver()
    {
    }

    /**
     * 点播文件生成回调 record/created-success
     *
     * @see http://www.vhallyun.com/docs/show/1058.html
     */
    public function eventRecordCreatedSuccess()
    {
    }
}
