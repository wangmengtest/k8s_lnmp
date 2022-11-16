<?php

namespace vhallComponent\broadcast\services;

use App\Constants\ResponseCode;
use GuzzleHttp\Client;
use Vss\Common\Services\WebBaseService;

/**
 * RebroadcastServiceTrait
 *
 * @uses     yangjin
 * @date     2020-08-13
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class RebroadcastService extends WebBaseService
{
    /**
     * 转播列表
     *
     * @param $params
     *
     * @return mixed
     *
     */
    public function lists($params)
    {
        vss_validator($params, [
            'room_id' => 'required',
            'subject' => '',
        ]);
        $page      = $params['curr_page'] ?? 1;
        $pageSize  = $params['page_size'] ?? 20;
        $roomInfo  = vss_model()->getRoomsModel()->findByRoomId($params['room_id']);

        $rebroadcast = vss_model()->getRebroadCastModel()->getStartRebroadcastByRoomId($params['room_id']);
        $lists       = vss_model()->getRoomsModel()->where([
            'status'     => 1,
            'account_id' => $roomInfo->account_id
        ])->where('room_id', '<>', $params['room_id']);

        if (!empty($rebroadcast->source_id)) {
            $lists  = $lists->where('id', '<>', $rebroadcast->source_room_id);
            $source = vss_model()->getRoomsModel()->findByRoomId($rebroadcast->source_room_id);
        }
        if (!empty($params['subject'])) {
            $lists = $lists->where('subject', 'like', '%' . $params['subject'] . '%');
        }
        $all_count         = $lists->count();
        $arr['total']      = $all_count;
        $arr['total_page'] = ceil($all_count / $pageSize);
        $arr['curr_page']  = $page;
        $curr_start        = ($page == 1) ? 0 : $pageSize * ($page - 1);
        $lists             = $lists->orderBy('created_at', 'desc')
            ->skip($curr_start)
            ->take($pageSize)
            ->get()->toArray();
        if (!empty($source) && $page == 1) {
            $sourceArr[] = $source->toArray();
            $lists       = array_merge($sourceArr, $lists);
        }
        foreach ($lists as &$l) {
            $l['isStream'] = 0;
            if (!empty($rebroadcast->source_room_id) && $rebroadcast->source_room_id == $l['room_id']) {
                $l['isStream'] = 1;
            }
        }
        $arr['list'] = $lists;
        return $arr;
    }

    /**
     * 转播预览
     *
     * @param $params
     *
     * @return array
     *
     */
    public function preview($params)
    {
        vss_validator($params, [
            'source_room_id'      => 'required',
            'third_party_user_id' => 'required'
        ]);

        $sourceInfo = vss_model()->getRoomsModel()->findByRoomId($params['source_room_id']);

        # vhallEOF-document-rebroadcastService-preview-1-start
        
        try {
            $watch_info = vss_service()->getDocumentService()->watchInfo([
                "app_id"  => $sourceInfo->app_id,
                "channel" => $sourceInfo->channel_id
            ]);
        } catch (\Exception $e) {
            $watch_info["list"] = [];
        }

        # vhallEOF-document-rebroadcastService-preview-1-end

        $documentUrl = '';
        if (!empty($watch_info['list'])) {
            $info = $watch_info['list'][0];
            if (isset($info['switch_status']) && $info['switch_status'] == 1) {
                $documentUrl = vss_config('documentUrl') . '/' . $info['hash'] . '/' . (intval($info['show_page']) + 1) . '.jpg';
            }
        }
        $roomInfo = vss_service()->getRoomService()->get($params['source_room_id'], $params['third_party_user_id'])->toArray();
        if (!empty($roomInfo)) {
            $roomInfo['document_url'] = $documentUrl;
        }
        return $roomInfo;
    }

    /**
     * 转播开始
     *
     * @param $params
     *
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function start($params)
    {
        vss_validator($params, [
            'room_id'        => 'required',
            'source_room_id' => 'required'
        ]);
        if ($params['room_id'] == $params['source_room_id']) {
            $this->fail(ResponseCode::BUSINESS_SOURCE_ERROR);
        }
        $roomInfo   = vss_model()->getRoomsModel()->findByRoomId($params['room_id']);
        $sourceInfo = vss_model()->getRoomsModel()->findByRoomId($params['source_room_id']);
        if ($sourceInfo->status === 0) {
            $this->fail(ResponseCode::BUSINESS_SOURCE_NOT_START);
        }
        if ($sourceInfo->status === 2) {
            $this->fail(ResponseCode::BUSINESS_SOURCE_END);
        }
        $isExistSource = vss_model()->getRebroadCastModel()->getStartRebroadcastByRoomId($params['source_room_id']);
        if ($isExistSource) {
            $this->fail(ResponseCode::BUSINESS_NOT_REPEAT);
        }
        $stream = vss_model()->getRebroadCastModel()->setRebroadcast($params['room_id'], $params['source_room_id'], 1);
        if (empty($stream->id)) {
            $this->fail(ResponseCode::BUSINESS_SET_FAILED);
        }
        $pullStream = $this->pullStreame($params['room_id'], $params['source_room_id'], $stream->id, 'start');
        $this->setChannel($sourceInfo);
        $data = [
            'type'       => 'live_broadcast_start',
            'channel_id' => $sourceInfo->channel_id
        ];
        vss_service()->getPaasChannelService()->sendMessageByChannel($roomInfo->channel_id, $data);

        # vhallEOF-document-rebroadcastService-1-start
        
        try {
            $watch_info = vss_service()->getDocumentService()->watchInfo([
                "app_id"  => $sourceInfo->app_id,
                "channel" => $sourceInfo->channel_id
            ]);
        } catch (\Exception $e) {
            $watch_info["list"] = [];
        }


        # vhallEOF-document-rebroadcastService-1-end

        if (!empty($watch_info['list'])) {
            $info     = $watch_info['list'][0];
            $uniqueID = time();
            $params   = [
                'hash'        => $info['hash'],
                'uniqueID'    => $uniqueID,
                'currentPage' => $info['show_page'],
                'page'        => $info['page'],
                'currentStep' => $info['show_step'],
                'step'        => 0,
                'cw'          => $info['cw'],
                'ch'          => $info['ch'],
                'width'       => $info['width'],
                'height'      => $info['height'],
                'cid'         => $info['cid'],
                'ext'         => $info['ext'],
                'version'     => '1.1.0',
                'doc_type'    => $info['doc_type'],
                'docId'       => $info['docId'],
                'pf'          => 'js'
            ];
            //创建消息
            vss_service()->getPaasChannelService()->sendMessageByChannel($roomInfo->channel_id, $params + [
                'flipOver' => 0,
                'command'  => [
                    [
                        'type'     => 'create',
                        'data'     => [
                            'id'              => $info['cid'],
                            'width'           => $info['width'],
                            'height'          => $info['height'],
                            'backgroundColor' => $info['backgroundColor'],
                            'type'            => $info['is_board'] == 2 ? 'board' : 'document'
                        ],
                        'stamp'    => $uniqueID,
                        'event'    => 'operate',
                        'op'       => 'create',
                        'is_board' => $info['is_board']
                    ]
                ],
            ], $roomInfo->account_id, 'service_document', 'pc_browser', 0);
            //选容器
            vss_service()->getPaasChannelService()->sendMessageByChannel($roomInfo->channel_id, $params + [
                'flipOver' => 0,
                'command'  => [
                    [
                        'type'     => 'active',
                        'data'     => [
                            'id' => $info['cid']
                        ],
                        'stamp'    => $uniqueID,
                        'event'    => 'operate',
                        'op'       => 'active',
                        'is_board' => $info['is_board']
                    ]
                ],
            ], $roomInfo->account_id, 'service_document', 'pc_browser', 0);
            //翻页
            vss_service()->getPaasChannelService()->sendMessageByChannel($roomInfo->channel_id, $params + [
                'flipOver' => 1,
                'command'  => [
                    [
                        'info'     => [
                            'slidesTotal' => $info['page'],
                            'slideIndex'  => $info['show_page'],
                            'pageHash'    => $info['hash'] . '/' . $info['show_page'],
                            'hash'        => $info['hash'],
                            'stepsAll'    => [0],
                            'stepsTotal'  => 0,
                            'docType'     => $info['doc_type'] == 2 ? 'jpg' : 'ppt',
                        ],
                        'cid'      => $info['cid'],
                        'params'   => [
                            'slideIndex'  => $info['show_page'],
                            'slidesTotal' => $info['page'],
                            'stepsTotal'  => 0,
                            'stepIndex'   => $info['show_step']
                        ],
                        'data'     => [
                            'id' => $info['cid']
                        ],
                        'stamp'    => $uniqueID,
                        'event'    => 'onSlideChange',
                        'op'       => 'filpover',
                        'is_board' => $info['is_board']
                    ]
                ],
            ], $roomInfo->account_id, 'service_document', 'pc_browser', 0);
            //开关
            vss_service()->getPaasChannelService()->sendMessageByChannel($roomInfo->channel_id, $params + [
                'flipOver' => 0,
                'command'  => [
                    [
                        'type'     => $info['switch_status'] == 1 ? 'switchon' : 'switchoff',
                        'data'     => [
                            'id' => $info['cid']
                        ],
                        'stamp'    => $uniqueID,
                        'event'    => 'operate',
                        'op'       => $info['switch_status'] == 1 ? 'switchon' : 'switchoff',
                        'is_board' => $info['is_board']
                    ]
                ],
            ], $roomInfo->account_id, 'service_document', 'pc_browser', 0);
        }
        return true;
    }

    /**
     * 转播结束
     *
     * @param $params
     *
     * @return int|mixed|void|null
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function stop($params)
    {
        vss_validator($params, [
            'room_id'        => 'required',
            'source_room_id' => 'required'
        ]);
        if ($params['room_id'] == $params['source_room_id']) {
            $this->fail(ResponseCode::BUSINESS_SOURCE_ERROR);
        }
        $roomInfo   = vss_model()->getRoomsModel()->findByRoomId($params['room_id']);
        $sourceInfo = vss_model()->getRoomsModel()->findByRoomId($params['source_room_id']);

        $stream = vss_model()->getRebroadCastModel()->setRebroadcast($params['room_id'], $params['source_room_id'], 0);
        if (empty($stream->id)) {
            $this->fail(ResponseCode::BUSINESS_SET_FAILED);
        }
        $pullStream = $this->pullStreame($params['room_id'], $params['source_room_id'], $stream->id, 'stop');
        $this->setChannel($sourceInfo);
        $data = [
            'type'       => 'live_broadcast_stop',
            'channel_id' => $sourceInfo->channel_id
        ];
        return vss_service()->getPaasChannelService()->sendMessageByChannel($roomInfo->channel_id, $data);
    }

    /**
     * 流媒体拉流
     *
     * @param        $targetId
     * @param        $sourceId
     * @param        $sessionId
     * @param string $command
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pullStreame($targetId, $sourceId, $sessionId, $command = 'start')
    {
        $url   = vss_config('domain.services.stream') . '/api/relaystream/';
        $param = [
            'sessionid'       => (string)$sessionId,
            'command'         => $command,
            'source_streamid' => (string)$sourceId,
            'target_streamid' => (string)$targetId,
            'bu'              => '1'
        ];
        $start = microtime();
        //调用流媒体接口进行转播
        $client = new Client();
        $result = $client->request('post', $url, [
            'headers' => [
                'content-type' => 'application/json'
            ],
            'body'    => json_encode($param)
        ]);
        $result = json_decode($result->getBody()->getContents(), true);
        $end    = microtime();
        vss_logger()->info(
            'pullStreame',
            ['url' => $url, '$param' => $param, 'result' => $result, 'start' => $start, 'end' => $end]
        );
        if ($result['code'] == 100) {
            return true;
        }
        return false;
    }

    public function setChannel($sourceInfo)
    {
        $roomIdArr    = vss_model()->getRebroadCastModel()->where([
            'source_room_id' => $sourceInfo->room_id,
            'status'         => 1
        ])->pluck('room_id')->toArray();
        $channelIdArr = [];
        if ($roomIdArr) {
            $channelIdArr = vss_model()->getRoomsModel()
                ->whereIn('room_id', $roomIdArr)
                ->pluck('room_id', 'channel_id')
                ->toArray();
        }
        if ($channelIdArr) {
            return vss_service()->getPaasService()->setTargetChannel($sourceInfo->channel_id, json_encode($channelIdArr));
        }
        return vss_service()->getPaasService()->resetTargetChannel($sourceInfo->channel_id);
    }
}
