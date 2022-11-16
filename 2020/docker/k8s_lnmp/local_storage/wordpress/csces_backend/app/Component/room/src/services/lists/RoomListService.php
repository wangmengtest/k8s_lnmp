<?php

namespace App\Component\room\src\services\lists;

use App\Component\account\src\constants\AccountOrgConstant;
use App\Component\room\src\constants\RoomConstant;
use App\Component\room\src\constants\RoomJoinRoleNameConstant;
use vhallComponent\watchlimit\constants\WatchlimitConstant;
use Vss\Common\Services\WebBaseService;

/**
 * RoomListServiceTrait
 */
class RoomListService extends WebBaseService
{
    protected static $ownerCountKeys   = ['account_id', 'source'];

    protected static $inviteCountKeys  = ['invited_account_id', 'invited_limit_type', 'without_account_id', 'group', 'source'];

    protected static $ownerListKeys    = ['account_id', 'subject_like', 'status', 'created', 'source'];

    protected static $manageListKeys   = ['created', 'status', 'subject_like', 'il_id', 'account_name_like', 'dept_id', 'org_id', 'source'];

    protected static $inviteListKeys   = ['created', 'il_id', 'status', 'subject_like', 'invited_account_id', 'invited_limit_type', 'without_account_id', 'group', 'source', 'begin_times'];

    protected static $watchWaitingKeys = ['created', 'il_id', 'status', 'subject_like', 'invited_account_id', 'invited_limit_type', 'group', 'source', 'begin_times'];

    protected static $watchStopKeys    = ['created', 'il_id', 'org_id', 'dept_id', 'status', 'subject_like', 'invited_account_id', 'invited_limit_type', 'group', 'source', 'begin_times'];

    protected static $searchKeys       = ['created','il_id','org_id','dept_id','status','subject_like','invited_account_id','invited_limit_type','group','source','begin_times','created','il_id','status','subject_like','invited_account_id','invited_limit_type','group','source','begin_times','created','il_id','status','subject_like','invited_account_id','invited_limit_type','without_account_id','group','source','begin_times'];

    /**
     * 获取搜索条件
     */
    private static function getCondition($params, $accountInfo, $source = 'watch-list'){
        $keyword = trim($params['keyword'] ?: '');
        $beginTime = \Helper::fillFullTimeStamp($params['begin_time'] ?: date('Y-m-d', strtotime("-90 day")));
        $endTime = \Helper::fillFullTimeStamp($params['end_time'], false, "23:59:59");

        $status = $params['status'] ?? '';
        $ilId = trim($params['il_id'] ?: '');
        $subject = trim($params['subject'] ?: '');
        $accountName = $params['account_name'] ?: '';
        $dept = $params['dept'] ?: '';
        $org = $params['org'] ?: '';

        $timeInterval = ($beginTime && $endTime) ? [$beginTime, $endTime] : [];
        vss_service()->getAccountFormatService()->getCommonDeptOrOrg($dept, $org, $accountInfo);

        //列表数据
        $condition = [
            'keyword'    => $keyword,
            'status'     => $status,
            'il_id'      => $ilId,
            'subject_like' => $subject,
            'account_name_like' => $accountName,
            'account_id' => $accountInfo['account_id'],
            'invited_account_id' => $accountInfo['account_id'],
            'invited_limit_type' => WatchlimitConstant::ACCOUNT_TYPE_APPROVE,
            'without_account_id' => $accountInfo['account_id'],
            'create_account_id'  => $accountInfo['account_id'],
            'dept_id'    => $dept,
            'org_id'     => $org,
            'source'     => $source,
            'group'      => 'rooms.il_id',
        ];

        // 直播时间范围查询和创建时间乏味查询定义
        if(isset($params['begin_times']) && count($params['begin_times']) == 2)  {
            $condition['begin_times'] = $params['begin_times'];
        } else {
            $condition['created'] = $timeInterval;
        }

        return $condition;
    }

