<?php

namespace App\Component\room\src\services\inav;
use Vss\Common\Services\WebBaseService;

/**
 * CheckOnlineServiceTrait
 * @uses     mengmeng.wang
 * @date     2021-10-15
 */
class CheckOnlineService extends WebBaseService
{
    /*
     * 检查是否在直播间中
     * $rooms array
     * */
    public function isUserOnlineByRoomIds($roomIds, $accountId){
        $isOnline = false;
        array_walk($roomIds, function ($roomId) use (&$isOnline, $accountId){
            if($isOnline){
                return;
            }
            $roomInfo = vss_model()->getRoomsModel()->findByRoomId($roomId);
            if(empty($roomInfo)){
                return;
            }
            $result = $this->isUserOnlineByChannel($roomInfo['channel_id'], $accountId);
            if($result){
                $isOnline = true;
                return;
            }
        });
        return $isOnline;
    }

    /*
     * 检查是否在直播间中
     * $rooms array
     * */
    public function isUserOnlineByChannels($rooms, $accountId){
        $isOnline = false;
        array_walk($rooms, function ($room) use (&$isOnline, $accountId){
            if($isOnline){
                return;
            }
            $result = $this->isUserOnlineByChannel($room['channel_id'], $accountId);
            if($result){
                $isOnline = true;
                return;
            }
        });
        return $isOnline;
    }

    /*
     * 检查是否在直播间中
     * */
    public function isUserOnlineByChannel($channelId, $accountId){
        $accountIds = explode(',', $accountId);
        $result     = vss_service()->getPaasChannelService()->checkUserOnlineByChannel($channelId, $accountIds);
        $isOnline = false;
        foreach ($result as $userId=>$online){
            if($userId == $accountId){
                $isOnline = ($online == 0) ? false : true;
                break;
            }
        }
        return $isOnline;
    }
}
