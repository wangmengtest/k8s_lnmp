<?php

namespace App\Component\room\src\services\cache;

use App\Component\room\src\constants\RoomConstant;
use Vss\Common\Services\WebBaseService;
/**
 * CacheRoomListService
 */
class CacheRoomListService extends WebBaseService
{
    protected $cacheOpType = 'opService';

    /**
     * 获取我创建的房间数量（创建的总量）
     */
    public function ownerCount($params, $accountInfo)
    {
        $this->cacheExpire = [
            RoomConstant::ROOMS_OWNER_COUNT_ALL => 86400,
        ];
        $key = $accountInfo['account_id'];
        $data = $this->getCache(RoomConstant::ROOMS_OWNER_COUNT_ALL, $key, function () use ($params, $accountInfo) {
            return vss_service()->getRoomListService()->ownerCount($params, $accountInfo);
        });
        return $data;
    }

    /**
     * 获取被邀请的房间数量（邀请的总量）
     */
    public function watchlist($params, $accountInfo)
    {
        vss_logger()->info('csces-watchlist', ['action'=>'watchlist', 'params' => $params, 'account_id'=>$accountInfo['account_id']]); //日志
        $pageSize   = $params['pagesize'] ?: '10';
        $page       = $params['page'] ?: '1';
        $status     = $params['status'] ?? '';

        // 非标准搜索
        $subject    = $params['subject'] ?? '';
        $startTime  = $params['begin_time'] ?? '';
        $endTime    = $params['end_time'] ?? '';

        // 是否标准查询
        $standWatchList = !($subject || $startTime || $endTime);

        if ($standWatchList) {
            $expireTime = rand(3610, 3700);
            $this->cacheExpire = [
                RoomConstant::ROOMS_WATCH_LIST_ACCOUNT => ($page > 2) ? 60 : $expireTime,
            ];

            $key = $accountInfo['account_id'] . $status . $page . $pageSize;
            $this->setCacheMembers(RoomConstant::ROOMS_WATCH_LIST_CACHE_MEMBERS, $accountInfo['account_id'], $expireTime);

            $data = $this->getCache(RoomConstant::ROOMS_WATCH_LIST_ACCOUNT, $key, function () use ($params, $accountInfo) {
                $result = vss_service()->getRoomListService()->watchlist($params, $accountInfo);
                vss_logger()->info('csces-watchlist', ['action'=>'getCacheFromDb', 'params' => $params, 'account_id'=>$accountInfo['account_id'], 'result'=>$result]); //日志
                return $result;
            });

        } else {
            $key = "custom search";
            // 非标转查询，查询所有状态
            $params['status'] = -1;

            // 非标查询，含有时间查询时使用直播时间作为查询依据
            if ($startTime && $endTime) {
                $params['begin_times'] = [\Helper::fillFullTimeStamp($startTime, true), \Helper::fillFullTimeStamp($endTime, true, "23:59:59")];
            }

            $data = vss_service()->getRoomListService()->watchlist($params, $accountInfo);
        }

        vss_logger()->info('csces-watchlist', ['action'=>'getCache', 'cache_key'=>$key, 'result' => $data, 'account_id'=>$accountInfo['account_id']]); //日志

        return $data;
    }

    /**
     * 删除观看列表缓存
     */
    public function delWatchListCache(){
        $members = vss_redis()->smembers(RoomConstant::ROOMS_WATCH_LIST_CACHE_MEMBERS);
        vss_redis()->del(RoomConstant::ROOMS_WATCH_LIST_CACHE_MEMBERS);
        vss_logger()->info('csces-delWatchListCache', ['action'=>'delWatchListCache', 'result' => $members]); //日志
        array_walk($members, function ($accountId){
            for ($i=0; $i<3; $i++){
                $key = $accountId . $i . '1' . '10';
                vss_logger()->info('csces-delWatchListCache', ['action'=>'delWatchListCache', 'del_key' => $key]); //日志
                $this->deleteCache(RoomConstant::ROOMS_WATCH_LIST_ACCOUNT, $key);
                $key = $accountId . $i . '2' . '10';
                vss_logger()->info('csces-delWatchListCache', ['action'=>'delWatchListCache', 'del_key' => $key]); //日志
                $this->deleteCache(RoomConstant::ROOMS_WATCH_LIST_ACCOUNT, $key);
            }
        });
    }

    /**
     * 获取被邀请的房间数量（邀请的总量）
     */
    public function inviteCount($params, $accountInfo)
    {
        $expireTime = rand(3610, 3700);
        $this->cacheExpire = [
            RoomConstant::ROOMS_INVITE_COUNT_ALL => $expireTime,
        ];

        $key = $accountInfo['account_id'];
        $this->setCacheMembers(RoomConstant::ROOMS_INVITE_COUNT_CACHE_MEMBERS, $key, $expireTime);

        $data = $this->getCache(RoomConstant::ROOMS_INVITE_COUNT_ALL, $key, function () use ($params, $accountInfo) {
            return vss_service()->getRoomListService()->inviteCount($params, $accountInfo);
        });
        return $data;
    }

    /**
     * 观看列表各状态下的数量
     */
    public function firstWatchList($params, $accountInfo){
        $expireTime = rand(3550, 3600);
        $this->cacheExpire = [
            RoomConstant::ROOMS_FIRST_WATCHLIST_CACHE => $expireTime,
        ];
        $key = $accountInfo['account_id'];
        $this->setCacheMembers(RoomConstant::ROOMS_FIRST_WATCHLIST_CACHE_MEMBERS, $key, $expireTime);

        return $this->getCache(RoomConstant::ROOMS_FIRST_WATCHLIST_CACHE, $key, function () use ($params, $accountInfo) {
            return vss_service()->getRoomListService()->firstWatchList($params, $accountInfo);
        });
    }

    /**
     * 删除观看列表各状态下的数量缓存
     */
    public function delFirstWatchListCache(){
        $members = vss_redis()->smembers(RoomConstant::ROOMS_FIRST_WATCHLIST_CACHE_MEMBERS);
        vss_redis()->del(RoomConstant::ROOMS_FIRST_WATCHLIST_CACHE_MEMBERS);
        array_walk($members, function ($key){
            $this->deleteCache(RoomConstant::ROOMS_FIRST_WATCHLIST_CACHE, $key);
        });
    }

    /**
     * 删除缓存
     */
    public function delOwnerCountCache($key){
        $this->deleteCache(RoomConstant::ROOMS_OWNER_COUNT_ALL, $key);
    }

    /**
     * 删除缓存
     */
    public function delInviteCountCache(){
        $members = vss_redis()->smembers(RoomConstant::ROOMS_INVITE_COUNT_CACHE_MEMBERS);
        vss_redis()->del(RoomConstant::ROOMS_INVITE_COUNT_CACHE_MEMBERS);
        array_walk($members, function ($key){
            $this->deleteCache(RoomConstant::ROOMS_INVITE_COUNT_ALL, $key);
        });
    }

    /*
     * 缓存集合
     * */
    protected function setCacheMembers($cacheKey, $key, $expireTime){
        if(!vss_redis()->exists($cacheKey)){
            vss_redis()->sadd($cacheKey, $key);
            vss_redis()->expire($cacheKey, $expireTime);
        }else{
            vss_redis()->sadd($cacheKey, $key);
        }
    }
}
