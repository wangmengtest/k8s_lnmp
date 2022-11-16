<?php

namespace vhallComponent\decouple\crontabs;

use Exception;
use vhallComponent\decouple\commands\ScheduleCommand;

/**
 * 自动调度组件的定时任务
 * Class ScheduleCrontab
 * @package vhallComponent\decouple\crontab
 */
class ScheduleCrontab extends ScheduleCommand
{
    protected static $module = 'crontabs';

    /**
     *  调度任务
     * @auther yaming.feng@vhall.com
     * @date 2021/5/6
     *
     * @param callable $callback
     *
     * @throws Exception
     */
    public static function schedule(callable $callback)
    {
        $crontabList = static::load();
        foreach ($crontabList as $crontab) {

            /**
             * @var BaseCrontab $cron
             */
            $cron = vss_make($crontab);

            call_user_func($callback, $cron);
        }
    }

    /**
     * 检查命令是否要加载
     * @auther yaming.feng@vhall.com
     * @date 2021/5/27
     *
     * @param $command
     *
     * @return bool
     */
    protected static function isLoad($command): bool
    {
        if (!($command instanceof BaseCrontab)) {
            return false;
        }

        if (!$command->enable) {
            return false;
        }

        return true;
    }
}
