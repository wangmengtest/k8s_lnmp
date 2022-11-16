<?php

namespace App\Component\room\src\services\cache;

use App\Component\room\src\constants\RoomInvitedConstant;
use Vss\Common\Services\WebBaseService;
use Vss\Traits\CacheTrait;
use Vss\Traits\SingletonTrait;
/**
 * CacheRoomInvitedService
 */
class CacheRoomInvitedService extends WebBaseService
{
    use CacheTrait;
    use SingletonTrait;

    /**
     * 获取room邀请数据-加人员数据打包
     */
    public function getInvitedAccountInfoByIlId($key)
    {
        $this->cacheExpire = [
            RoomInvitedConstant::ROOMS_GET_INVITED_ACCOUNTINFO_BY_ILID => 86400,
        ];
        $data = $this->getCache(RoomInvitedConstant::ROOMS_GET_INVITED_ACCOUNTINFO_BY_ILID, $key, function () use ($key) {
            return vss_service()->getRoomInvitedService()->getInvitedAccountInfoByIlId($key);
        });
        return $data;
    }

    /**
     * 删除缓存
     */
    public function delCache($key){
        $this->deleteCache(RoomInvitedConstant::ROOMS_GET_INVITED_ACCOUNTINFO_BY_ILID, $key);
    }
}
