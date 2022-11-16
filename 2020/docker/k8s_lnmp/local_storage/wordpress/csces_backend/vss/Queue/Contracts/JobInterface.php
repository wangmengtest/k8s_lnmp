<?php

namespace Vss\Queue\Contracts;

use Throwable;

interface JobInterface
{

    /**
     * 消费任务
     * @auther yaming.feng@vhall.com
     * @date 2021/4/29
     * @return mixed
     */
    public function handle();

    /**
     * 任务失败后执行
     * Laravel 下只会在第一次失败时回调
     * Hyperf 下可以通过事件调用此函数
     * @auther yaming.feng@vhall.com
     * @date 2021/4/29
     *
     * @param Throwable $e
     *
     * @return mixed
     */
    public function failed(Throwable $e);
}
