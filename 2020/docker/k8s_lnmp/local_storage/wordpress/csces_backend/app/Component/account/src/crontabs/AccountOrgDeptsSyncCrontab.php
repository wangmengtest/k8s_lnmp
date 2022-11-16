<?php


namespace App\Component\account\src\crontabs;

use vhallComponent\decouple\crontabs\BaseCrontab;

/**
 * 同步组织架构同级及以下部门数据， 2点 第 25 分钟执行
 * Class ExportCrontab
 * @package App\Component\account\src\crontab
 */
class AccountOrgDeptsSyncCrontab extends BaseCrontab
{
    public $name = 'crontab:account-org-depts-sync';

    public $description = '同步组织架构同级及以下部门与组织';

    //2点 第 25 分钟执行
    public $cron = '25 2 * * *';

    public function handle()
    {
        vss_logger()->info('csces-accountorgdeptssync', ['action'=>'handle', 'date'=>date('Y-m-d H:i:s')]); //日志
        vss_service()->getAccountSyncService()->syncOrgsDepts();
    }
}
