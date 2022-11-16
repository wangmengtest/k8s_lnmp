<?php

namespace Vss\Queue;

use Throwable;
use vhallComponent\decouple\jobs\BaseJob;

abstract class JobStrategy extends BaseJob
{

    /**
     * 最大尝试次数，子类可重写
     * 避免设置为 0, laravel 下设置为 0时， 当执行一直失败时，会一直重复执行
     *
     * @var int
     */
    public $tries = 1;

    /**
     * 任务失败执行的回调，子类可重写
     * hyperf 下，可以在 FailedHandle 事件中调用该方法， 来实现任务失败自动执行
     * @auther yaming.feng@vhall.com
     * @date 2021/4/29
     * @param Throwable $e
     */
    public function failed(Throwable $e)
    {
        vss_logger()->error($this->logPrefix() . '队列任务执行失败', [
            'msg' => $e->getMessage()
        ]);
    }

    protected function logPrefix(): string
    {
        return "[jobs-" . get_class($this) . "]: ";
    }

    protected function info($msg, $context = [])
    {
        vss_logger()->info($this->logPrefix() . $msg, $context);
    }

    protected function error($msg, $context = [])
    {
        vss_logger()->error($this->logPrefix() . $msg, $context);
    }
}
