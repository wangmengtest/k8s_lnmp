<?php

namespace App\Component\room\src\constants;

class RoomConstant
{
    //文档开关
    const IS_OPEN_DOCUMENT = 'is_open_document';

    //白板开关
    const IS_OPEN_BOARD = 'is_open_board';

    //点赞
    const LIKE = 'op:room:like:';

    /**
     * 直播状态
     */
    const STATUS_WAITING = 0; //待直播

    const STATUS_START = 1; //直播中

    const STATUS_STOP = 2; //直播结束

    const V_STATUS_RECORD = 3; //回放 兼容saas模板回放类型 与room表中状态无对应关系

    /**
     * 文档开关
     */
    const DOCUMENT_OPEN = 1;

    const DOCUMENT_CLOSE = 0;

    /**
     * 聊天数超过200处理方式
     */
    const MESSAGE_APPROVAL_AUTO_SEND = 1; //自动发送

    const MESSAGE_APPROVAL_AUTO_STOP = 2; //自动阻止

    const MESSAGE_APPROVAL_DISABLE_AUDIT = 3; //关闭审核

    /**
     * 审核状态
     */
    const LIVE_AUDIT_WAIT = 1;  //待审核

    const LIVE_AUDIT_PASS = 2;  //审核通过

    const LIVE_AUDIT_BACK = 3;  //审核驳回

    /**
     * 房间等级
     */
    const LIVE_ROOM_TYPE = [
        0 => '无',
        1 => 'R1',
        2 => 'R3',
    ];

    /**
     * 直播状态
     */
    const LIVE_PLAY_ALL = 1; //全部

    const LIVE_STATUS_START = 2; //直播中

    const LIVE_PLAY_BACK = 3; //直播回放

    const PAGE_SIZE = 10;

    /**
     * 直播类型
     */
    const LIVE_TYPE_INTERACTION = 1; // 互动直播
    const LIVE_TYPE_ONLY        = 2; // 纯直播

    /**
     * 房间队列
     */
    const FORM_QUEUE_ROOM_IDS = 'form_queue_room_ids';

    //暖场视频信息
    const WARM_INFO = 'warm:info:';

    //房间下当前连接数数量
    const CONNECT_COUNT_BY_ROOM = 'connect:count:il_id:';

    //应用下当前连接数量
    const CONNECT_COUNT_BY_APP = 'connect:count:app:';

    //账户下直播中房间
    const LIVING_ROOMS_OF_ACCOUNT = 'living:rooms:account_id:';

    //10分钟短信通知room集合
    const ROOMS_SMS_NOTICE_DEFAULT = 'rooms:sms:notice:default';

    //计划短信通知room集合
    const ROOMS_PLAN_SMS_NOTICE_DEFAULT = 'rooms:plan:sms:notice:default';

    //记录每天检测房间用户信息时房间数据总数
    const ROOMS_ACCOUNT_SYNC_CURRENT_TOTAL = 'rooms:account:sync:current:total:';

    //记录每天检测房间用户信息时房间pagesize
    const ROOMS_ACCOUNT_SYNC_CURRENT_PAGESIZE = 'rooms:account:sync:current:pagesize:';

    //记录每天检测房间用户信息时房间page
    const ROOMS_ACCOUNT_SYNC_CURRENT_PAGE = 'rooms:account:sync:current:page:';

    //我创建的房间总量
    const ROOMS_OWNER_COUNT_ALL = 'rooms:owner:count:all:';

    //被邀请的房间总量
    const ROOMS_INVITE_COUNT_ALL = 'rooms:invite:count:all:';

    //邀请数量接口有缓存的人员集合
    const ROOMS_INVITE_COUNT_CACHE_MEMBERS = 'rooms:invite:count:cache:members:';

    //观看列表缓存Key
    const ROOMS_WATCH_LIST_ACCOUNT = 'rooms:watch:list:account:';

    //观看列表有缓存的人员集合
    const ROOMS_WATCH_LIST_CACHE_MEMBERS = 'rooms:watch:list:cache:members:';

    //开播的房间数量
    const ROOMS_START_IDS_CACHE = 'rooms:start:ids:cache:';

    //第一次进观看列表查询状态tab下的数量
    const ROOMS_FIRST_WATCHLIST_CACHE = 'rooms:first:watchlist:cache:';

    //第一次进观看列表查询状态tab下的人员集合
    const ROOMS_FIRST_WATCHLIST_CACHE_MEMBERS = 'rooms:first:watchlist:cache:members:';

    //检查当前正在直播中的房间流状态是否正常脚本执行次数CheckStreamStatusCrontab
    const ROOMS_CHECKSTREAMSTATUS_COUNT_CACHE = 'rooms:checkstreamstatus:count:cache:';
}
