<?php


namespace App\Component\export\src\crontabs;

use Illuminate\Support\Facades\Log;
use vhallComponent\decouple\crontabs\BaseCrontab;

/**
 * 异步导出， 默认每分钟执行一次
 * Class ExportCrontab
 * @package App\Component\export\src\crontab
 */
class ExportCrontab extends BaseCrontab
{
    public $name = 'crontab:export';

    public $description = '异步导出';

    public $cron = '* * * * *';

    public function handle()
    {
        Log::info("exec crontab:export");
        vss_service()->getExportService()->execute();
    }
}