    /**
     * 观看列表各状态下的数量
     */
    public function firstWatchList($params, $accountInfo){
        $pageSize  = $params['pagesize'] ?: '20';
        $page      = $params['page'] ?: '1';
        $source    = 'watch-list';

        //直播中列表：展示自己被邀请的和公开的直播，身份可以是嘉宾、观众和助理
        $params['status'] = RoomConstant::STATUS_START;
        $condition = self::getInviteCondition($params, $accountInfo, $source);
        $liveList = vss_service()->getRoomService()->getListByFilter($condition, $page, $pageSize, 'start_time')->toArray();
        if($liveList['data']){
            return RoomConstant::STATUS_START;
        }

        //预告列表：展示自己被邀请的、自己创建的和公开的直播。身份可以是嘉宾、观众、助理和主持人
        $params['status'] = RoomConstant::STATUS_WAITING;
        $condition = self::getWatingCondition($params, $accountInfo, $source);
        $liveList = vss_service()->getRoomService()->getListByFilter($condition, $page, $pageSize, 'start_time')->toArray();
        if($liveList['data']){
            return RoomConstant::STATUS_WAITING;
        }

        //已结束列表：展示该用户自己创建的、被邀请的、公开的和同级别及以下组织其他用户的直播回放
        $params['status'] = RoomConstant::STATUS_STOP;
        $condition = self::getStopCondition($params, $accountInfo, $source);
        $liveList = vss_service()->getRoomService()->getListByFilter($condition, $page, $pageSize, 'start_time')->toArray();
        if($liveList['data']){
            return RoomConstant::STATUS_STOP;
        }
        return RoomConstant::STATUS_START;
    }

    /**
     * 观看端-房间列表数量
     */
    public function watchCountList($params, $accountInfo){
        $source    = 'watch-list';
        $status    = $params['status'] ?? '';

        //已结束列表：展示该用户自己创建的、被邀请的、公开的和同级别及以下组织其他用户的直播回放
        if($status == RoomConstant::STATUS_STOP){
            $condition = self::getStopCondition($params, $accountInfo, $source);
        }
        //预告列表：展示自己被邀请的、自己创建的和公开的直播。身份可以是嘉宾、观众、助理和主持人
        if($status == RoomConstant::STATUS_WAITING){
            $condition = self::getWatingCondition($params, $accountInfo, $source);
        }
        //直播中列表：展示自己被邀请的和公开的直播，身份可以是嘉宾、观众和助理
        if($status == RoomConstant::STATUS_START){
            $condition = self::getInviteCondition($params, $accountInfo, $source);
        }

        return vss_service()->getRoomService()->getCountByFilter($condition);
    }

    /**
     * 观看端-房间列表
     */
    public function watchList($params, $accountInfo){
        $source    = 'watch-list';
        $pageSize  = $params['pagesize'] ?: '10';
        $page      = $params['page'] ?: '1';
        $status    = isset($params['status']) ? (int)$params['status'] : 0;

        switch ($status) {
            //已结束列表：展示该用户自己创建的、被邀请的、公开的和同级别及以下组织其他用户的直播回放
            case RoomConstant::STATUS_STOP:
                $condition = self::getStopCondition($params, $accountInfo, $source);
                break;

            //预告列表：展示自己被邀请的、自己创建的和公开的直播。身份可以是嘉宾、观众、助理和主持人
            case RoomConstant::STATUS_WAITING:
                $condition = self::getWatingCondition($params, $accountInfo, $source);
                break;

            //直播中列表：展示自己被邀请的和公开的直播，身份可以是嘉宾、观众和助理
            case RoomConstant::STATUS_START:
                $condition = self::getInviteCondition($params, $accountInfo, $source);
                break;

            // 无状态搜索
            default:
                $params['status'] = '';
                $condition = self::getCondition($params, $accountInfo, $source);
                $condition = self::formatCondition(self::$searchKeys, $condition);
        }

//        \Helper::dumpSql();
        $liveList = vss_service()->getRoomService()->getListByFilter($condition, $page, $pageSize, 'start_time');
        return vss_service()->getRoomFormatService()->formatList($liveList, $accountInfo);
    }

    /**
     * 直播控制台-会议结束列表条件
     */
    public static function getStopCondition($params, $accountInfo, $source){
        $condition = self::getCondition($params, $accountInfo, $source);
        return self::formatCondition(self::$watchStopKeys, $condition);
    }


    /*
     * 预告搜索条件
     * */
    public static function getWatingCondition($params, $accountInfo, $source){
        $condition = self::getCondition($params, $accountInfo, $source);
        return self::formatCondition(self::$watchWaitingKeys, $condition);
    }

    /*
     * 邀请搜索条件
     * */
    public static function getInviteCondition($params, $accountInfo, $source){
        $condition = self::getCondition($params, $accountInfo, $source);
        $condition['subject_like'] = $condition['keyword'];
        return self::formatCondition(self::$inviteListKeys, $condition);
    }

