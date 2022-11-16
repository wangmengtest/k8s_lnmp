<?php

namespace vhallComponent\decouple\proxy;

use Illuminate\Support\Facades\Queue;
use vhallComponent\decouple\contracts\QueueInterface;
use Vss\Queue\JobStrategy;

class QueueProxy implements QueueInterface
{
    protected static $instances = [];

    protected $channel;

    public static function getInstance(string $channel)
    {
        if (!isset(self::$instances[$channel])) {
            self::$instances[$channel] = new static($channel);
        }

        return self::$instances[$channel];
    }

    public function __construct(string $channel)
    {
        $this->channel = $channel;
    }

    public function push(JobStrategy $job, int $delay = 0)
    {
        $dis = dispatch($job)->delay($delay);
        if ($this->channel != 'default') {
            $dis->onQueue($this->channel);
        }
    }

    public function size(): int
    {
        return Queue::size($this->channel);
    }

    public function isEmpty(): bool
    {
        return !$this->size();
    }
}
