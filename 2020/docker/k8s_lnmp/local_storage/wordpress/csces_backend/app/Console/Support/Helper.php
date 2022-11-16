<?php

namespace App\Console\Support;

use Illuminate\Support\Str;

class Helper
{
    /**
     * 蛇形命名转小驼峰
     * @auther yaming.feng@vhall.com
     * @date 2021/2/1
     *
     * @param $value
     *
     * @return string
     */
    public static function camel($value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));
        $value = str_replace(' ', '', $value);
        return lcfirst($value);
    }

    /**
     * 组件目录 vendor 下
     * @return string
     * @since  2021/7/2
     * @author fym
     */
    public static function getComponentDir(): string
    {
        return base_path('vendor/vhall-component');
    }

    /**
     * 组件开发目录 app 下
     * @return string
     * @since  2021/7/2
     * @author fym
     */
    public static function getComponentDevelopDir(): string
    {
        return app_path('Component');
    }

    /**
     * 组件开发和发布的目录列表
     * @return string[]
     * @since  2021/7/2
     * @author fym
     */
    public static function getComponentDirList(): array
    {
        return [
            self::getComponentDevelopDir(),
            self::getComponentDir(),
        ];
    }

    /**
     *  检查组件是否已存在
     *
     * @param string $componentName
     *
     * @return string
     * @author fym
     * @since  2021/7/2
     */
    public static function componentIsExist(string $componentPath): string
    {
        foreach (self::getComponentDirList() as $componentDir) {
            $name  = basename($componentPath);
            $names = [
                $name,
                Str::snake($name),
                Str::studly($name),
                str::camel($name)
            ];

            foreach ($names as $name) {
                $path = $componentDir . '/' . $name;
                if (is_dir($path)) {
                    return $path;
                }
            }
        }
        return '';
    }

    public static function plural($text, $parse = "strtolower")
    {
        return $parse($text . 's');
    }

    public static function matchComponent($onlyComponent, $componentPath, bool $ignore = true): bool
    {
        $componentName = strtolower(basename($componentPath));
        // 该组件是与框架相关的组件，不是功能组件，不需要处理
        if ($ignore && is_file($componentPath . '/.ignore')) {
            return false;
        }

        return !$onlyComponent
            || $onlyComponent == 'all'
            || $onlyComponent == $componentName;
    }

    public static function mkdir($dir): bool
    {
        return is_dir($dir) || mkdir($dir, 0777, true);
    }

    /**
     * 删除目录或文件
     *
     * @param string $filePath 文件路径
     * @param array  $except   要排除的路径，相对于 $filePath 的路径
     * @param string $rootPath 等于用户指定的 $filePath , 递归的时候使用
     *
     * @param array  $except
     *
     * @since  2021/7/15
     * @author fym
     */
    public static function unlink(string $filePath, array $except = [], string $rootPath = '')
    {
        if (is_file($filePath)) {
            unlink($filePath);
            return;
        }
        if (!$rootPath) {
            $rootPath = $filePath;
        }

        // 查询以点开头的隐藏文件
        foreach (glob($filePath . '/\.*') as $file) {
            $relativePath = ltrim(str_replace($rootPath, '', $file), '/');
            if (in_array(basename($file), ['.', '..']) || in_array($relativePath, $except)) {
                continue;
            }
            self::unlink($file, $except, $filePath);
        }

        foreach (glob($filePath . '/*') as $file) {
            $relativePath = ltrim(str_replace($rootPath, '', $file), '/');
            if (in_array($relativePath, $except)) {
                continue;
            }
            self::unlink($file, $except, $filePath);
        }

        is_dir($filePath) && rmdir($filePath);
    }

    /**
     * 检查字符串结尾
     * @auther yaming.feng@vhall.com
     * @date 2021/3/17
     *
     * @param string       $str        要检查的字符串
     * @param array|string $suffix     要检查的后缀
     * @param bool         $ignoreCase 是否忽略大小写
     */
    public static function hasSuffix(string $str, $suffix, bool $ignoreCase = false): bool
    {
        $strLen = strlen($str);
        $suffix = (array)$suffix;

        if ($ignoreCase) {
            $str    = strtolower($str);
            $suffix = array_map('strtolower', $suffix);
        }

        foreach ($suffix as $s) {
            if ($strLen - strlen($s) == strrpos($str, $s)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 删除指定的后缀
     * @auther yaming.feng@vhall.com
     * @date 2021/3/15
     *
     * @param string $str
     * @param string $suffix
     *
     * @return false|string
     */
    public static function removeSuffix(string $str, string $suffix)
    {
        $lastIndex = strlen($str) - strlen($suffix);
        if (substr($str, $lastIndex) == $suffix) {
            return substr($str, 0, $lastIndex);
        }
        return $str;
    }

    /**
     * 对特殊的组件名进行处理
     * @auther yaming.feng@vhall.com
     * @date 2021/3/23
     *
     * @param string $componentName
     * @param string $filePath
     *
     * @return string
     */
    public static function getComponentName(string $componentName, string $filePath): string
    {
        $oldComponentName = $componentName;
        if (strpos($componentName, '-') !== false || strpos($componentName, '_') !== false) {
            if (is_file($filePath)) {
                $reg     = "/([\s\S]+?)(namespace\s+vhallComponent\\\)(\w+)([\s\S]+)/i";
                $content = file_get_contents($filePath);
                preg_match_all($reg, $content, $matches);
                $componentName = $matches[3][0] ?? $oldComponentName;
            }

            if ($componentName == $oldComponentName) {
                $componentName = self::camel($oldComponentName);
            }
        }

        return $componentName;
    }

    /**
     * 如果数组中不存在，则添加到数组中
     * @auther yaming.feng@vhall.com
     * @date 2021/3/23
     *
     * @param $arr
     * @param $item
     *
     * @return bool
     */
    public static function addNotExist(&$arr, $item): bool
    {
        if (in_array($item, $arr)) {
            return false;
        }

        $arr[] = $item;
        return true;
    }

    public static function getPublishDir($modulePath, $paths): string
    {
        array_unshift($paths, app_path($modulePath));
        return implode('/', $paths);
    }

    /**
     * 复制文件夹
     *
     * @param $src
     * @param $dst
     *
     * @author fym
     * @since  2021/6/24
     */
    public static function copyDir($src, $dst)
    {
        self::mkdir($dst);
        foreach (glob($src . '/*') as $file) {
            if (is_dir($file)) {
                self::copyDir($file, $dst . str_replace($src, '', $file));
                continue;
            }

            copy($file, $dst . '/' . basename($file));
        }

        return true;
    }
}
