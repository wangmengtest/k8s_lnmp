<?php

namespace vhallComponent\decouple\commands;

use Exception;
use Illuminate\Support\Str;

/**
 * 组件命令行调度加载
 *
 * Class ScheduleCommand
 * @package vhallComponent\decouple\commands
 */
class ScheduleCommand
{
    protected static $commandList = [];

    protected static $module = 'commands';

    /**
     * 加载组件命令
     * @auther yaming.feng@vhall.com
     * @date 2021/5/6
     * @return array
     * @throws Exception
     */
    public static function load(): array
    {
        $module = static::$module;
        if (isset(static::$commandList[$module])) {
            return static::$commandList[$module];
        }

        $dir = '/src/' . $module;

        static::$commandList[$module] = [];

        foreach (component_paths() as $componentPath) {
            foreach (glob($componentPath . '/*') as $component) {
                if (!static::isValidComponent($component, $dir)) {
                    continue;
                }

                static::loadComponentCommand($component);
            }
        }

        return static::$commandList[$module];
    }

    /**
     * 检查该命令类是否需要加载
     * @auther yaming.feng@vhall.com
     * @date 2021/5/27
     *
     * @param $command
     *
     * @return bool
     */
    protected static function isLoad($command): bool
    {
        if (!($command instanceof BaseCommand)) {
            return false;
        }

        return true;
    }

    /**
     * 检查是否是一个有效的组件
     *
     * @param string $component
     * @param string $dir
     *
     * @return bool
     * @since  2021/7/7
     * @author fym
     */
    protected static function isValidComponent(string $component, string $dir): bool
    {
        if (!is_dir($component)) {
            return false;
        }

        if (!is_dir($component . $dir)) {
            return false;
        }

        if (is_file($component . '/.ignore')) {
            return false;
        }

        $componentName = Str::camel(basename($component));
        if ($componentName == 'decouple') {
            return false;
        }

        return true;
    }

    /**
     * 加载组件中的命令行
     *
     * @param string $component
     *
     * @throws Exception
     * @since  2021/7/7
     * @author fym
     */
    protected static function loadComponentCommand(string $component)
    {
        $dirName       = static::$module;
        $dir           = '/src/' . $dirName;
        $componentName = Str::camel(basename($component));

        foreach (glob($component . $dir . '/*.php') as $file) {
            $commandClass = static::getClass($file, $componentName, $dirName);

            if (!class_exists($commandClass)) {
                echo 'class load error: ' . $commandClass . PHP_EOL;
                continue;
            }

            $command = vss_make($commandClass);

            if (!static::isLoad($command)) {
                continue;
            }

            static::$commandList[$dirName][] = $commandClass;
        }
    }

    /**
     * 获取待加载类
     *
     * @param string $path
     * @param string $componentName
     * @param string $module
     *
     * @return string
     * @author fym
     * @since  2021/7/7
     */
    protected static function getClass(string $path, string $componentName, string $module): string
    {
        $isVendor  = stripos($path, 'vendor') !== false;
        $className = str_replace('.php', '', basename($path));

        if ($isVendor) {
            return "\\vhallComponent\\$componentName\\$module\\$className";
        }
        return "App\\Component\\$componentName\\src\\$module\\$className";
    }
}
