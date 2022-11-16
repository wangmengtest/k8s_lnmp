<?php
namespace vhallComponent\pendant\jobs;

use \Vss\Queue\JobStrategy;

class SaveOperateStatsJob extends JobStrategy
{
    public $pendant;

    public function __construct($pendant)
    {
        $this->pendant = $pendant;
    }

    public function handle()
    {
        vss_service()->getPendantService()->saveOperateStats($this->pendant);
    }
}
