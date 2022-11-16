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
class AccountOrgConstant
{
    const ORG_TYPE_ORG   = 0; //组织

    const ORG_TYPE_DEPT = 1; //部门

    const ORG_VIRTUAL_ID = 90000000;//虚拟部门ID

    //组织架构无用户信息列表
    const ORGS_NONEUSER_CACHE = 'orgs:noneuser:cache:';

    //组织架构有用户信息列表
    const ORGS_HASUSER_CACHE = 'orgs:hasuser:cache:';

    //组织架构同步时候记录的记录消失次数
    const ORGS_NOTEXISTS_COUNT = 'orgs:notexists:count:';
}
