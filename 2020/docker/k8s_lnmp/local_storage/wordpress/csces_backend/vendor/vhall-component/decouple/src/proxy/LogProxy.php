<?php

namespace vhallComponent\decouple\proxy;

/**
 * @method \Psr\Log\LoggerInterface stack(array $channels, string $channel = null)
 * @method void alert(string $message, array $context = [])
 * @method void critical(string $message, array $context = [])
 * @method void debug(string $message, array $context = [])
 * @method void emergency(string $message, array $context = [])
 * @method void error(string $message, array $context = [])
 * @method void info(string $message, array $context = [])
 * @method void log($level, string $message, array $context = [])
 * @method void notice(string $message, array $context = [])
 * @method void warning(string $message, array $context = [])
 * @method void write(string $level, string $message, array $context = [])
 * @method void listen(\Closure $callback)
 *
 * @see \Illuminate\Log\Logger
 */
class LogProxy
{
    protected static $instances = [];
    protected        $logger;

    public static function getInstance(string $channel)
    {
        if (!isset(self::$instances[$channel])) {
            self::$instances[$channel] = new static($channel);
        }

        return self::$instances[$channel];
    }

    protected function __construct(string $channel)
    {
        $this->logger = app('log')->channel($channel);
        $processors   = config("logging.channels.{$channel}.processors");

        if (is_array($processors)) {
            foreach ($processors as $processor) {
                $handler = $processor;
                $params  = [];
                if (is_array($processor)) {
                    if (!isset($processor['handler'])) {
                        throw new \Exception('日志处理器配置格式错误: ' . $channel . '.processors');
                    }
                    $handler = $processor['handler'];
                    $params  = $processor['params'] ?? [];
                }
                $this->logger->pushProcessor(vss_make($handler, $params));
            }
        }

        return $this;
    }

    public function __call($method, $args)
    {
        $this->logger->{$method}(...$args);
    }
}
