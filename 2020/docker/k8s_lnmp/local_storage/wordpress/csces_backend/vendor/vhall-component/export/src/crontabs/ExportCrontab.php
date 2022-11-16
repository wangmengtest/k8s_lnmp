<?php


namespace vhallComponent\export\crontabs;

use vhallComponent\decouple\crontabs\BaseCrontab;

/**
 * 异步导出， 默认每分钟执行一次
 * Class ExportCrontab
 * @package vhallComponent\export\crontab
 */
class ExportCrontab extends BaseCrontab
{
    public $name = 'crontab:export';

    public $description = '异步导出';

    public function handle()
    {
        vss_service()->getExportService()->execute();
    }
}
