<?php

namespace vhallComponent\decouple\proxy;

use Illuminate\Support\Facades\DB;
use Vss\Traits\SingletonTrait;

/**
 * @method \Illuminate\Database\ConnectionInterface connection(string $name = null)
 * @method \Illuminate\Database\Query\Builder table(string $table, string $as = null)
 * @method \Illuminate\Database\Query\Expression raw($value)
 * @method array getQueryLog()
 * @method array prepareBindings(array $bindings)
 * @method array pretend(\Closure $callback)
 * @method array select(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method bool insert(string $query, array $bindings = [])
 * @method bool logging()
 * @method bool statement(string $query, array $bindings = [])
 * @method bool unprepared(string $query)
 * @method int affectingStatement(string $query, array $bindings = [])
 * @method int delete(string $query, array $bindings = [])
 * @method int transactionLevel()
 * @method int update(string $query, array $bindings = [])
 * @method mixed selectOne(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method mixed transaction(\Closure $callback, int $attempts = 1)
 * @method string getDefaultConnection()
 * @method void afterCommit(\Closure $callback)
 * @method void beginTransaction()
 * @method void commit()
 * @method void enableQueryLog()
 * @method void disableQueryLog()
 * @method void flushQueryLog()
 * @method void listen(\Closure $callback)
 * @method void rollBack(int $toLevel = null)
 * @method void setDefaultConnection(string $name)
 *
 * @see \Illuminate\Database\DatabaseManager
 * @see \Illuminate\Database\Connection
 */
class DBProxy
{
    use SingletonTrait;

    private $db;

    private function __construct()
    {
        $this->db = vss_make('db');
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->db, $name], $arguments);
    }
}
