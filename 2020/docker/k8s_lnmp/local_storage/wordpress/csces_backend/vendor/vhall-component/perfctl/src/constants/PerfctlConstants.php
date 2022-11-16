<?php
namespace vhallComponent\perfctl\constants;

class PerfctlConstants
{
    //房间下当前连接数数量
    //const CONNECT_COUNT_BY_ROOM = 'op:connect:count:room_id:';
    //应用下当前连接数量
    //const CONNECT_COUNT_BY_APP = 'op:connect:count:app:';

    //并发控制排队用户队列
    const QUEUE_PERFCTLCTL_ILID = 'queue:perfctl:il_id:';

    //请求频率锁
    const LOCK_REQ_CONNECT = 'lock_req_connect_';

    //锁时间
    const LOCK_TIME = 1;

    //并发到达上线通知锁
    const LOCK_NOTICE_LIMITED = 'lock_notice_limited_';

    //队列每次取出数量
    const QUEUE_BATCH_POP_NUM = 1000;

    //锁控制队列处理频率
    const LOCK_QUEUE_DEAL = 'lock_queue_deal_';

    //队列广播用户暂存集合
    const NOTIFY_ACCOUNT_LIST_ILID = 'notify:account:list:il_id:';

    //广播用户维持时间(s)
    const NOTIFY_ACCOUNT_EXIST_TIME = 10;

    //账户剩余连接数
    const CONNECT_COUNT_OF_ACCOUNT_BY_ILID = 'connect_count_of_account_by_ilid_';

    //队列锁定时间
    const LOCK_QUEUE_CRON_EXEC = 'lock_queue_cron_exec';

    //正在开播的房间
    const PERFCTL_BEGINNING_ILINFO = 'perfctl:beginning:ilinfo';

    //维护连接数与阈值占比   90即为90%
    const CONNECT_RATIO = 90;
}
