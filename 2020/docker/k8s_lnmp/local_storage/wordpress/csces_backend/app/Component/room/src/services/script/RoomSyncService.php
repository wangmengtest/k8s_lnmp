<?php

namespace App\Component\room\src\services\script;
use App\Component\room\src\constants\RoomConstant;
use Vss\Common\Services\WebBaseService;
/**
 * RoomSyncService
 */
class RoomSyncService extends WebBaseService
{
    /*
     * 会议同步人员姓名,用户人员姓名是可以修改的,检测作用
     * */
    public function syncAccountName(){
        $initSize = 1000;
        $key = RoomConstant::ROOMS_ACCOUNT_SYNC_CURRENT_TOTAL . date('Y');
        $pagesizeKey = RoomConstant::ROOMS_ACCOUNT_SYNC_CURRENT_PAGESIZE . date('Y');
        $pageKey = RoomConstant::ROOMS_ACCOUNT_SYNC_CURRENT_PAGE . date('Y');


        $pageTotal = vss_redis()->get($key);
        $pagesize = vss_redis()->get($pagesizeKey);
        $page =  vss_redis()->get($pageKey);
        vss_logger()->info('csces-room-account-sync', ['action'=>'syncAccountName-start', 'result' => ['page_total'=>$pageTotal, 'pagesize'=>$pagesize, 'page'=>$page]]); //日志

        if($page > $pageTotal){
            //已经处理完毕
            return;
        }

        $beginTime = date('Y-m-d', strtotime("-90 day"));
        $endTime = date('Y-m-d');
        if(!vss_redis()->exists($key) || !vss_redis()->exists($pagesizeKey) || !vss_redis()->exists($pageKey)){
            $condition = [
                'created' => ($beginTime && $endTime) ? [$beginTime, $endTime . ' 23:59:59'] : [],
            ];
            $total = vss_service()->getRoomService()->getCountByFilter($condition);
            if($total > 500000){
                $initSize = 2000;
            }
            if($total > 1000000){
                $initSize = 4000;
            }
            if($total > 5000000){
                $initSize = 6000;
            }
            if($total > 10000000){
                $initSize = 10000;
            }
            vss_logger()->info('csces-room-account-sync', ['action'=>'syncAccountName-get-count', 'result' => $total]); //日志
            $realTotal = ceil($total / $initSize);
            $pagesize = ceil($total / $realTotal);
            if($total < $initSize){
                $pagesize = $initSize;
            }
            $pageTotal = ceil($total / $pagesize);
            $runDay = ceil($total / ($initSize * 138));
            $expire = 24 * $runDay * 3600;
            vss_redis()->set($key, $pageTotal, $expire);
            vss_redis()->set($pagesizeKey, $pagesize, $expire);
            vss_redis()->set($pageKey, 1, $expire);
        }

        $condition = [
            'source'=>'script-sync-list',
            'created' => ($beginTime && $endTime) ? [$beginTime, $endTime . ' 23:59:59'] : [],
        ];
        $lives = vss_service()->getRoomService()->getListByFilter($condition, $page, $pagesize, 'il_id')->toArray();

        if(empty($lives['data'])){
            return;
        }
        vss_logger()->info('csces-room-account-sync', ['action'=>'syncAccountName-get-list', 'result' => $lives['data']]); //日志
        $allUsers = (array)vss_service()->getAccountOrgService()->allUser();
        $allUsers = array_column($allUsers, 'nickname', 'account_id');
        array_walk($lives['data'], function ($live) use($allUsers){
            if(isset($allUsers[$live['account_id']])){
                if($allUsers[$live['account_id']] != $live['account_name']){
                    vss_service()->getRoomService()->updateAccountNameByIlIdAndAccountId($live['il_id'], $live['account_id'], $allUsers[$live['account_id']]);
                }
            }
        });
        vss_redis()->incr($pageKey);
    }
}
