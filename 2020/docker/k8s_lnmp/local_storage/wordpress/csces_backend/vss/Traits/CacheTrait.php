<?php

namespace Vss\Traits;

use vhallComponent\decouple\proxy\CacheProxy;

trait CacheTrait
{
    protected $cacheExpire = [];
    protected $cacheUtilInstants;
    protected $cacheOpType = 'opModel';

    public function getCache($type, $key, $callback)
    {
        $value = $this->getCacheUtils($type)->get($key);
        if (!$value) {
            $value = $callback();
            if (!is_null($value)) {
                $this->putCache($type, $key, $value);
            }
        }
        return $value;
    }

    public function putCache($type, $key, $value)
    {
        $this->getCacheUtils($type)->setItem($key, $value);
    }

    public function deleteCache($type, $key)
    {
        $cacheUtils = $this->getCacheUtils($type);
        $cacheUtils->deleteItem($key);
    }

    public function clearAllCache($type)
    {
        $cacheUtils = $this->getCacheUtils($type);
        $cacheUtils->clear();
    }

    protected function getCacheUtils($type)
    {
        if (!$this->cacheUtilInstants[$type]) {
            $seconds                        = isset($this->cacheExpire[$type]) ? $this->cacheExpire[$type] : 86400;
            $this->cacheUtilInstants[$type] = new CacheProxy($this->cacheOpType . class_basename($this) . ucfirst($type),
                $seconds);
        }
        return $this->cacheUtilInstants[$type];
    }
}
