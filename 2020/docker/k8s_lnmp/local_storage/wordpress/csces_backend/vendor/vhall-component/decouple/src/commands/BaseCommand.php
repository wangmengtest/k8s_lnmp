<?php

namespace vhallComponent\decouple\commands;

use Exception;
use Illuminate\Console\Command;
use Vss\Exceptions\ResponseException;

/**
 *
 * 组件命令行基类
 *
 * Class BaseCommand
 * @package vhallComponent\decouple\commands
 */
abstract class BaseCommand extends Command
{
    /**
     * 每个命令必须唯一
     * @var string $name
     */
    public $name = '命令的名称';

    /**
     * 没有实际用处，给程序员看的
     * @var string
     */
    public $description = '命令的描述';

    private $debug;

    public function __construct()
    {
        $this->debug = env('APP_ENV') == 'localhost';
        parent::__construct();
    }

    /**
     * 任务执行入口
     * @auther yaming.feng@vhall.com
     * @date 2021/5/27
     * @return mixed
     */
    abstract public function handle();

    /**
     * 日志前缀
     * @auther yaming.feng@vhall.com
     * @date 2021/5/27
     * @return string
     */
    protected function logPrefix(): string
    {
        // 冒号对 elk 搜索不友好
        $name = str_replace(':', '-', $this->name);
        return "[$name]: ";
    }

    /**
     * 输出异常消息
     * @auther yaming.feng@vhall.com
     * @date 2021/5/26
     *
     * @param Exception $e
     * @param string     $msg
     */
    protected function exceptionLog(Exception $e, string $msg = '')
    {
        $msg   = $this->logPrefix() . $msg;
        $error = [];
        if ($e instanceof ResponseException) {
            $error = $e->getData();
        }
        if ($this->debug) {
            $this->error($msg . ' ' . $e->getMessage() . var_export($error, true));
            return;
        }

        vss_logger()->error($msg, [
            'error' => [
                'code'     => $e->getCode(),
                'msg'      => $e->getMessage(),
                'previous' => $error
            ]
        ]);
    }

    /**
     * 错误日志
     * @auther yaming.feng@vhall.com
     * @date 2021/5/27
     *
     * @param string $msg
     * @param array  $context
     */
    protected function errorLog(string $msg, array $context = [])
    {
        $msg = $this->logPrefix() . $msg;
        if ($this->debug) {
            $this->error($msg . json_encode($context));
            return;
        }

        vss_logger()->error($msg, $context);
    }

    /**
     * 输出普通消息
     * @auther yaming.feng@vhall.com
     * @date 2021/5/26
     *
     * @param string $msg
     * @param array  $context
     */
    protected function infoLog(string $msg, array $context = [])
    {
        $msg = $this->logPrefix() . $msg;
        if ($this->debug) {
            $context = $context ? PHP_EOL . var_export($context, true) : '';
            $this->info($msg . $context);
            return;
        }

        vss_logger()->info($msg, $context);
    }
}
