<?php
/**
 * 剪辑
 * User: zhangshilong
 * Date: 2019/12/16
 * Time: 16:38
 */

namespace vhallComponent\cut\controllers\console;

use App\Constants\ResponseCode;
use Helper;
use vhallComponent\decouple\controllers\BaseController;
use vhallComponent\room\constants\RoomConstant;
use Exception;

class CutController extends BaseController
{
    /**
     * 获取点播列表
     * @throws Exception
     */
    public function listAction()
    {

        //1、获取参数信息
        $keywords = $this->getParam('keywords', 0);
        if (empty($keywords)) {
            $this->success([]);
        }
        $params = ['account_id' => $this->accountInfo['account_id'], 'keywords' => $keywords];
        //2.1、获取指定房间ID或者回放ID的信息
        $result = vss_service()->getCutService()->getList($params);

        if (isset($result['list']) && !empty($result['list'])) {
            $list = $result['list'];
            //2.2、获取房间活动ID
            $ilIdArr = array_column($list, 'il_id') ?? [];
            //2.3、获取房间信息
            $ilInfos = vss_service()->getRoomService()->getInfoByIlIds($ilIdArr);
            $tmp     = []; //存放房间信息容器
            foreach ($ilInfos as $ilinfo) {
                $tmp[$ilinfo['il_id']] = $ilinfo;
            }
            $appId = vss_service()->getTokenService()->getAppId();
            //2.4、组织数据格式
            foreach ($list as $cutKey => $cutInfo) {
                $ilDetail                         = $tmp[$cutInfo['il_id']];
                $list[$cutKey]['room_id']         = $ilDetail['room_id'];
                $list[$cutKey]['channel_id']      = $ilDetail['channel_id'];
                $list[$cutKey]['duration_second'] = $cutInfo['duration'];
                $list[$cutKey]['app_id']          = $appId;
                $list[$cutKey]['source_name']     = $this->source[$cutInfo['source']] ?? '';
                $list[$cutKey]['duration']        = Helper::secToTime($cutInfo['duration']);
            }
            $data = [
                'total'        => $result['total'],
                'per_page'     => $result['per_page'],
                'current_page' => $result['page_num'],
                'data'         => $list
            ];
            $this->success($data);
        }
        $this->fail(ResponseCode::EMPTY_LIST);
    }

    /**
     * 生成回放
     *
     * @throws Exception
     */
    public function mergeRecordAction()
    {
        validator($this->getParam(), [
            'il_id'      => 'required|number',
            'begin_time' => 'required|date',
            'end_time'   => 'required|date|after:begin_time',
        ]);

        //1、接收参数信息，包括时间段，以及活动id，且都为必须
        $ilId      = $this->getParam('il_id');
        $beginTime = $this->getParam('begin_time');
        $endTime   = $this->getParam('end_time');

        //2、合并
        //2.1、查看活动信息是否存在
        $condition           = [
            'account_id' => $this->accountInfo['account_id'],
            'il_id'      => $ilId,
        ];
        $interactiveLiveInfo = vss_service()->getRoomService()->getRow($condition);

        //2.2、判断房间号
        if (empty($interactiveLiveInfo['room_id'])) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }

        if ($interactiveLiveInfo['status'] === RoomConstant::STATUS_WAITING) {
            $this->fail(ResponseCode::BUSINESS_SOURCE_NOT_START);
        }

        //3、获取详情
        $params               = ['stream_id' => $interactiveLiveInfo['room_id']];
        $params['il_id']      = $ilId;
        $params['account_id'] = $this->accountInfo['account_id'];
        $params['start_time'] = $beginTime;
        $params['end_time']   = $endTime;

        $data = vss_service()->getRecordService()->mergeRecord($params);
        if ($data) {
            $this->success($data);
        }

