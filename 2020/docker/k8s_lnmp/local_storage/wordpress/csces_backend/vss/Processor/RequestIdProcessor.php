<?php

namespace Vss\Processor;

use Monolog\Processor\ProcessorInterface;
use Vss\Utils\RequestIdUtil;

/**
 * 向日志中增加 request_id
 * Class RequestIdProcessor
 * @package Vss\Processor
 */
class RequestIdProcessor implements ProcessorInterface
{
    public function __invoke(array $record)
    {
        if (isset($record['context']['result']['request_id'])) {
            unset($record['context']['result']['request_id']);
        }

        if (isset($record['context']['request-id'])) {
            unset($record['context']['request-id']);
        }

        $record['extra']['request_id'] = RequestIdUtil::get();

        return $record;
    }
}
