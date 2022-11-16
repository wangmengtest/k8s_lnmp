<?php
/**
 *+----------------------------------------------------------------------
 * @file RoomServiceImpl.php
 * @date 2019/6/9 15:57
 *+----------------------------------------------------------------------
 */

namespace vhallComponent\roomlike\services;

use App\Constants\ResponseCode;
use vhallComponent\room\constants\RoomConstant;
use vhallComponent\roomlike\constants\WaitWriteConstant;
use Vss\Common\Services\WebBaseService;

/**
 *+----------------------------------------------------------------------
 * Class RoomService
 * 房间模型服务装载器
 *+----------------------------------------------------------------------
 */
class RoomlikeService extends WebBaseService
{
    public function like(array $params)
    {
        vss_validator($params, [
            'room_id' => 'required',
            'num' => 'required',
            'type' => '',
        ]);
        $num = $params['num'] > 500 ? 500 : $params['num'];
        $rooms = vss_model()->getRoomsModel()->findByRoomId($params['room_id']);
        empty($rooms) && $this->fail(ResponseCode::EMPTY_ROOM);
        $key = RoomConstant::LIKE . $rooms->room_id;

        if ($params['type'] ?? 0) {
            $currentJoinUser = vss_service()->getRoomJoinsModel()->findByAccountIdAndRoomId(vss_service()->getTokenService()->getAccountId(), $params['room_id']);
            $like = vss_model()->getRoomLikeModel()->where(['room_id' => $params['room_id'], 'account_id' => $currentJoinUser->account_id])->first();
            if ($like) {
                vss_redis()->decr($key);
                $like->forceDelete();
            } else {
                vss_redis()->incr($key);
                vss_model()->getRoomLikeModel()->create(['room_id' => $params['room_id'], 'account_id' => $currentJoinUser->account_id]);
            }
        } else {
            vss_redis()->incrby($key, $num);
        }
        if (!vss_redis()->lock(WaitWriteConstant::LIKE . $rooms->room_id, 300)) {
            $count = vss_redis()->get($key);
            $like  = $rooms->like + intval($count);
            vss_redis()->decrby($key, $count);
            $rooms->update(['like' => $like]);
            vss_service()->getPaasChannelService()->sendMessage($params['room_id'], [
                'type' => 'room_like_num',
                'like' => $rooms->like,
            ]);
        }
    }
}
