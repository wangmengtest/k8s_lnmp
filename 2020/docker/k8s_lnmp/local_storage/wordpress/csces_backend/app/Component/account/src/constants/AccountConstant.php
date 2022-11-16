<?php

namespace App\Component\account\src\constants;

/**
 * AccountConstant
 *
 * @uses     yangjin
 * @date     2020-07-30
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class AccountConstant
{
    /**
     * 性别
     */
    const SEX_MEN   = 1; //男

    const SEX_WOMEN = 0; //女

    /**
     * 用户类型
     */
    const TYPE_NULL        = 0; //未分配类型

    const TYPE_MASTER      = 1; //主持人

    const TYPE_WATCH       = 2; //观众

    const TYPE_ASSISTANT   = 3; //助理人员

    const TYPE_INTERACTION = 4; //嘉宾/互动者

    const TYPE_FLYING = 5; //飞手

    /**
     * 用户状态
     */
    const STATUS_DISABLED = -1; //封停

    const STATUS_ENABLED  = 0; //正常

    /**
     * 用户账号类型
     */
    const ACCOUNT_TYPE_MASTER = 1;

    const ACCOUNT_TYPE_WATCH  = 2;

    const ACCOUNT_TYPE_VISITOR = 3;

    /**
     * 账号类型
     */
    const ACCOUT_TYPE = 1;  //发起端

    const TOKEN_TIME = 604800;  //token过期时间 7天

    /**
     * 控制台登录有效模块范围
     *
     * @var array
     */
    const ALLOW_MODULES = ['Console', 'Api'];

    const ALLOW_API_MODULES = ['Api'];

    const SYNC_USER_API = 'http://219.134.89.13:8082/wUser/selectUserAll';

    const SYNC_ORG_API = 'http://219.134.89.13:8082/wOrgOrgs/selectIOrgAll';

    const USER_TYPE_CSCES = 2;//中建用户

    //用户同步时候记录的记录消失次数
    const ACCOUNT_NOTEXISTS_COUNT = 'accounts:notexists:count:';
}
