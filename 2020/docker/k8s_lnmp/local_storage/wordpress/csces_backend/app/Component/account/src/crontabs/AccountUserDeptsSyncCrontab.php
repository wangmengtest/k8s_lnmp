<?php


namespace App\Component\account\src\crontabs;

use vhallComponent\decouple\crontabs\BaseCrontab;

/**
 * 同步人员同级及以下部门数据， 每小时的第 25 分钟执行
 * Class ExportCrontab
 * @package App\Component\account\src\crontab
 */
class AccountUserDeptsSyncCrontab
{
    public $name = 'crontab:account-user-depts-sync';

    public $description = '同步人员同级及以下部门与组织';

    //每小时的第 15 分钟执行
    public $cron = '25 1 * * *';

    public function handle()
    {
        return;
        vss_service()->getAccountSyncService()->syncUsvverDepts();
    }
}
