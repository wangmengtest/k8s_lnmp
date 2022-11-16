<?php


namespace App\Component\account\src\crontabs;

use vhallComponent\decouple\crontabs\BaseCrontab;

/**
 * 同步用户数据， 每小时的第 10 分钟执行
 * Class ExportCrontab
 * @package App\Component\account\src\crontab
 */
class AccountUserSyncCrontab extends BaseCrontab
{
    public $name = 'crontab:account-user-sync';

    public $description = '同步用户数据';

    //每小时的第 10 分钟执行
    public $cron = '10 * * * *';

    public function handle()
    {
        vss_logger()->info('csces-account-sync', ['action'=>'handle']); //日志
        vss_service()->getAccountSyncService()->syncUser();
    }
}
