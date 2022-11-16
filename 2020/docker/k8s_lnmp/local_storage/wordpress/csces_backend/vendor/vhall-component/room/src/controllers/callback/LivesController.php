<?php

namespace vhallComponent\room\controllers\callback;

use App\Constants\ResponseCode;
use Exception;
use Vss\Common\Controllers\CallbackBaseController;
use vhallComponent\room\constants\RoomConstant;
use Vss\Exceptions\CallbackException;

/**
 * RoomControllerTrait
 *
 * @uses     yangjin
 * @date     2020-09-09
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class LivesController extends CallbackBaseController
{
    /**
     * 直播房间流状态回调 lives/stream-change-status
     *
     * @see http://www.vhallyun.com/docs/show/1502.html
     *
     */
    public function eventLivesStreamChangeStatus()
    {
        $this->syncLiveInfo();
    }

    /**
     * 同步直播房间信息（主动拉取状态）
     * 同步信息包括：开始推流时间、结束推流时间、推流状态
     *
     * @see    http://www.vhallyun.com/docs/show/1506.html
     * @author ensong.liu@vhall.com
     * @date   2019-01-19 12:52:34
     * @return void
     *
     * @throws Exception
     */
    public function syncLiveInfo()
    {
        $roomId = $this->params['room_id'];
        vss_logger()->info('change-oov', ['fix' => $this->params]);
        //直播房间信息
        $interactiveLiveInfo = vss_model()->getRoomsModel()->where('room_id', $roomId)->first();
        vss_logger()->info('changeStreamStatus', ['ssgetStreamInfos' => $interactiveLiveInfo]);
        if (empty($interactiveLiveInfo)) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }
        //拉取paas流状态列表
        $streamStatusList = vss_service()->getPaasService()->getStreamStatus($roomId);
        if (empty($streamStatusList) || !is_array($streamStatusList) || !isset($streamStatusList[$roomId])) {
            $this->fail(ResponseCode::BUSINESS_GET_LIVE_STREAM_FAILED);
        }
        //当前房间流状态信息
        $streamStatusInfo = $streamStatusList[$roomId];
        if (!is_array($streamStatusInfo)) {
            $this->fail(ResponseCode::BUSINESS_LIVE_STREAM_FORMAT_ERROR);
        }
        vss_logger()->info('change-oov', ['getStreamInfos' => $streamStatusInfo]);
        //更新房间信息
        switch ($streamStatusInfo['stream_status']) {
            case RoomConstant::STATUS_START:
                vss_logger()->info('changeStreamStatus', ['开始直播']);
                $interactiveLiveInfo->begin_live_time = $streamStatusInfo['push_time'];
                $interactiveLiveInfo->status          = RoomConstant::STATUS_START;
                $saved                                = $interactiveLiveInfo->update();
                break;
            case RoomConstant::STATUS_STOP:
                vss_logger()->info('changeStreamStatus', ['结束直播']);
                //直播结束，更新该房间下的，聊天总数
                $messageStatResult                  = vss_service()->getPaasChannelService()
                    ->getMessageCountAndUserCount($interactiveLiveInfo['room_id']);
                $interactiveLiveInfo->message_total = $messageStatResult['count'] ?? 0;

                # vhallEOF-record-liveCallback-syncLiveInfo-1-start
        
                vss_service()->getRecordService()->mergeRecord([
                    "stream_id"  => $interactiveLiveInfo->room_id,
                    "start_time" => $interactiveLiveInfo->begin_live_time,
                    "end_time"   => $streamStatusInfo["end_time"],
                    "il_id"      => $interactiveLiveInfo->il_id,
                    "account_id" => $interactiveLiveInfo->account_id,
                    "source"     => 0
                ]);

        # vhallEOF-record-liveCallback-syncLiveInfo-1-end

                $interactiveLiveInfo->end_live_time = $streamStatusInfo['end_time'];
                $saved                              = $interactiveLiveInfo->update();
                break;
            default:
                $saved = true;
        }

        if (!$saved) {
            $this->fail(ResponseCode::BUSINESS_EDIT_FAILED);
        }
    }

    public function eventLivesPull_stream_status()
    {
        $params = $this->params;
        vss_validator($params, [
            'status'    => 'required',
            'room_id'   => 'required',
            'config_id' => 'required',
        ]);

        if ($params['status'] == 2) {
            return true;
        }

        if ($params['status'] == 3) {
            $this->fail(ResponseCode::BUSINESS_PULL_STREAM_FAILED);
        }
        $key = 'pull_stream_' . $params['room_id'];
        vss_redis()->set($key, $params['config_id']);
        vss_redis()->persist($key);
        if (!vss_service()->getRoomService()->startLive($params['room_id'], 4)) {
            $this->fail(ResponseCode::BUSINESS_START_LIVE_FAILED);
        }

        vss_logger()->info('back-pull-list', [$params]);
    }

    /**
     * 获取事件方法名
     *
     * @return string
     */
    protected function getEventMethodName(): string
    {
        $event = preg_replace(['/\//', '/-/'], ' ', $this->params['event']);

        return sprintf('event%s', str_replace(' ', '', ucwords($event)));
    }
}
