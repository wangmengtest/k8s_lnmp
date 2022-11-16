<?php

namespace App\Component\room\src\crontabs;

use App\Component\room\src\constants\RoomConstant;
use vhallComponent\decouple\crontabs\BaseCrontab;

/**
 * 房间全量检测用户信息
 * Class StatSyncCrontab
 * @package App\Component\room\src\crontabs
 */
class AccountSyncCrontab extends BaseCrontab
{
    public $name = 'crontab:room-account-sync';

    public $description = '房间全量检测用户信息';

    public $cron = '*/10 * * * *';

    public function handle()
    {
        vss_service()->getRoomSyncService()->syncAccountName();
        $this->infoLog('success');
    }
}
