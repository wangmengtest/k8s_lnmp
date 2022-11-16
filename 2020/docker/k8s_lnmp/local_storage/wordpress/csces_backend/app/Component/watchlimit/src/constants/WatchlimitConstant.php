<?php

namespace App\Component\watchlimit\src\constants;

/**
 * WatchLimitConstant
 *
 * @uses     yangjin
 * @date     2020-07-30
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class WatchlimitConstant
{
    /**
     * 房间类型
     */
    const ACCOUNT_TYPE_LOGIN = 0; #登录

    const ACCOUNT_TYPE_APPEAR  = 1; #上报

    const ACCOUNT_TYPE_APPROVE = 2;  #默认

    const ACCOUNT_TYPE_WHITE = 3; #白名单

    /**
     *  表单提交 redis key
     * */
    const APPLY_SUBMIT = 'apply:submit:' ;

    const APPLYUSER_SUBMIT = 'applyuser:submit:' ;

    const WHITE_ACCOUNT_MYRIAD = 50000;

    const WHITE_ACCOUNT_FOUR = 40000;
}
