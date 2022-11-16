<?php

namespace vhallComponent\decouple\proxy;

use Vss\Traits\SingletonTrait;

/**
 * Redis 代理类
 * Class RedisProxy
 * @package vhallComponent\decouple\proxy
 * @method persist($beginDateCacheKey)
 */
class RedisProxy
{
    use SingletonTrait;

    protected $redis;

    public function __construct()
    {
        // 底层使用 Laravel 的 Redis
        $this->redis = app('redis.connection');
    }

    /**
     * 加锁
     * @auther yaming.feng@vhall.com
     * @date 2021/4/8
     *
     * @param string $key
     * @param int    $lockTime         单位 秒
     * @param mixed  $value
     * @param string $expireResolution 过期时间的单位， EX 秒， PX 毫秒
     *
     * @return bool true 加锁失败， false 加锁成功
     */
    public function lock($key, $lockTime = 0, $value = 1, $expireResolution = 'EX')
    {
        return !$this->setnx($key, $value, $lockTime, $expireResolution);
    }

    /**
     * 释放锁
     *
     * @param     $key
     * @param int $value
     *
     * @return mixed
     * @since  2021/6/17
     * @author fym
     */
    public function unlock($key, $value = 1)
    {
        $luaScript = <<<EOF
if redis.call("get",KEYS[1]) == ARGV[1] then
    return redis.call("del",KEYS[1])
else
    return 0
end
EOF;
        $value     = is_numeric($value) ? $value : serialize($value);
        return $this->eval($luaScript, [$key, $value], 1);
    }

    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/4/12
     *
     * @param     $key
     * @param int $lockTime
     * @param int $waitTime
     */
    public function lockWait($key, $lockTime = 0, $waitTime = 1000)
    {
        if ($this->lock($key, $lockTime)) {
            usleep($waitTime);
        }
    }

    public function get($key)
    {
        $val = $this->redis->get($key);
        if (!is_numeric($val)) {
            $val = unserialize($val);
        }
        return $val;
    }

    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/4/13
     *
     * @param string      $key
     * @param string      $value
     * @param int         $expire           过期时间
     * @param string      $expireResolution 过期时间的单位，EX: 秒， PX: 毫秒
     * @param null|string $flag             选项: EX: 只有键key不存在的时候才会设置key的值，XX: 只有键key不存在的时候才会设置key的值
     *
     * @return mixed
     */
    public function set($key, $value, $expire = 1800, $expireResolution = 'EX', $flag = null)
    {
        $value = is_numeric($value) ? $value : serialize($value);
        if ($flag) {
            // $flag 如果为空，会报语法错误
            return $this->redis->set($key, $value, $expireResolution, $expire, $flag);
        }
        return $this->redis->set($key, $value, $expireResolution, $expire);
    }

    public function setnx($key, $value, $expire = null, $expireResolution = 'EX')
    {
        if ($expire) {
            return $this->set($key, $value, $expire, $expireResolution, 'NX');
        }

        $value = is_numeric($value) ? $value : serialize($value);

        return $this->redis->setnx($key, $value);
    }

    /**
     * 兼容性增加
     * 对项目中的 eval 用法做兼容
     * @auther yaming.feng@vhall.com
     * @date 2021/4/21
     *
     * @param string $luaScript
     * @param array  $keyOrAvg
     * @param int    $keyNum
     */
    public function eval(string $luaScript, array $keyOrAvg, int $keyNum)
    {
        return $this->redis->eval($luaScript, $keyNum, ...$keyOrAvg);
    }

    /**
     * 清空当前库所有缓存，慎用
     * @auther yaming.feng@vhall.com
     * @date 2021/4/8
     */
    public function clear()
    {
        return $this->redis->flushdb();
    }

    /**
     * 批量从队列头部取出多个数据
     *
     * @param string $key     要取出队列名称
     * @param int    $len     要取出队列的长度
     * @param bool   $block   是否阻塞式读取
     * @param int    $timeout 超时时间，单位秒
     *
     * @return array $data 从队列里取出的数据
     */
    public function batchPop(string $key, int $len = 1, bool $block = true, $timeout = 1)
    {
        $data = [];
        for ($i = 0; $i < $len; $i++) {
            $item = $block
                ? $this->redis->blpop($key, $timeout) // 返回的是一个数组 [key, val]
                : $this->redis->lpop($key);  // 返回的是一个值 $val

            $item = is_array($item) ? $item[1] : $item;
            if ($item === null) {
                return $data;
            }

            $data[] = is_numeric($item) ? $item : json_decode($item, true);
        }
        return $data;
    }

    /**
     * 入队列操作,向队列尾部压人一个数据
     *
     * @param string $key   队列名称
     * @param mixed  $value 要入队列的数据
     *
     * @return bool 成功返回true,失败返回false
     */
    public function push(string $key, $value)
    {
        $value = is_numeric($value) ? $value : json_encode($value,
            JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $this->redis->rPush($key, $value);
    }

    public function __call($method, $args)
    {
        $method = $methodMap[$method] ?? $method;
        return call_user_func_array([$this->redis, $method], $args);
    }
}
