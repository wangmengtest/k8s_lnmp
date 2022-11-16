<?php

namespace App\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Throwable;

class QueryListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param QueryExecuted $event
     * @return void
     */
    public function handle(QueryExecuted $event)
    {
        $sql = str_replace("?", "'%s'", $event->sql);
        try {
            $sql = sprintf($sql, ...$event->bindings);
            vss_logger()->info("执行sql: " . $sql);
        } catch (Throwable $e) {
            vss_logger()->info("执行 sql: " . $sql, $event->bindings);
        }
    }
}
