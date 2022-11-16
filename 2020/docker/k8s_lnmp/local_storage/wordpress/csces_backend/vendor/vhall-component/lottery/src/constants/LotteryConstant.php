<?php
/**
 * Created by PhpStorm.
 * User: liuxiangliang
 * Date: 2020/11/9
 * Time: 18:43
 */
namespace vhallComponent\lottery\constants;

class LotteryConstant
{
    const IS_WINNER_YES = 1;    //中奖

    const IS_WINNER_NO = 0;     //未中奖

    //符合条件的抽奖用户范围 自定义导入用户
    const LOTTERY_RANGE_IMPORT = 'lottery:range:import:';

    //符合条件的抽奖用户范围 指定列表随机
    const LOTTERY_RANGE_ACCOUNTS = 'lottery:range:accounts:';

    //抽奖规则(标题)
    const LOTTERY_RULE_TEXT = 'lottery:rule:text:';

    //抽奖导入用户文件名
    const LOTTERY_IMPORT_FILENAME = 'lottery:import:filename:';

    //抽奖信息收集
    const LOTTERY_EXTENSION = 'lottery:extension:';

    //开始抽奖防并发提交
    const LOTTERY_ADD_LOCK = 'lottery:add:lock:';

    //抽奖json信息地址
    const LOTTERY_WINNERS_JSON_URL = 'lottery:winners:json:url:';

    //抽奖完成
    const LOTTERY_STATUS_END = 1;

    //抽奖规则--指定列表随机
    const LOTTERY_RULE_LIVE = 1;

    //抽奖规则--自定义列表随机
    const LOTTERY_RULE_CUSTOM = 2;

    //抽奖用户池记录目录
    const USER_NOTE_DIR = 'lottery/lottery_user/';

    //抽奖中奖用户记录目录
    const WINNERS_NOTE_DIR = 'lottery/lottery_winners/';

    //抽奖导出
    const EXPORT_LOTTERY = 'lottery';
}