    /*
     * 被邀请数量搜索条件
     * */
    public static function getInviteCountCondition($params, $accountInfo, $source){
        $condition = self::getCondition($params, $accountInfo, $source);
        return self::formatCondition(self::$inviteCountKeys, $condition);
    }

    /**
     * 直播控制台-邀请列表
     */
    public function inviteList($params, $accountInfo){
        $pageSize  = $params['pagesize'] ?: '20';
        $page      = $params['page'] ?: '1';
        $condition = self::getInviteCondition($params, $accountInfo, 'invited-list');

        $liveList = vss_service()->getRoomService()->getListByFilter($condition, $page, $pageSize, 'start_time');
        return vss_service()->getRoomFormatService()->formatList($liveList, $accountInfo);
    }

    /**
     * 直播控制台-邀请列表数量
     */
    public function inviteCount($params, $accountInfo){
        $pageSize  = $params['pagesize'] ?: '1';
        $page      = $params['page'] ?: '1';
        $condition = self::getInviteCountCondition($params, $accountInfo, 'invited-list');
        $liveList = vss_service()->getRoomService()->getListByFilter($condition, $page, $pageSize, 'start_time')->toArray();
        return $liveList['total'] ?: 0;
    }

    /**
     * 直播控制台-邀请列表数量bak
     */
    public function inviteCountBak($params, $accountInfo){
        $condition = ['limit_type'=>WatchlimitConstant::ACCOUNT_TYPE_APPROVE, 'without_account_id' => $accountInfo['account_id']];
        $publicCount = (int)vss_service()->getRoomService()->getCountByFilter($condition);
        $condition = ['room_role_ids'=> RoomJoinRoleNameConstant::ROLES_WITHOUT_HOST, 'account_id' => $accountInfo['account_id']];
        $inviteCount = (int)vss_service()->getRoomInvitedService()->getCountByFilter($condition);
        return $publicCount + $inviteCount;
    }

    /**
     * 直播控制台-我创建的列表
     */
    public static function getOwnerCondition($params, $accountInfo, $source){
        $condition = self::getCondition($params, $accountInfo, $source);
        $condition['subject_like'] = $condition['keyword'];
        return self::formatCondition(self::$ownerListKeys, $condition);
    }

    /**
     * 直播控制台-我创建的数量
     */
    public static function getOwnerCountCondition($params, $accountInfo, $source){
        $condition = self::getCondition($params, $accountInfo, $source);
        return self::formatCondition(self::$ownerCountKeys, $condition);
    }

    /**
     * 直播控制台-我创建的列表
     */
    public function ownerList($params, $accountInfo){
        $pageSize  = $params['pagesize'] ?: '20';
        $page      = $params['page'] ?: '1';
        $condition = self::getOwnerCondition($params, $accountInfo, 'owner-list');

        $liveList = vss_service()->getRoomService()->getListByFilter($condition, $page, $pageSize, 'start_time');
        return vss_service()->getRoomFormatService()->formatList($liveList, $accountInfo);
    }

    /**
     * 直播控制台-我创建的列表-数量
     */
    public function ownerCount($params, $accountInfo){
        $condition = self::getOwnerCountCondition($params, $accountInfo, 'owner-list');
        return vss_service()->getRoomService()->getCountByFilter($condition);
    }

    /*
     * 管理列表搜索条件组装
     * */
    public static function getManageCondition($params, $accountInfo, $source){
        $condition = self::getCondition($params, $accountInfo, $source);
        return self::formatCondition(self::$manageListKeys, $condition);
    }

    /**
     * 直播控制台-房间管理列表
     */
    public function manageList($params, $accountInfo){
        if($params['org'] > AccountOrgConstant::ORG_VIRTUAL_ID){
            return [];
        }
        $pageSize  = $params['pagesize'] ?: '20';
        $page      = $params['page'] ?: '1';
        $condition = self::getManageCondition($params, $accountInfo, 'manage-list');

        $liveList = vss_service()->getRoomService()->getListByFilter($condition, $page, $pageSize);
        return vss_service()->getRoomFormatService()->formatList($liveList, $accountInfo);
    }

    /*
     * 获取有效的条件
     * */
    protected static function formatCondition($fields, $condition){
        foreach ($condition as $field=>$value){
            if(!in_array($field, $fields)){
                unset($condition[$field]);
            }
        }
        return $condition;
    }
}
