<?php

namespace vhallComponent\record\services;

use App\Constants\ResponseCode;
use Exception;
use vhallComponent\record\constants\RecordConstant;
use Vss\Common\Services\WebBaseService;

/**
 * RecordServiceTrait
 *
 * @uses     yangjin
 * @date     2020-08-12
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class RecordService extends WebBaseService
{
    /**
     * 列表
     *
     * @param $param
     *
     * @return mixed
     */
    public function getList($param)
    {
        $param['status'] = RecordConstant::STATUS_YES;
        if (isset($param['start_time']) && !empty($param['start_time'])) {
            $param['end_time'] = !empty($param['end_time']) ? $param['end_time'] : date('Y-m-d H:i:s');
        }

        return vss_model()->getRecordModel()->setPerPage($param['page_size'])->getList($param, [], $param['page_num']);
    }

    /**
     * 重命名
     *
     * @param $params
     *
     * @return mixed
     *
     */
    public function rename($params)
    {
        $data['name']   = $params['name'];
        $data['vod_id'] = $params['record_id'];

        $res = vss_model()->getRecordModel()->getRow(['vod_id' => $data['vod_id']]);
        if (empty($res)) {
            $this->fail(ResponseCode::EMPTY_VIDEO);
        }

        vss_model()->getRecordModel()->updateRow($res->id, $data);

        return vss_service()->getPaasService()->changeName($data);
    }

    /**
     * 详情
     *
     * @param $params
     *
     * @return mixed
     */
    public function info($params)
    {
        $data['app_id']             = $params['app_id'] ?? vss_config('paas.apps.lite.appId');
        $data['vod_id']             = $params['vod_id'];
        $data['acquire_video_info'] = 1;
        $info                       = vss_service()->getPaasService()->recordInfo($data);
        $prekey                     = RecordConstant::RECORD_DOWN_QA_URL . $data['vod_id'];
        array_walk($info['video_info'], function (&$value) use ($prekey) {
            $value['down_url'] = vss_redis()->get($prekey . ':' . $value['quality']);
        });
        return $info;
    }

    /**
     * 删除
     *
     * @param $params
     *
     * @return mixed
     *
     */
    public function del($params)
    {
        $data['app_id'] = vss_service()->getTokenService()->getAppId();
        $data['vod_id'] = $params['record_id'];

        $ids = explode(',', $params['record_id']);

        $ret = vss_model()->getRecordModel()->deleteIds($ids);

        if (empty($ret)) {
            $this->fail(ResponseCode::BUSINESS_DELETE_FAILED);
        }

        return vss_service()->getPaasService()->recordDel($data);
    }

    /**
     * @param $params
     *
     * @return bool|mixed
     */
    public function createRecord($params)
    {
        vss_validator($params, [
            'vod_id' => 'required',
        ]);
        if ($params) {
            $data['account_id'] = $params['account_id'];
            $data['il_id']      = $params['il_id'];
            $data['vod_id']     = $params['vod_id'];
            $data['name']       = $params['name'];
            $data['source']     = $params['source'];
            //获取视频详情
            $result = $this->info($params);
            vss_logger()->info('mergeRecordLink4', [$result, $params]);

            if (!empty($result) && !empty($result['vod_info'])) {
                $vod_info                 = $result['vod_info'];
                $data['name']             = $data['name'] ?? $vod_info['name'];
                $data['created_time']     = $vod_info['created_at'];
                $data['source']           = $data['source'] ?? $vod_info['source'];
                $data['transcode_status'] = $vod_info['transcode_status'];
                $data['storage']          = $vod_info['storage'];
                $data['duration']         = $vod_info['duration'];
                $data['room_id']          = $vod_info['room_id'];
            } else {
                vss_logger()->info('record-back-error', ['param' => $params, 'info' => $result]);

                return false;
            }
            if (!vss_model()->getRecordModel()->create($data)->toArray()) {
                vss_logger()->info('record-save-error', $data);

                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * 手动合并回放--生成
     *
     * @param $params
     *
     * @return mixed
     */
    public function mergeRecord($params)
    {
        //1、获取参数信息
        $data['stream_id']  = $params['stream_id'];
        $data['start_time'] = $params['start_time'];
        $data['end_time']   = $params['end_time'];
        $data['instant']    = 1;

        //2、获取生成信息
        $mergeInfo = vss_service()->getPaasService()->submitCreateRecordTask($data);
        if (!empty($mergeInfo)) {
            //3、添加生成的信息至数据库中
            $field['room_id']    = $params['stream_id'];
            $field['account_id'] = $params['account_id'];
            $field['il_id']      = $params['il_id'];
            $field['source']     = isset($params['source']) ? 0 : 10; //合成
            $field['vod_id']     = $mergeInfo['vod_id']; //合成
            if ($field['source'] < 1) {
                $field['name'] = $field['room_id'] . '' . $data['start_time'];
            }
            $createRe = $this->createRecord($field);
            if ($createRe) {
                $result = vss_model()->getRecordModel()->getInfoByVodId($mergeInfo['vod_id']);
                return $result;
            }
        }

        return [];
    }

    /**
     *  列表
     * http://www.vhallyun.com/docs/show/481.html
     *
     * @param $param
     *
     * @return mixed
     */
    public function __getList($param)
    {
        $data['app_id']    = $param['app_id'];
        $data['room_id']   = !empty($param['room_id']) ? $param['room_id'] : '';
        $data['page_num']  = !empty($param['page_num']) ? $param['page_num'] : '';
        $data['page_size'] = !empty($param['page_size']) ? $param['page_size'] : 100;
        if (!empty($param['starttime'])) {
            $data['starttime'] = $param['starttime'];
        }
        if (!empty($param['endtime'])) {
            $data['endtime'] = $param['endtime'];
        }
        if (isset($param['source']) && $param['source'] != '') {
            $data['source'] = $param['source'];
        }
        if (!empty($param['status'])) {
            $data['status'] = $param['status'];
        }
        $data['sortby'] = !empty($param['sortby']) ? $param['sortby'] : 'created_at:desc';
        if (!empty($param['search'])) {
            $data['search'] = $param['search'];
            unset($data['page_num']);
        }

        return vss_service()->getPaasService()->getRecordList($data);
    }

    /**
     * 下载
     *
     * @param $params
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function down($params)
    {
        $data['app_id'] = $params['app_id'];
        $data['vod_id'] = $params['id'];
        $key            = RecordConstant::RECORD_DOWN_URL . $data['vod_id'];
        // type 为 cache 时直接读取缓存
        $url = vss_redis()->get($key);
        if (empty($url) && $params['type'] != 'cache') {
            vss_service()->getPaasService()->download($data);
            sleep(3);
            $url = vss_redis()->get($key);
        }

        if ($url) {
            return ['down_url' => $url];
        }
        $this->fail(ResponseCode::BUSINESS_DOWNLOADING);

        return true;
    }

    /**
     * 回调
     *
     * @param $params
     *
     * @return bool
     */
    public function event($params)
    {
        if ($params) {
            if ($params['event'] == $this->event[0]) {
                vss_model()->getRecordModel()
                    ->where(['record_id' => $params['vod_id']])
                    ->update(['status' => $params['status']]);
                vss_logger()->info('CreateRecordComplete-update', $params);

                return true;
            }
            if ($params['event'] == $this->event[1]) {
                $result = vss_model()->getRecordModel()->where(['record_id' => $params['vod_id']])->get()->first();
                if (!$result) {
                    vss_logger()->info('MediaPackageComplete-down-error', $params);

                    return false;
                }
                if ($params['status'] == 1) {
                    //todo 打包完成
                    /**
                     * 1.调用 https://doc.vhallyun.com/docs/show/483接口
                     * 2.接口返回地址url
                     */
                }
                //下载失败
            }
        }
        vss_logger()->info('event-empty', $params);

        return false;
    }

    /**
     * Quality下载
     *
     * @param $params
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function downQuality($params)
    {
        $data['app_id']  = $params['app_id'];
        $data['vod_id']  = $params['id'];
        $data['quality'] = $params['quality'];
        // type 为 cache 时直接读取缓存
        $key = RecordConstant::RECORD_DOWN_QA_URL . $data['vod_id'] . ':' . $data['quality'];
        $url = vss_redis()->get($key);
        if (empty($url) && $params['type'] != 'cache') {
            vss_service()->getPaasService()->downQuality($data);
            sleep(3);
            $url = vss_redis()->get($key);
        }
        if ($url) {
            return ['down_url' => $url];
        }
        $this->fail(ResponseCode::BUSINESS_DOWNLOADING);

        return true;
    }

    /**
     * 获取回放总时长
     *
     * @param $params
     *
     * @return int
     *
     */
    public function getRecordDurationSum($params)
    {
        $condition = [];
        $params['il_id'] && $condition['il_id'] = $params['il_id'];
        $params['account_id'] && $condition['account_id'] = $params['account_id'];
        $params['room_id'] && $condition['room_id'] = $params['room_id'];
        $params['source'] && $condition['source'] = $params['source'];
        $params['status'] && $condition['status'] = $params['status'];
        $params['begin_time'] && $condition['begin_time'] = $params['begin_time'];
        $params['end_time'] && $condition['end_time'] = $params['end_time'];
        if (empty($condition)) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        $duration = vss_model()->getRecordModel()->getDurationSum($condition);
        $duration = $duration ? $duration : 0;
        return $duration;
    }

    /**
     *
     * @param $params
     *
     * @return mixed
     */
    public function videoDel($params)
    {
        $data['app_id']   = $params['app_id'];
        $data['vod_id']   = $params['id'];
        $data['video_id'] = $params['video_id'];

        return vss_service()->getPaasService()->videoDel($data);
    }

    /**
     * 回放下上传的音频列表
     *
     * @param $params
     *
     * @return array
     * @author  jin.yang@vhall.com
     * @date    2020-08-12
     */
    public function getRecordListByConsole($params)
    {
        $roomInfo = vss_service()->getRoomService()->getRow(['il_id' => $params['il_id']]);
        $recordId = $roomInfo['record_id']; //默认回放id
        $page     = $params['page'] ? $params['page'] : 1;
        $pagesize = $params['pagesize'] ? $params['pagesize'] : 10;

        //同步一下回放信息
        vss_model()->getRecordModel()->syncRecords($params['il_id']);

        //获取音频信息
        $vodCondition = [
            'il_id'      => $params['il_id'],
            'account_id' => $roomInfo['account_id'],
            'source_in'  => [0, 10, 11]
        ];
        $roomVodList  = vss_model()->getRecordModel()->setPerPage($pagesize)->getList($vodCondition, [], $page);
        $roomVodList  = $roomVodList->toArray();
        if (!empty($roomVodList['data'])) {
            foreach ($roomVodList['data'] as $key => $roomVod) {
                $tmpData['il_id']          = $params['il_id'];
                $tmpData['created_time']   = $roomVod['created_at'];
                $tmpData['sort_date']      = strtotime($roomVod['created_at']);
                $tmpData['duration']       = \Helper::secToTime($roomVod['duration']);
                $tmpData['storage']        = \Helper::calc($roomVod['storage']);
                $tmpData['is_default']     = $roomVod['vod_id'] == $recordId ? 1 : 0;
                $tmpData['record_id']      = $roomVod['vod_id'];
                $tmpData['record_name']    = $roomVod['name'];
                $tmpData['account_id']     = $roomVod['account_id'];
                $tmpData['document_exist'] = 0;
                $roomVodList['data'][$key] = $tmpData;
            }
        }

        return $roomVodList;
    }

    /**
     * 修改默认回放
     *
     * @param $ilId
     * @param $recordId
     *
     * @return bool
     *
     * @author  jin.yang@vhall.com
     * @date    2020-09-08
     */
    public function setDefaultRecord($params)
    {
        //参数列表
        $condition = vss_validator($params, [
            'il_id'     => 'required',
            'record_id' => 'required',
        ]);

        $ilId     = $condition['il_id'];
        $recordId = $condition['record_id'];

        $recordInfo = vss_model()->getRecordModel()->getRow($condition);
        if (empty($recordInfo)) {
            $this->fail(ResponseCode::EMPTY_RECORD);
        }

        //更新默认回放信息
        $attributes = [
            'record_id' => vss_model()->getRoomsModel()->getCount($condition) > 0 ? '' : $recordId,
        ];
        if (vss_model()->getRoomsModel()->updateRow($ilId, $attributes) == false) {
            $this->fail(ResponseCode::BUSINESS_EDIT_FAILED);
        }

        return true;
    }

    /**
     * 获取点播统计信息
     *
     * @param         $accountId
     * @param int     $ilId      互动房间ID值
     * @param string  $beginTime 开始时间
     * @param string  $endTime   截止时间
     *
     * @return array
     */
    public function recordData($accountId, $ilId, $beginTime, $endTime, $source)
    {
        //筛选条件
        $condition = [
            'il_id'      => $ilId,
            'begin_time' => $beginTime,
            'end_time'   => $endTime,
        ];
        if ($accountId) {
            $condition['account_id'] = $accountId;
        }

        //累计观众人数
        $uvStat = vss_model()->getRecordAttendsModel()->getUvCount($condition);

        //累计观看次数
        $pvStat = vss_model()->getRecordAttendsModel()->getPvCount($condition);

        //回放总时长
        $totalTime = vss_model()->getRecordAttendsModel()->getTotalTime($condition);
        //平均
        $avgWatchTime = $uvStat > 0 ? $totalTime / $uvStat : 0;

        // 回放时长， 直播生成的回放视频总时长
        $recordCondition = array_merge($condition, [
            'source' => 0, // 只查询回放记录
        ]);
        $videoTotalTime  = vss_model()->getRecordModel()->getDurationSum($recordCondition);

        //返回数据
        return [
            'live_uv'        => [
                'stat'   => $uvStat,
                'unit'   => '人',
                'export' => 'uv',
            ],
            'live_pv'        => [
                'stat'   => $pvStat,
                'unit'   => '次',
                'export' => 'pv',
            ],
            // 观看时长
            'watch_time'     => [
                'stat'   => ceil($totalTime / 60), // 结果向上取整
                'unit'   => '分钟',
                'export' => '',
            ],
            // 人均观看时长
            'avg_watch_time' => [
                'stat'   => $avgWatchTime < 0 ? 0 : ceil($avgWatchTime / 60),
                'unit'   => '分钟',
                'export' => '',
            ],
            // 回放时长， 直播生成的回放视频总时长
            'video_time'     => [
                'stat'   => ceil($videoTotalTime / 60),
                'unit'   => '分钟',
                'export' => '',
            ],
        ];
    }
}
