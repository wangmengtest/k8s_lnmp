<?php

namespace Vss\Traits;

/**
 * 单例支持
 * Trait SingletonTrait
 * @package Vss\Traits
 */
trait SingletonTrait
{
    protected static $instance;/*服务单例对象*/

    /**
     * @return static 单例对象实例
     */
    public static function getInstance()
    {
        if (!isset(self::$instance) || !self::$instance instanceof static) {
            self::$instance = new static();
        }
        return self::$instance;
    }
}
