<?php


namespace App\Component\account\src\crontabs;

use vhallComponent\decouple\crontabs\BaseCrontab;

/**
 * 同步组织数据， 每小时的第 15 分钟执行
 * Class ExportCrontab
 * @package App\Component\account\src\crontab
 */
class AccountOrgSyncCrontab extends BaseCrontab
{
    public $name = 'crontab:account-org-sync';

    public $description = '同步组织数据';

    //每小时的第 15 分钟执行
    public $cron = '15 * * * *';

    public function handle()
    {
        vss_service()->getAccountSyncService()->syncOrg();
    }
}
