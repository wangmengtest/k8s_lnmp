<?php

namespace vhallComponent\decouple\contracts;

use Vss\Queue\JobStrategy;

/**
 * 队列接口
 * Interface QueueInteface
 */
interface QueueInterface
{
    /**
     * 初始化队列通道
     * QueueInteface constructor.
     * @param string $channel
     */
    public function __construct(string $channel);

    /**
     * 向队列中推送任务
     * @auther yaming.feng@vhall.com
     * @date 2021/5/10
     * @param JobStrategy $job 推送的任务对象
     * @param int $delay 延迟时间，单位秒
     * @return mixed
     */
    public function push(JobStrategy $job, int $delay = 0);

    /**
     * 队列中任务数
     * @auther yaming.feng@vhall.com
     * @date 2021/5/10
     * @return int
     */
    public function size(): int;

    /**
     * 队列是否为空
     * @auther yaming.feng@vhall.com
     * @date 2021/5/10
     * @return bool
     */
    public function isEmpty(): bool;
}
