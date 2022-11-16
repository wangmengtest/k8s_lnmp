<?php

namespace vhallComponent\decouple\crontabs;

use RuntimeException;
use vhallComponent\decouple\commands\BaseCommand;

abstract class BaseCrontab extends BaseCommand
{
    /**
     * 每个任务必须唯一
     * @var string $name
     */
    public $name = '任务的名称';

    /**
     * 没有实际用处，给程序员看的
     * @var string
     */
    public $description = '任务的描述';

    /**
     * 该定时任务是否开启
     * @var bool
     */
    public $enable = true;

    /**
     * 是否只在一台机器上执行
     * 缓存驱动不能是本地存储，必须是多台服务器共享的存储，如 db,redis,memcached 等
     * @var bool
     */
    public $onOneService = true;

    /**
     * 是否是单例执行
     * false 表示上一个任务没有结束前，可以启动下一个任务，
     * true 则必须等待上一个任务结束才会启动下一个任务
     * @var bool
     */
    public $singleton = true;

    /**
     * 返回定时任务执行频次, 按 linux 定时任务格式定义
     * @var string $cron
     */
    public $cron = '* * * * *';

    /**
     * 任务执行入口
     * @auther yaming.feng@vhall.com
     * @date 2021/5/6
     * @return mixed
     */
    abstract public function handle();

    /**
     * 命令行加锁，并在进程结束使自动释放锁，加锁失败会抛出异常，终止程序
     *
     * @param string $key    锁键
     * @param int    $expire 过期时间，秒
     *
     * @return string
     *
     * @since  2021/6/17
     * @author fym
     */
    public function autoLock(string $key, int $expire = 60): string
    {
        $value = uniqid();

        // 进程结束删除锁
        register_shutdown_function(function () use ($key, $value) {
            vss_redis()->unlock($key, $value);
        });

        // 加锁
        if (vss_redis()->lock($key, $expire, $value)) {
            $this->infoLog('already running');
            throw new RuntimeException('加锁失败');
        }

        return $value;
    }
}