        $this->fail(ResponseCode::BUSINESS_CREATE_FAILED);
    }

    /**
     * 获取点播详情
     *
     *
     * @throws Exception
     */
    public function getVodInfoAction()
    {

        //1、接收参数信息
        $recordId = $this->getParam('record_id', 0);
        //1.1、验证
        $params = ['vod_id' => $recordId];
        vss_validator($params, [
            'vod_id' => 'required'
        ]);

        //2、返回参数信息
        $data = vss_service()->getCutService()->getInfo($params);
        if ($data == -1) {
            $this->fail(ResponseCode::BUSINESS_CREATE_FAILED);
        } elseif ($data == -2) {
            $this->fail(ResponseCode::EMPTY_VOD);
        } elseif ($data) {
            $ilInfo                  = vss_service()->getRoomService()->getInfoByIlId($data['il_id']);
            $data['channel_id']      = $ilInfo['channel_id'];
            $data['duration_second'] = $data['duration'];
            $data['app_id']          = vss_service()->getTokenService()->getAppId();
            $data['source_name']     = $this->source[$data['source']] ?? '';
            $data['duration']        = Helper::secToTime($data['duration']);
            $this->success($data);
        } else {
            $this->fail(ResponseCode::EMPTY_VOD);
        }

        $this->success();
    }

    /**
     * 保存或导出剪辑信息
     *
     * @throws Exception
     */
    public function saveRecordAction()
    {
        //1、接收参数信息
        $params = $this->getParam();
        //1.1、验证
        vss_validator($params, [
            'vod_id'       => 'required',
            'cut_sections' => 'required',
            'account_id'   => 'required',
        ]);
        $params['il_id'] = $params['il_id'] ?? 0;
        //2、验证数据格式
        $cutSections    = $params['cut_sections'];
        $tmpCutSections = json_decode($cutSections, true);
        //2.1、裁剪格式有误
        if (!is_array($tmpCutSections)) {
            $this->fail(ResponseCode::TYPE_INVALID_CUT);
        }
        //2.2、裁剪格式有误
        $tmpData = [];
        if (!empty($tmpCutSections)) {
            foreach ($tmpCutSections as $tmpCutInfo) {
                if (!isset($tmpCutInfo['start']) || !isset($tmpCutInfo['end'])) {
                    $this->fail(ResponseCode::TYPE_INVALID_CUT);
                    break;
                }
                if ($tmpCutInfo['start'] > $tmpCutInfo['end']) {
                    $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
                    break;
                }
                if ($tmpCutInfo['start'] == $tmpCutInfo['end']) {
                    continue;
                }
                $tmpData[] = $tmpCutInfo;
            }
        }
        $params['cut_sections'] = json_encode($tmpData);
        //2.3、判断打点的信息格式
        $pointSections = $params['point_sections'];
        if (!empty($pointSections)) {
            $tmpPointSections = json_decode($pointSections, true);
            //2.4、裁剪格式有误
            if (!is_array($tmpPointSections)) {
                $this->fail(ResponseCode::TYPE_INVALID_CUT);
            }
            //2.5、裁剪格式有误
            foreach ($tmpPointSections as $tmpPointInfo) {
                if (!isset($tmpPointInfo['timePoint']) || !isset($tmpPointInfo['msg'])) {
                    $this->fail(ResponseCode::TYPE_INVALID_CUT);
                    break;
                }
            }
        }

        $data = vss_service()->getCutService()->saveRecord($params);
        if (!empty($data['data'])) {
            $this->success($data['data']);
        }

        $this->fail(ResponseCode::BUSINESS_CREATE_FAILED);
    }

    /**
     * @throws Exception
     */
    public function detailAction()
    {
        //1、接收参数信息
        $params = $this->getParam();
        //1.1、验证
        vss_validator($params, [
            'vod_id' => 'required',
        ]);
        //2、验证数据格式
        $data = vss_service()->getCutService()->getDetailByRecordId($params);
        $this->success($data);
    }
}
