<?php

namespace vhallComponent\pendant\constants;

/**
 * PendantConstant
 *
 * @uses     oujun
 * @date     2021-2-4
 * @author   jun.ou@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class PendantConstant
{
    /*挂件表业务常量*/
    const TYPE_PUSH  = 1;//推屏挂件
    const TYPE_FIXED = 2;//固定挂件


    const STATUS_ON  = 1;           //正常
    const STATUS_OFF = -1;          //删除

    //商品点击操作类型
    const OPERATE_TYPE_CLICK = 1;   // 点击


    /**redis key  */

    const PENDANT_KEY_EXPIRE = 86400;

    //观众端固定挂件信息 key
    const PENDANT_FIXED_INFO_KEY = 'fixed_pendant_key:';

    //活跃的挂件id hash
    const ACTIVITY_PENDANT_KEY = 'activity_pendant_key';
}
