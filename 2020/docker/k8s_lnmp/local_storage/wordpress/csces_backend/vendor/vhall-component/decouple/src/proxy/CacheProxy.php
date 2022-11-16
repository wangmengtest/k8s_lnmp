<?php

namespace vhallComponent\decouple\proxy;

use Vss\Traits\SingletonTrait;

/**
 * 缓存代理类
 * Class CacheProxy
 * @package Vss\Utils
 */
class CacheProxy
{
    use SingletonTrait;

    public $cache;

    private $namespace;

    protected $defaultLifetime = null;

    /**
     * @param string $namespace 缓存池
     * @param int $defaultLifetime 缓存时间
     *
     * @return $this
     */
    public function __construct($namespace = '', $defaultLifetime = null)
    {
        $this->namespace       = $namespace;
        $this->defaultLifetime = $defaultLifetime;
        $this->cache           = cache(); // 使用 Laravel 缓存
        return $this;
    }

    protected function getKey($key)
    {
        return $this->namespace . ':' . $key;
    }

    /**
     * 重写set方法
     *
     * @param $key
     * @param $value
     * @return bool
     * @throws
     */
    public function setItem($key, $value)
    {
        $key = $this->getKey($key);
        return $this->cache->put($key, $value, $this->defaultLifetime);
    }

    /**
     * @param $key
     * @return
     * @throws
     */
    public function get($key)
    {
        $key = $this->getKey($key);
        return $this->cache->get($key);
    }

    public function set($key, $value, $ttl = null)
    {
        $key = $this->getKey($key);
        return $this->cache->put($key, $value, $ttl);
    }

    /**
     * @param $key
     * @return bool
     */
    public function deleteItem($key)
    {
        $key = $this->getKey($key);
        return $this->cache->delete($key);
    }

    public function clear()
    {
        return $this->cache->clear();
    }
}
