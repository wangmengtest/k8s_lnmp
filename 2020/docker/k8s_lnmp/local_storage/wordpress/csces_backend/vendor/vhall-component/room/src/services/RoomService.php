<?php

namespace vhallComponent\room\services;

use App\Constants\ResponseCode;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use vhallComponent\room\constants\CachePrefixConstant;
use vhallComponent\room\constants\InavGlobalConstant;
use vhallComponent\room\constants\RoomConstant;
use vhallComponent\room\models\DibblingModel;
use vhallComponent\room\models\RoomAttendsModel;
use vhallComponent\room\models\RoomJoinsModel;
use vhallComponent\room\models\RoomsModel;
use Vss\Common\Services\WebBaseService;
use Vss\Exceptions\ValidationException;

/**
 * RoomServiceTrait
 *
 * @author   jin.yangjin@vhall.com
 * @uses     yangjin
 * @date     2020-08-07
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class RoomService extends WebBaseService
{
    private $auditArr = [2 => '审核通过', 3 => '审核驳回']; //2--审核通过；3--审核驳回

    /**
     * 创建房间
     *
     * @param array $params
     *
     * @return RoomsModel|false
     * @throws \Throwable
     *
     * @author ensong.liu@vhall.com
     * @date   2019-06-09 17:42:30
     */
    public function create(array $params)
    {
        unset($params['vod_id']);

        if (empty($params['room_id']) || empty($params['inav_id']) ||
            empty($params['channel_id']) || empty($params['nify_channel'])) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }

        //创建数据
        $roomModel = vss_model()->getRoomsModel()->create($params);

        return $roomModel ?: false;
    }

    /**
     * Notes: 创建点播
     * Author: michael
     * Date: 2019/10/25
     * Time: 13:38
     *
     * @param $params
     *
     * @return DibblingModel
     */
    public function createDibbling($params)
    {
        // 修改房间状态
        $model            = vss_model()->getRoomsModel()->findByRoomId($params['room_id']);
        $model->status    = RoomConstant::STATUS_START;
        $model->live_type = 2;
        $model->save();

        $date = date('Y-m-d H:i:s');
        // 创建点播转直播记录
        $data = [
            'room_id'    => $params['room_id'],
            'is_delete'  => 0,
            'start_time' => $date,
            'created_at' => $date,
            'vod_id'     => $params['vod_id'],
        ];
        return vss_model()->getDibblingModel()->create($data);
    }

    /**
     * 删除房间
     *
     * @param bool $force
     *
     * @param string $roomId
     *
     * @return bool
     * @author ensong.liu@vhall.com
     * @date   2019-06-09 17:47:54
     *
     */
    public function delete($ilId, $force = false)
    {
        $roomInfo = vss_model()->getRoomsModel()->find($ilId);
        if ($force === true) {
            return $roomInfo->forceDelete() > 0;
        }
        return $roomInfo->delete() > 0;
    }

    /**
     * @param $params
     *
     * @return mixed
     *
     * @author  jin.yang@vhall.com
     * @date    2020-08-12
     */
    public function delelteByConsole($params)
    {
        $room = $this->getRow($params);

        if ($room->status == RoomConstant::STATUS_START) {
            $this->fail(ResponseCode::BUSINESS_LIVING_NOT_DELETE);
        }

        //软删除
        if (vss_service()->getRoomService()->delete($room->il_id, false) == false) {
            $this->fail(ResponseCode::BUSINESS_DELETE_FAILED);
        }
        $key = RoomConstant::WARM_INFO . $params['il_id'];
        return vss_redis()->del([$key]);
    }

    /**
     * 批量删除房间
     *
     * @param $ilIds
     *
     * @author  jin.yang@vhall.com
     * @date    2020-09-07
     */
    public function deleteByIds($ilIds)
    {

        //遍历删除房间
        $data     = [];
        $ilIdList = explode(',', $ilIds);
        $count    = vss_model()->getRoomsModel()->getIlIdsCount($ilIdList, ['status' => RoomConstant::STATUS_START]);
        if ($count) {
            $this->fail(ResponseCode::BUSINESS_LIVING_NOT_DELETE);
        }
        foreach ($ilIdList as $ilId) {
            $condition = [
                'il_id' => $ilId,
            ];
            $roomInfo  = vss_model()->getRoomsModel()->getRow($condition);
            if ($roomInfo && $roomInfo['status'] != RoomConstant::STATUS_START && $roomInfo->delRow($ilId, true)) {
                array_push($data, $roomInfo['il_id']);
                # vhallEOF-anchormanage-roomService-get-1-start
        
                //取消关联主播
                vss_service()->getAnchorManageService()->deleteLink($ilId);

        # vhallEOF-anchormanage-roomService-get-1-end
            }
        }

        return $data;
    }

    /**
     * 更新房间
     *
     * @param array $params
     *
     * @return bool
     *
     */
    public function update(array $params)
    {
        $room = $this->getRow([
            'il_id'      => $params['il_id'],
            'account_id' => $params['account_id'],
        ]);

        if (!$room) {
            return false;
        }

        if (isset($params['room_id'])) {
            unset($params['room_id']);
        }

        unset($params['il_id']);
        unset($params['account_id']);

        return $room->update($params);
    }

    /**
     * 获取房间信息
     *
     * @param string $roomId
     * @param string $thirdPartUid
     *
     * @return RoomsModel|null|false
     *
     */
    public function get($roomId, $thirdPartUid = '')
    {
        $roomModel = vss_model()->getRoomsModel()->findByRoomId($roomId);
        if (empty($roomModel)) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }
        if ($roomModel && $thirdPartUid) {
            $access_token = vss_service()->getPaasService()->baseCreateAccessToken([
                'third_party_user_id'  => $thirdPartUid,
                'publish_stream'       => $roomModel['room_id'],
                'chat'                 => $roomModel['channel_id'],
                'operate_document'     => $roomModel['channel_id'],
                'kick_inav'            => $roomModel['inav_id'],
                'publish_inav_stream'  => $roomModel['inav_id'],
                'kick_inav_stream'     => $roomModel['inav_id'],
                'askfor_inav_publish'  => $roomModel['inav_id'],
                'audit_inav_publish'   => $roomModel['inav_id'],
                'publish_inav_another' => $roomModel['inav_id'],
                'apply_inav_publish'   => $roomModel['inav_id'],
                'data_collect_manage'  => 'all',
                'data_collect_submit'  => 'all',
                'data_collect_view'    => 'all',
            ]);
            $roomModel->setAttribute('third_party_user_id', (string)$thirdPartUid);
            $roomModel->setAttribute('paas_access_token', (string)$access_token);
        }
        $roomModel->setAttribute('like', $roomModel->like);

        return $roomModel;
    }

    /**
     * 获取房间信息
     *
     * @param array $where
     *
     * @return Model|\Illuminate\Database\Query\Builder|object
     *
     */
    public function getRow(array $where, $with = [])
    {
        $roomModel = vss_model()->getRoomsModel()->getRow($where, $with);
        if (empty($roomModel)) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }
        return $roomModel;
    }

    /**
     * @param $params
     *
     * @return mixed
     * @author  jin.yang@vhall.com
     * @date    2020-08-12
     */
    public function getByConsole($params)
    {
        $roomInfo = vss_service()->getRoomService()->getRow([
            'il_id'      => $params['il_id'],
            'account_id' => $params['account_id'],
        ])->toArray();

        vss_logger()->info('room_info', ['data' => $roomInfo]);
        if ($params['operateType'] == 1) {
            if ($roomInfo['live_audit'] != RoomConstant::LIVE_AUDIT_PASS) {
                $this->fail(ResponseCode::BUSINESS_AUDIT_REJECT);
            }
        }

        $ret = vss_redis()->get(RoomConstant::WARM_INFO . $params['il_id']);
        $ret = json_decode($ret, true);
        if ($ret) {
            if ($ret['type'] == 0) {
                $ret['img'] = $roomInfo['cover_image'];
            }
            $roomInfo['warm_info'] = $ret;
        } else {
            $roomInfo['warm_info'] = ['type' => 0];
        }

        if (!empty($roomInfo['topics'])) {
            $roomInfo['topics'] = $this->getTagsInfo($roomInfo['topics']);
        }

        return $roomInfo;
    }

    /**
     * 获取标签信息
     *
     * @param $topocs
     *
     * @return array
     */
    public function getTagsInfo($topocs)
    {
        $tags = [];
        if (!empty($topocs)) {
            # vhallEOF-tag-inavService-get-1-start
        
            $tags = vss_service()->getTagService()->tagsInfo($topocs);

        # vhallEOF-tag-inavService-get-1-end
        }
        return $tags;
    }

    /**
     * 获取房间列表 --轻享模版默认逻辑
     *
     * @param        $ilId
     * @param        $keyword
     * @param        $beginTime
     * @param        $endTime
     * @param        $status
     * @param        $page
     * @param        $pageSize
     * @param        $accountId
     * @param int $roomType
     * @param string $keyName
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @throws \Exception
     */
    public function getList(
        $ilId,
        $keyword,
        $beginTime,
        $endTime,
        $status,
        $page,
        $pageSize,
        $accountId,
        $roomType = 1,
        $keyName = ''
    )
    {
        //列表数据
        $condition = [
            'keyword'    => $keyword,
            'created'    => [$beginTime, $endTime . ' 23:59:59'],
            'status'     => $status,
            'il_id'      => $ilId,
            'account_id' => $accountId,
            'room_type'  => $roomType,
        ];
        return $this->roomList($condition, $page, $pageSize, $keyName);
    }

    /**
     * 获取房间列表 --saas模版逻辑
     *
     * @param        $ilId
     * @param        $keyword
     * @param        $beginTime
     * @param        $endTime
     * @param        $status
     * @param        $page
     * @param        $pageSize
     * @param        $accountId
     * @param int $roomType
     * @param string $keyName
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @throws \Exception
     */
    public function saasList(
        $ilId,
        $keyword,
        $beginTime,
        $endTime,
        $status,
        $page,
        $pageSize,
        $accountId,
        $roomType = 1,
        $keyName = ''
    )
    {
        //列表数据
        $condition = [
            'keyword'    => $keyword,
            'created'    => [$beginTime, $endTime . ' 23:59:59'],
            'status'     => $status,
            'il_id'      => $ilId,
            'account_id' => $accountId,
            'room_type'  => $roomType,
        ];
        if ($status == RoomConstant::STATUS_STOP) {
            $condition['record_id'] = '';
        }
        return $this->roomList($condition, $page, $pageSize, $keyName);
    }

    /**
     * 房间列表
     *
     * @param        $condition
     * @param int $page
     * @param int $pageSize
     * @param string $keyName
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @throws \Exception
     */
    public function roomList($condition, $page = 1, $pageSize = 10, $keyName = '')
    {
        $with  = [];
        $model = vss_model()->getRoomsModel();
        //设置排序字段
        if ($keyName) {
            $model = $model->setKeyName($keyName);
        }
        $liveList = $model->setPerPage($pageSize)->getList($condition, $with, $page);
        if (!empty($liveList)) {
            /** @var  $value RoomsModel */
            foreach ($liveList as $key => $value) {
                //直播时长，即最后一次回放的时长
                $time = $this->getLivingTime($value);

                $liveList[$key]['live_time'] = \Helper::secToTime($time);
            }
        }

        return $liveList;
    }

    /**
     * 获取直播时长
     * @params $roomInfo
     * @return int
     */
    public function getLivingTime($roomInfo)
    {
        $status        = $roomInfo->status; // 开播状态 0-待直播 1-直播中 2-直播结束
        $beginLiveTime = $roomInfo->begin_live_time; // 直播开始时间
        $endLiveTime   = $roomInfo->end_live_time; // 直播结束时间

        if ($status == RoomConstant::STATUS_START && $beginLiveTime > $endLiveTime) {
            $time = time() - strtotime($beginLiveTime);
        } elseif ($endLiveTime >= $beginLiveTime) {
            $time = strtotime($endLiveTime) - strtotime($beginLiveTime);
        } else {
            // 正常运行不会进入此处逻辑
            // 当结束直播，并且 paas 回调还未到来时，会进入这个逻辑
            $streamStatus = vss_service()->getPaasService()->getStreamStatus($roomInfo['room_id']);
            $time         = bcsub(
                strtotime($streamStatus[$roomInfo['room_id']]['end_time']),
                strtotime($streamStatus[$roomInfo['room_id']]['push_time'])
            );
            vss_logger()->info(
                'getLivingTime',
                ['status' => $status, 'begin' => $beginLiveTime, 'end' => $endLiveTime, 'streamInfo' => $streamStatus]
            );
        }
        return (int)$time;
    }

    /**
     * @param string $roomId
     * @param int $status
     *
     * @return bool
     *
     */
    public function setStatus($roomId, $status)
    {
        vss_validator([
            'room_id' => $roomId,
            'status'  => $status,
        ], [
            'room_id' => 'required',
            'status'  => 'required',
        ]);
        $room = vss_model()->getRoomsModel()->findByRoomId($roomId);

        return $room && $room->update(['status' => $status]);
    }

    /**
     * 开始直播
     *
     * @param string $roomId
     * @param int $startType
     *
     * @return bool
     *
     */
    public function startLive($roomId, $startType = 1)
    {
        try {
            vss_model()->getRoomExtendsModel()->getConnection()->beginTransaction();
            $this->setStatus($roomId, RoomConstant::STATUS_START);
            vss_model()->getRoomExtendsModel()->create([
                'room_id'    => $roomId,
                'start_time' => date('Y-m-d H:i:s'),
                'start_type' => $startType,
            ]);
            vss_service()->getInavService()->setGlobal($roomId, InavGlobalConstant::START_TYPE, $startType);
            $this->flushIsOpenDocument($roomId);
            vss_model()->getRoomExtendsModel()->getConnection()->commit();

            //房间消息
            vss_service()->getPaasChannelService()->sendMessage($roomId, [
                'type'         => 'live_start',
                'room_join_id' => vss_service()->getTokenService()->getAccountId(),
            ]);
            //通知消息
            vss_service()->getPaasChannelService()->sendNotifyMessage($roomId, [
                'type'         => 'live_start',
                'room_join_id' => vss_service()->getTokenService()->getAccountId(),
            ]);

            return true;
        } catch (Exception $e) {
            vss_model()->getRoomExtendsModel()->getConnection()->rollBack();
            return false;
        }
    }

    /**
     * 结束直播
     *
     * @param string $roomId
     * @param int $endType
     *
     * @param array $params
     *
     * @return bool
     *
     */
    public function endLive($roomId, $endType = 1, $params = [])
    {
        vss_validator([
            'room_id'  => $roomId,
            'end_type' => $endType,
        ], [
            'room_id'  => 'required',
            'end_type' => 'required',
        ]);
        try {
            vss_model()->getRoomExtendsModel()->getConnection()->beginTransaction();
            $this->setStatus($roomId, RoomConstant::STATUS_STOP);
            $this->flushIsOpenDocument($roomId);
            $extend = vss_model()->getRoomExtendsModel()->where([
                'room_id'  => $roomId,
                'end_type' => 0,
            ])->orderBy('created_at', 'desc')->first();
            if ($extend) {
                $extend->update([
                    'end_time' => date('Y-m-d H:i:s'),
                    'end_type' => $endType,
                ]);
            } else {
                vss_logger()->info('end-third-new', ['aa' => $params, 'result' => $extend]); //日志
            }
            vss_model()->getRoomExtendsModel()->getConnection()->commit();

            //====删除rtmp========
            if (!empty($params['tag'])) {
                $key       = 'pull_stream_' . $roomId;
                $config_id = vss_redis()->get($key);
                if ($config_id) {
                    vss_redis()->del([$key]);
                    vss_service()->getPaasService()->delStream($config_id);
                }
            }
            vss_service()->getPaasChannelService()->sendMessage($roomId, [
                'type'         => 'live_over',
                'room_join_id' => vss_service()->getTokenService()->getAccountId(),
            ]);
            //通知消息
            vss_service()->getPaasChannelService()->sendNotifyMessage($roomId, [
                'type'         => 'live_over',
                'room_join_id' => vss_service()->getTokenService()->getAccountId(),
            ]);

            vss_logger()->info('end-third-new', ['aa' => $params, 'result' => $extend]); //日志
            return true;
        } catch (Exception $e) {
            vss_model()->getRoomExtendsModel()->getConnection()->rollBack();
            $this->fail($e->getCode(), $e->getMessage());

            return false;
        }
    }

    /**
     * 开启/关闭文档
     *
     * @param string $roomId
     * @param int $status
     *
     * @return bool
     * @throws Exception
     */
    public function setIsOpenDocument($roomId, $status)
    {
        vss_redis()->hset(
            CachePrefixConstant::ROOM_GLOBAL . $roomId,
            RoomConstant::IS_OPEN_DOCUMENT,
            (int)$status
        );

        return true;
    }

    /**
     * 获取文档开关状态
     *
     * @param string $roomId
     *
     * @return int
     * @throws Exception
     */
    public function getIsOpenDocument($roomId)
    {
        return (int)vss_redis()->hget(CachePrefixConstant::ROOM_GLOBAL . $roomId, RoomConstant::IS_OPEN_DOCUMENT);
    }

    /**
     * 清除文档开关状态缓存
     *
     * @param string $roomId
     *
     * @throws Exception
     */
    public function flushIsOpenDocument($roomId)
    {
        vss_redis()->del([CachePrefixConstant::ROOM_GLOBAL . $roomId]);
    }

    /**
     * 禁言/取消禁言
     *
     * @param RoomJoinsModel $joinUser
     * @param                $status
     *
     * @return bool
     */
    public function setIsBanned($joinUser, $status)
    {
        $joinUser->update([
            'is_banned' => $status,
        ]);
        $currentJoinUser = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId(
            vss_service()->getTokenService()
                ->getAccountId(),
            $joinUser->room_id
        );
        vss_service()->getPaasChannelService()->sendChatMessage($joinUser->room_id, $currentJoinUser->account_id, [
            'type'         => $status ? 'disable' : 'permit',
            'room_join_id' => $currentJoinUser->account_id,
            'target_id'    => $joinUser->account_id,
            'nick_name'    => $joinUser->nickname,
        ]);

        return true;
    }

    /**
     * 批量禁言
     *
     * @param $roomId
     * @param $accountIds
     * @param $status
     *
     * @return bool
     */
    public function batchBanned($roomId, $accountIds, $status)
    {
        vss_validator([
            'room_id'     => $roomId,
            'status'      => $status,
            'account_ids' => $accountIds,
        ], [
            'room_id'     => 'required',
            'status'      => 'required',
            'account_ids' => 'required',
        ]);

        $list    = vss_model()->getRoomJoinsModel()->where('room_id', $roomId)->whereIn(
            'account_id',
            $accountIds
        )->get(['join_id', 'account_id', 'nickname'])->toArray();
        $joinIds = array_column($list, 'join_id');
        //批量修改
        $updCount = vss_model()->getRoomJoinsModel()->updateByJoinIds($joinIds, ['is_banned' => $status]);
        if (!$updCount) {
            return false;
        }

        $currentJoinUser = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId(
            vss_service()->getTokenService()->getAccountId(),
            $roomId
        );
        foreach ($list as $joinInfo) {
            //删除缓存
            vss_model()->getRoomJoinsModel()->delCache($joinInfo['join_id'], $joinInfo['account_id'], $roomId);
            vss_model()->getRoomJoinsModel()->deleteCache('getBannedNum', $roomId);
            //消息广播
            vss_service()->getPaasChannelService()->sendChatMessage($roomId, $currentJoinUser->account_id, [
                'type'         => $status ? 'disable' : 'permit',
                'room_join_id' => $currentJoinUser->account_id,
                'target_id'    => $joinInfo['account_id'],
                'nick_name'    => $joinInfo['nickname'],
            ]);
        }

        return true;
    }

    public function getBannedNum($room_id)
    {
        return vss_model()->getRoomJoinsModel()->getBannedNum($room_id);
    }

    /**
     * 获取禁言状态
     *
     * @param string $roomId
     * @param int $joinId
     *
     * @return int
     */
    public function getIsBanned($roomId, $joinId)
    {
        return vss_model()->getRoomJoinsModel()->where('room_id', $roomId)->where(
            'join_id',
            $joinId
        )->value('is_banned');
    }

    /**
     * 获取禁言列表
     *
     * @param string $roomId
     * @param int $page
     * @param int $pageSize
     *
     * @return mixed
     */
    public function getBannedList($roomId, $page, $pageSize)
    {
        return vss_model()->getRoomJoinsModel()->where(['room_id' => $roomId, 'is_banned' => 1])->paginate(
            $pageSize,
            ['*'],
            'page',
            $page
        );
    }

    /**
     * 踢出/取消踢出
     *
     * @param string $roomId
     * @param int $joinId
     * @param int $status
     *
     * @return bool
     */
    public function setIsKicked($roomId, $joinId, $status)
    {
        $roomJoinInfo = vss_model()->getRoomJoinsModel()->findByJoinId($joinId);
        if (!$roomJoinInfo) {
            return false;
        }
        $roomJoinInfo->is_kicked = $status;
        $roomJoinInfo->save();
        $currentJoinUser = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId(
            vss_service()->getTokenService()
                ->getAccountId(),
            $roomId
        );
        vss_service()->getPaasChannelService()->sendMessage($roomId, [
            'type'         => $status ? 'room_kickout' : 'room_kickout_cancel',
            'room_join_id' => $currentJoinUser->account_id,
            'target_id'    => $roomJoinInfo->account_id,
            'nick_name'    => $roomJoinInfo->nickname,
            'push_time'    => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    /**
     * 批量踢出
     *
     * @param $roomId
     * @param $accountIds
     * @param $status
     *
     * @return bool
     */
    public function batchKick($roomId, $accountIds, $status)
    {
        vss_validator([
            'room_id'     => $roomId,
            'status'      => $status,
            'account_ids' => $accountIds,
        ], [
            'room_id'     => 'required',
            'status'      => 'required',
            'account_ids' => 'required',
        ]);

        $list    = vss_model()->getRoomJoinsModel()->where('room_id', $roomId)->whereIn(
            'account_id',
            $accountIds
        )->get(['join_id', 'account_id', 'nickname'])->toArray();
        $joinIds = array_column($list, 'join_id');
        //批量修改
        $updCount = vss_model()->getRoomJoinsModel()->updateByJoinIds($joinIds, ['is_kicked' => $status]);
        if (!$updCount) {
            return false;
        }

        $currentJoinUser = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId(
            vss_service()->getTokenService()->getAccountId(),
            $roomId
        );
        $date            = date('Y-m-d H:i:s');
        foreach ($list as $joinInfo) {
            //删除缓存
            vss_model()->getRoomJoinsModel()->delCache($joinInfo['join_id'], $joinInfo['account_id'], $roomId);
            vss_service()->getPaasChannelService()->sendMessage($roomId, [
                'type'         => $status ? 'room_kickout' : 'room_kickout_cancel',
                'room_join_id' => $currentJoinUser->account_id,
                'target_id'    => $joinInfo['account_id'],
                'nick_name'    => $joinInfo['nickname'],
                'push_time'    => $date,
            ]);
        }

        return true;
    }

    /**
     * 获取禁言状态
     *
     * @param string $roomId
     * @param int $joinId
     *
     * @return int
     */
    public function getIsKicked($roomId, $joinId)
    {
        return vss_model()->getRoomJoinsModel()->where('room_id', $roomId)->where(
            'join_id',
            $joinId
        )->value('is_kicked');
    }

    /**
     * 获取踢出列表
     *
     * @param string $roomId
     * @param int $page
     * @param int $pageSize
     *
     * @return mixed
     */
    public function getKickedList($roomId, $page, $pageSize)
    {
        return vss_model()->getRoomJoinsModel()->where(['room_id' => $roomId, 'is_kicked' => 1])->paginate(
            $pageSize,
            ['*'],
            'page',
            $page
        );
    }

    /**
     * 获取禁言和提出的列表
     *
     * @param string $roomId
     * @param int $page
     * @param int $pageSize
     *
     * @return mixed
     */
    public function getBannedKickedList($roomId, $page, $pageSize)
    {
        $list = vss_model()->getRoomJoinsModel()->where('room_id', $roomId)
            ->where(function ($query) use ($roomId) {
                $query->where('is_kicked', 1);
                $query->orwhere('is_banned', 1);
            })->selectRaw('room_id,is_kicked,is_banned,username,nickname,account_id,role_name')
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'page', $page);

        return json_decode(json_encode($list, JSON_UNESCAPED_UNICODE), true);
    }

    /**
     * 更新加入房间的信息
     *
     * @param array $params
     *
     * @return RoomJoinsModel|bool
     *
     */
    public function joinUpdate(array $params)
    {
        $rule          = [
            'room_id'    => 'required',
            'account_id' => 'required',
            'nickname'   => '',
            'avatar'     => '',
            'role_name'  => '',
        ];
        $data          = vss_validator($params, $rule);
        $roomJoinModel = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId(
            $params['account_id'],
            $params['room_id']
        );
        if (!$roomJoinModel) {
            $this->fail(ResponseCode::BUSINESS_INVALID_USER);
        }
        if (!empty($params['no_sync_saas'])) { //防止逆向同步
            $roomJoinModel->sync_saas = false;
        }
        !$roomJoinModel->update($data) && $this->fail(ResponseCode::BUSINESS_EDIT_FAILED);

        return $roomJoinModel;
    }

    /**
     * 加入房间
     *
     * @param array $params
     *
     * @return RoomJoinsModel|bool
     *
     */
    public function join(array $params)
    {
        $rule          = [
            'room_id'       => 'required',
            'account_id'    => 'required',
            'username'      => 'required',
            'nickname'      => 'required',
            'avatar'        => '',
            'role_name'     => 'required',
            'device_type'   => '',
            'device_status' => '',
        ];
        $data          = vss_validator($params, $rule);
        $roomJoinModel = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId(
            $params['account_id'],
            $params['room_id']
        );
        if (!$roomJoinModel) {
            $roomJoinModel = vss_model()->getRoomJoinsModel()->create($data);
//            $roomJoinModel && vss_service()->getPaasChannelService()->saveUserInfo($roomJoinModel->account_id,
            //                $roomJoinModel->nickname, $roomJoinModel->avatar ?: 'default.jpg');
        } else {
            $roomJoinModel->update($data);
        }

        return $roomJoinModel ? $roomJoinModel : false;
    }

    /**
     * 获取参会用户列表
     *
     * @param       $roomId
     * @param       $page
     * @param       $pageSize
     * @param array $condition
     *
     * @return mixed
     */
    public function getJoinList($roomId, $page, $pageSize, array $condition = [])
    {
        return vss_model()->getRoomJoinsModel()->where('room_id', $roomId)->when(
            isset($condition['nickname']),
            function (Builder $query) use ($condition) {
                $query->where('nickname', 'like', sprintf('%%%s%%', $condition['nickname']));
            }
        )->when(isset($condition['role_name']), function (Builder $query) use ($condition) {
            $query->where('role_name', '=', $condition['role_name']);
        })->paginate($pageSize, ['*'], 'page', $page);
    }

    /**
     * 获取在线用户列表
     *
     * @param $roomId
     * @param $page
     * @param $pageSize
     * @param $condition
     *
     * @return array
     * @throws Exception
     */
    public function getOnlineList($roomId, $page, $pageSize, $condition = [])
    {
        vss_validator([
            'room_id'  => $roomId,
            'page'     => $page,
            'pagesize' => $pageSize,
        ], [
            'room_id'  => 'required',
            'page'     => '',
            'pagesize' => '',
        ]);

        $specialList = [];
        $list        = [];
        $speakArr    = [];
        $roomModel   = vss_model()->getRoomsModel()->where('room_id', $roomId)->first();
        if ($roomModel) {
            if (empty($condition['nickname']) && $page == 1) {
                //上麦用户
                $speakList = $this->getSpeakUsers($roomId);
                //获取在线特殊角色用户
                $speakArr    = array_column($speakList, 'account_id');
                $specialList = $this->getSpecialUsers($roomId, $speakArr);

                $specialList = array_merge($specialList, $speakList);
                //判断是否在线
                $online = vss_service()->getPaasChannelService()->checkUserOnlineByChannel(
                    $roomModel->channel_id,
                    array_column($specialList, 'account_id')
                );
                foreach ($specialList as $key => $value) {
                    if ((isset($online[$value['account_id']]) && $online[$value['account_id']] > 0)
                        || $value['role_name'] == 1) {
                        continue;
                    }
                    unset($specialList[$key]);
                }
            }
            //在线用户
            $pageSize   = $pageSize > 200 ? 200 : $pageSize;
            $userIdList = vss_service()->getPaasService()->getUserIdList($roomModel->channel_id, $page, $pageSize);
            if ($userIdList && is_array($userIdList)) {
                $list = vss_model()->getRoomJoinsModel()
                    ->where('room_id', $roomId)
                    ->where('role_name', '2')
                    ->whereIn('account_id', $userIdList['third_party_user_id']);
                if ($speakArr) {
                    $list = $list->whereNotIn('account_id', $speakArr);
                }
                if (!empty($condition['nickname'])) {
                    $list = $list->where('nickname', $condition['nickname']);
                }
                $list = $list->get()->toArray();
                foreach ($list as &$l) {
                    $this->onlineInavAttr($l);
                }
            }
        }

        return array_merge($specialList, $list);
    }

    /**
     * 获取房间所有在线用户id
     *
     * @param $roomId
     *
     * @return array
     */
    public function getAllOnlineUser($roomId)
    {
        vss_validator(['room_id' => $roomId], [
            'room_id' => 'required',
        ]);
        $roomInfo   = vss_model()->getRoomsModel()->findByRoomId($roomId);
        $channelId  = $roomInfo->channel_id;
        $accountIds = $this->getOnlineAccountIds($channelId);
        if (empty($accountIds)) {
            return [];
        }
        return vss_model()->getRoomJoinsModel()->listByRoomIdAccountIds($roomId, $accountIds);
    }

    /**
     * 获取所有在线用户ID
     *
     * @param $roomId
     *
     * @return array
     */
    public function getOnlineAccountIds($channelId)
    {
        $page       = 1;
        $pagesize   = 1000;
        $accountIds = [];
        //获取所有在线用户account_id
        while (true) {
            $userIdList = vss_service()->getPaasService()->getUserIdList($channelId, $page, $pagesize);
            $accountIds = array_merge($accountIds, $userIdList['list']);

            if ($page >= $userIdList['page_all']) {
                break;
            }

            $page += 1;
        }
        return $accountIds;
    }

    /**
     * 获取在线用户列表总数
     *
     * @param $roomId
     * @param $condition
     *
     * @return int
     */
    public function getOnlineCount($roomId)
    {
        $count     = 0;
        $roomModel = vss_model()->getRoomsModel()->findByRoomId($roomId);
        if ($roomModel) {
            $count = (int)vss_service()->getPaasChannelService()->getOnlineNumByChannel($roomModel->channel_id);
        }

        return $count;
    }

    /**
     * 获取特殊用户列表（嘉宾/助理等）
     *
     * @param string $roomId
     *
     * @return array
     */
    public function getSpecialList($roomId)
    {
        $specialList = [];
        $roomModel   = vss_model()->getRoomsModel()->where('room_id', $roomId)->first();
        if ($roomModel) {
            //上麦用户
            $speakList = $this->getSpeakUsers($roomId);
            //获取在线特殊角色用户
            $speakArr    = array_column($speakList, 'account_id');
            $specialList = $this->getSpecialUsers($roomId, $speakArr);
            $specialList = array_merge($specialList, $speakList);
            //判断是否在线
            $online = vss_service()->getPaasChannelService()->checkUserOnlineByChannel(
                $roomModel->channel_id,
                array_column($specialList, 'account_id')
            );
            foreach ($specialList as $key => $value) {
                if ((isset($online[$value['account_id']]) && $online[$value['account_id']] > 0)
                    || $value['role_name'] == 1) {
                    continue;
                }
                unset($specialList[$key]);
            }
        }

        $specialList = array_values($specialList);
        return $specialList;
    }

    public function getUserInfoByAccountId($roomId, $accountId)
    {
        $object = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId($accountId, $roomId);
        if (!empty($object)) {
            return $object->toArray();
        }
        return [];
    }

    public function signedUserInfo($roomId, $accountId)
    {
        $object = vss_model()->getRoomJoinsModel()
            ->where('room_id', $roomId)
            ->where('is_signed', 0)
            ->where('account_id', $accountId)
            ->first();
        if (!empty($object)) {
            $object->is_signed = 1;

            return $object->save();
        }
        return false;
    }

    public function getUserInfosByAccountIds($roomId, $accountIds)
    {
        $list = vss_model()->getRoomJoinsModel()
            ->where('room_id', $roomId)
            ->whereIn('account_id', $accountIds)
            ->get()->toArray();
        if (!empty($list) && is_array($list)) {
            return array_column($list, null, 'account_id');
        }
        return [];
    }

    public function getHostUserInfoByRoomId($roomId)
    {
        $object = vss_model()->getRoomJoinsModel()
            ->where('room_id', $roomId)
            ->where('role_name', 1)
            ->first();
        if (!empty($object)) {
            return $object->toArray();
        }
        return [];
    }

    public function getRoomInfoByRoomId($roomId)
    {
        $object = vss_model()->getRoomsModel()->findByRoomId($roomId);
        if (!empty($object)) {
            return $object->toArray();
        }
        return [];
    }

    /**
     * @param $params [room_id]
     *
     * @return mixed
     * @author  jin.yang@vhall.com
     * @date    2020-08-08
     */
    public function getPushInfo($params)
    {
        $expireTime = date('Y/m/d H:i:s', time() + 24 * 3600);

        return vss_service()->getPaasService()->getPushInfo($params['room_id'], $expireTime);
    }

    /**
     * 获取直播流的流信息
     *
     * @param $roomId
     *
     * @return mixed
     */
    public function getStreamMsg($params)
    {
        vss_validator($params, [
            'room_id' => 'required',
        ]);
        return vss_service()->getPaasService()->getStreamMsg($params['room_id']);
    }

    /**
     * 获取上麦用户
     *
     * @param $roomId
     *
     * @return array
     * @throws Exception
     */
    public function getSpeakUsers($roomId)
    {
        $userList = vss_service()->getInavService()->getSpeakerList($roomId);
        if ($userList && is_array($userList)) {
            $userList            = array_column($userList, null, 'account_id');
            $accountIdArr        = array_keys($userList);
            $listOn              = vss_model()->getRoomJoinsModel()
                ->where('room_id', $roomId)
                ->whereIn('account_id', $accountIdArr)
                ->get()->toArray();
            $mainScreenAccountId = vss_redis()->hget(
                CachePrefixConstant::INTERACT_GLOBAL . $roomId,
                InavGlobalConstant::MAIN_SCREEN
            );
            foreach ($listOn as &$l) {
                $isSpeak      = 1;
                $audio        = $userList[$l['account_id']]['audio'];
                $video        = $userList[$l['account_id']]['video'];
                $isMainScreen = 0;
                if ($l['account_id'] == $mainScreenAccountId) {
                    $isMainScreen = 1;
                }
                $this->onlineInavAttr($l, $isSpeak, $audio, $video, $isMainScreen);
            }

            return $listOn;
        }

        return [];
    }

    public function onlineInavAttr(&$user, $isSpeak = 0, $audio = 0, $video = 0, $isMainScreen = 0)
    {
        $user['is_speak']       = (int)$isSpeak;
        $user['audio']          = (int)$audio;
        $user['video']          = (int)$video;
        $user['is_main_screen'] = (int)$isMainScreen;
        return $user;
    }

    /**
     * 获取在线特殊角色用户
     *
     * @param       $roomId
     * @param array $notAccountId 排除用户
     *
     * @return mixed
     */
    public function getSpecialUsers($roomId, $notAccountId = [])
    {
        $listSpecial = vss_model()->getRoomJoinsModel()
            ->where('room_id', $roomId)
            ->where('role_name', '<>', '2');
        if ($notAccountId) {
            $listSpecial = $listSpecial->whereNotIn('account_id', $notAccountId);
        }
        $listSpecial = $listSpecial->get()->toArray();
        foreach ($listSpecial as &$value) {
            $this->onlineInavAttr($value);
        }

        return $listSpecial;
    }

    /**
     * @param $params
     *
     * @return array
     *
     * @author  jin.yang@vhall.com
     * @date    2020-08-08
     */
    public function getAttr($params)
    {
        vss_validator($params, [
            'room_id' => 'required',
        ]);
        $roomId                 = $params['room_id'];
        $roomModel              = false;
        $rebroadcast_room_id    = '';
        $rebroadcast_channel_id = '';

        # vhallEOF-broadcast-RoomService-getAttr-1-start
        
        //房间转播情况
        $rebroadcast = vss_model()->getRebroadCastModel()->getStartRebroadcastByRoomId($roomId);
        $rebroadcast_room_id = $rebroadcast->source_room_id;
        if ($rebroadcast_room_id) {
            $roomModel = vss_model()->getRoomsModel()->findByRoomId($rebroadcast_room_id);
        }
        if (!empty($roomModel)) {
            $rebroadcast_channel_id = $roomModel->channel_id;
        }

        # vhallEOF-broadcast-RoomService-getAttr-1-end

        $data = [
            'is_board'               => 0,
            'is_doc'                 => vss_service()->getRoomService()->getIsOpenDocument($roomId),
            'is_handsup'             => vss_service()->getInavService()->getHandsup($roomId),
            'stream'                 => [
                'layout'     => vss_service()->getInavService()->getLayout($roomId),
                'definition' => vss_service()->getInavService()->getDefinition($roomId),
            ],
            'main_screen'            => (string)vss_service()->getInavService()->getMainScreen($roomId),
            'doc_permission'         => (string)vss_service()->getInavService()->getDocPermission($roomId),
            'speaker_list'           => vss_service()->getInavService()->getSpeakerList($roomId),
            'start_type'             => vss_service()->getInavService()->getGlobal(
                $roomId,
                InavGlobalConstant::START_TYPE,
                1
            ),
            'rebroadcast'            => $rebroadcast_room_id,
            'all_banned'             => vss_service()->getInavService()->getAllBanned($roomId),
            'is_desktop'             => vss_service()->getInavService()->getDesktop($roomId),
            'rebroadcast_channel_id' => $rebroadcast_channel_id,
            'tool'                   => (object)vss_redis()->hGetAll(CachePrefixConstant::INTERACT_TOOL . $roomId),
            'tool_records'           => (object)vss_redis()->hGetAll(CachePrefixConstant::INTERACT_TOOL_RECORDS . $roomId),

        ];

        # vhallEOF-invitecard-RoomService-getAttr-1-start
        
        $data["is_invitecard"] = vss_service()->getInviteCardService()->getStatus($roomId);
                                    
        # vhallEOF-invitecard-RoomService-getAttr-1-end

        return $data;
    }

    /**
     * Notes: 批量获取房间状态
     * Author: michael
     * Date: 2019/10/9
     * Time: 18:00
     *
     * @param $roomId
     *
     * @return array
     *
     */
    public function getRoomsStatu($roomId)
    {
        $roomModel = vss_model()->getRoomsModel()->findRoomStatusById($roomId);

        if (empty($roomModel) || count($roomModel) < 1) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }

        return $roomModel;
    }

    /**
     * 获取房间扩展信息
     *
     * @param string $roomId
     *
     * @return array|false|RoomsModel|null
     *
     */
    public function getRoomExtends($roomId)
    {

        //1、获取房间信息
        $roomModel = vss_model()->getRoomsModel()->findByRoomId($roomId);
        if (empty($roomModel)) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }

        //2、获取房间扩展信息
        $extend = vss_model()->getRoomExtendsModel()
            ->where(['room_id' => $roomId, 'end_type' => 0])
            ->orderByDesc('created_at')
            ->first();

        //3、组织数据格式
        return [
            'room_id'    => $roomModel->room_id,
            'record_id'  => $roomModel->record_id,
            'status'     => $roomModel->status,
            'account_id' => $roomModel->account_id,
            'start_time' => $extend['start_time'],
            'end_time'   => $extend['end_time'],
        ];
    }

    /**
     * 获取房间补充信息
     *
     * @param       $ilId
     * @param array $columns
     *
     * @return array
     *
     */
    public function getRoomSupplyByIlId($ilId)
    {
        if (empty($ilId)) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        $data = vss_model()->getRoomSupplyModel()->getInfoByIlId($ilId);
        return $data ? $data->toArray() : [];
    }

    /**
     * @param $params
     *
     * @return int|mixed
     *
     */
    public function saveWarm($params)
    {
        $condition = [];
        $params['il_id'] && $condition['il_id'] = $params['il_id'];
        $params['account_id'] && $condition['account_id'] = $params['account_id'];
        $params['room_id'] && $condition['room_id'] = $params['room_id'];

        $roomModel = $this->getRow($condition);

        $data = [
            'warm_type'   => $params['type'],
            'warm_vod_id' => $params['vod_id'],
        ];

        $result = $roomModel->update($data);

        if ($result) {
            $warmInfo = $this->getWarm(['il_id' => $roomModel->il_id, 'account_id' => $roomModel->account_id]);
            if ($warmInfo['type'] == 0) {
                $warmInfo['img'] = $roomModel->cover_image;
            }

            $key = RoomConstant::WARM_INFO . $roomModel->il_id;
            vss_redis()->set($key, json_encode($warmInfo));
            vss_redis()->persist($key);
            return $result;
        }

        $this->fail(ResponseCode::BUSINESS_EDIT_FAILED);
    }

    /**
     * @param $params
     *
     * @return array
     *
     */
    public function getWarm($params)
    {
        $roomModel = $this->getRow([
            'il_id'      => $params['il_id'],
            'account_id' => $params['account_id'],
        ]);

        $result = false;
        if ($roomModel->warm_type == 1 && !empty($roomModel->warm_vod_id)) {
            # vhallEOF-record-roomservice-getWarm-1-start

            # vhallEOF-record-roomservice-getWarm-1-end
        }

        if ($result) {
            $vod_name = $result->name;
        }

        return [
            'type'     => $roomModel->warm_type,
            'vod_id'   => $roomModel->warm_vod_id,
            'vod_name' => $vod_name ?? '',
        ];
    }

    /**
     * 获取直播中的活动频道信息
     */
    public function getOnlineChannelInfos()
    {
        //1、获取信息
        $model = vss_model()->getRoomsModel()
            ->where('status', '=', RoomConstant::STATUS_START)
            ->select(['channel_id', 'status'])
            ->get();

        if (empty($model)) {
            return [];
        }

        return $model->toArray();
    }

    /**
     * 获取正在推流的活动列表
     *
     * @param int $limit
     *
     * @param int $il_id
     *
     * @return array
     * @author  jin.yang@vhall.com
     * @date    2020-05-25
     */
    public function getPushStreamList($il_id = 0, $limit = 100)
    {
        $model = vss_model()->getRoomsModel()->where('status', RoomConstant::STATUS_START);
        if ($il_id > 0) {
            $model->where('il_id', '>', $il_id);
        }

        return $model->orderBy('il_id')->take($limit)->get()->toArray();
    }

    /**
     * 同步流状态
     *
     * @param int $interactiveStreamStatus 房间信息
     *
     * @return bool
     */
    public function syncStreamStatus($interactiveStreamStatus)
    {
        // 1 为推流状态,这里判断非推流状态才做处理
        if ($interactiveStreamStatus['stream_status'] != 1) {
            // 查询当前房间
            $info = vss_model()->getRoomsModel()->findByRoomId($interactiveStreamStatus['room_id']);
            // 如果存在
            if ($info) {
                // 修改状态为"停止"
                $info->status = RoomConstant::STATUS_STOP;
                if ($interactiveStreamStatus['push_time'] != '0000-00-00 00:00:00') {
                    $info->begin_live_time = $interactiveStreamStatus['push_time'];
                }
                if ($interactiveStreamStatus['end_time'] != '0000-00-00 00:00:00') {
                    $info->end_live_time = $interactiveStreamStatus['end_time'];
                } else {
                    $info->end_live_time = $info->begin_live_time;
                }

                return $info->save();
            }
        }
        return false;
    }

    /**
     * 通过活动ID批量获取对应的详情信息
     *
     * @param $ilIdArr
     *
     * @return array
     */
    public function getInfoByIlIds($ilIdArr)
    {
        //1、查询数据
        $model = vss_model()->getRoomsModel()->whereIn('il_id', $ilIdArr)->get(['il_id', 'room_id', 'channel_id']);

        return $model->toArray();
    }

    /**
     * 通过活动ID获取对应的详情信息
     *
     * @param $ilId
     *
     * @return array
     */
    public function getInfoByIlId($ilId)
    {
        $model = vss_model()->getRoomsModel()->where('il_id', $ilId)->first();
        if ($model) {
            return $model->toArray();
        }

        return [];
    }

    /**
     * @param $account_id
     *
     * @param $roomInfo
     *
     * @return mixed
     *
     * @author  jin.yang@vhall.com
     * @date    2020-08-07
     */
    public function getAccessToken($roomInfo, $account_id)
    {
        $inavId      = $roomInfo['inav_id'];
        $roomId      = $roomInfo['room_id'];
        $channelId   = $roomInfo['channel_id'];
        $tokenData   = [
            'kick_inav'            => $inavId,
            'publish_inav_stream'  => $inavId,
            'askfor_publish_inav'  => $inavId,
            'publish_inav_another' => $inavId,
            'audit_publish_inav'   => $inavId,
            'apply_inav_publish'   => $inavId,
            'publish_stream'       => $roomId,
            'kick_inav_stream'     => $inavId,
            'operate_document'     => $channelId,
            'chat'                 => $channelId,
            'third_party_user_id'  => $account_id,
        ];
        $accessToken = vss_service()->getPaasService()->baseCreateAccessToken($tokenData);

        if (empty($accessToken)) {
            throw new ValidationException(ResponseCode::AUTH_TOKEN_CREATE_FAILED);
        }

        return $accessToken;
    }

    /**
     * 房间审核
     *
     * @param $auditStatus
     *
     * @param $ilId
     *
     * @return string
     *
     * @author  jin.yang@vhall.com
     * @date    2020-09-08
     */
    public function audit($ilId, $auditStatus)
    {
        if (empty($ilId)) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        if (empty($auditStatus)) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        if (!in_array($auditStatus, array_keys($this->auditArr))) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }

        //2、判断需要审核的数据状态
        $condition = ['il_id' => $ilId];
        $liveInfo  = vss_model()->getRoomsModel()->getRow($condition);
        if (empty($liveInfo)) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }
        if ($liveInfo['live_audit'] == RoomConstant::LIVE_AUDIT_PASS) {
            $this->fail(ResponseCode::BUSINESS_AUDIT_PASS);
        }
        //3、更新状态
        $liveInfo->setAttribute('live_audit', $auditStatus);
        if ($liveInfo->update() === false) {
            $this->fail(ResponseCode::BUSINESS_AUDIT_FAILED);
        }

        return $this->auditArr[$auditStatus] . '--成功';
    }

    /**
     * 获取审核数据
     *
     * @return mixed
     *
     * @author  jin.yang@vhall.com
     * @date    2020-09-08
     */
    public function getAuditInfo()
    {
        $liveInfo = vss_model()->getRoomsModel()
            ->where('live_audit', '=', RoomConstant::LIVE_AUDIT_WAIT)
            ->orderBy('il_id', 'asc')
            ->first();
        if (!$liveInfo) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }
        $result = json_decode(json_encode($liveInfo), true);
        if ($result['live_audit'] == RoomConstant::LIVE_AUDIT_WAIT && $result['status'] == 0) {
            $result['status_str'] = '待审核';
        }
        if ($result['live_audit'] == RoomConstant::LIVE_AUDIT_BACK && $result['status'] == 0) {
            $result['status_str'] = '驳回';
        }

        return $result;
    }

    public function exportList($keyword, $beginTime, $endTime, $status)
    {
        //Excel文件名
        $fileName           = 'RoomsList' . date('YmdHis');
        $headerList         = ['房间id', '房间名称', '直播码', '主持人id', '主持人用户名', '主持人昵称', '直播总时长/秒', '观众总人数/人', '观看总次数/次'];
        $exportProxyService = vss_service()->getExportProxyService()->init($fileName)->putRow($headerList);

        //列表数据
        $page     = 1;
        $pageSize = 1000;
        while (true) {
            //当前page下列表数据
            $condition = [
                'keyword'    => $keyword,
                'begin_time' => $beginTime,
                'end_time'   => $endTime,
                'status'     => $status,
            ];
            $with      = [
                'account' => function ($query) {
                    $query->select('account_id', 'username', 'nickname');
                },
            ];
            $liveList  = vss_model()->getRoomsModel()
                ->setPerPage($pageSize)
                ->getListWithStat($condition, $with, $page);
            if (!empty($liveList->items())) {
                foreach ($liveList->items() as $liveItem) {
                    $row = [
                        $liveItem['il_id'] ?: '-',
                        $liveItem['name'] ?: '-',
                        $liveItem['room_id'] ?: '-',
                        $liveItem['account']['account_id'] ?: '-',
                        $liveItem['account']['username'] ?: '-',
                        $liveItem['account']['nickname'] ?: '-',
                        $liveItem['duration_total'],
                        $liveItem['uv_total'],
                        $liveItem['pv_total'],
                    ];
                    $exportProxyService->putRow($row);
                }
            }

            //跳出while
            if ($page >= $liveList->lastPage() || $page >= 10) { //10页表示1W上限
                break;
            }
            //下一页
            $page++;
        }

        //下载文件
        $exportProxyService->download();
    }

    /**
     * 增加房间PV量
     *
     * @param int $ilId
     * @param int $accountId
     *
     * @return bool
     */
    public function addPv($ilId, $accountId)
    {
        //增加访问记录
        $datetime = date('Y-m-d H:i:s');
        RoomAttendsModel::getInstance()->addRow([
            'il_id'      => $ilId,
            'account_id' => $accountId,
            'created_at' => $datetime,
            'updated_at' => $datetime,
        ]);

        return true;
    }

    /**
     * 房间查询
     *
     * @param array $params
     *
     * @return array
     *
     */
    public function roomSel($params)
    {
        $roomInfo = vss_model()->getRoomsModel()->getInfoByIlId($params['il_id']);
        return $roomInfo ? $roomInfo->toArray() : [];
    }

    /**
     * 更新房间里数据
     *
     * @param array $params
     *
     * @return bool|Model|\Illuminate\Database\Query\Builder|object
     *
     */
    public function wtachupdate(array $params)
    {
        $model = $this->getRow(['il_id' => $params['il_id']]);
        if ($model) {
            $model->setAttribute('limit_type', $params['limit_type']);
            $model->update();
        }
        return $model ?: false;
    }

    /**
     * 获取主持人的一些信息
     *
     * @param string $account_id
     *
     * @return mixed
     */
    public function getDirect($account_id, $roomid)
    {
        $model = vss_model()->getRoomJoinsModel()->where('room_id', $roomid)->where('role_name', 1)->first();
        return $model;
    }

    /**
     * 房间数量
     *
     * @param string $accountId
     *
     * @return array
     */
    public function roomCount($accountId = '')
    {
        //累计直播 已创建的房间数
        $condition = [];
        if ($accountId) {
            $condition['account_id'] = $accountId;
        }
        $count = vss_model()->getRoomsModel()->getCount($condition);

        //预告直播 预计开播时间晚于当前时间
        $condition = [];
        if ($accountId) {
            $condition['account_id'] = $accountId;
        }
        $condition['status'] = RoomConstant::STATUS_WAITING;
        $currTime            = date('Y-m-d H:i:s');
        $precount            = vss_model()->getRoomsModel()->where(
            'start_time',
            '>=',
            $currTime
        )->where($condition)->count();

        return ['count' => $count, 'preview_count' => $precount];
    }
}
