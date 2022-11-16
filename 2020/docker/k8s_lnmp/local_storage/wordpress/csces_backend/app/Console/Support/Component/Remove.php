<?php

namespace App\Console\Support\Component;

use App\Console\Support\Helper;

class Remove extends Publish
{
    public function process(string $componentPath): bool
    {
        $componentName = basename($componentPath);

        // 组件被发布后的目录
        $publishComponentPath = $this->publishDir . DIRECTORY_SEPARATOR . $componentName;

        // 检查组件是否存在
        if (!is_dir($publishComponentPath)) {
            $this->command->error("{$publishComponentPath}: not exist.");
            return false;
        }

        // 1. 删除组价目录到框架目录下
        Helper::unlink($publishComponentPath);

        // 2. 修改 modules 下 Controller 的继承类的命名空间
        $this->updateNamespace(app_path("Http/Modules"), $componentName);

        // 3. 修改其他发布过的组件对当前组件引用的命名空间
        $this->updateOtherComponentUseNamespace($this->publishDir, $componentName);

        // 4. 修改 ModelTrait 和 ServiceTrait 里该组件的命名空间
        $this->updateNamespace(app_path("Traits"), $componentName);

        // 5. 删除 常量 和 规则的 类别名
        $this->updateClassAlias($componentPath);

        return true;
    }

    /**
     * 修改 常量 和 验证规则 的类别名
     * @auther yaming.feng@vhall.com
     * @date 2021/4/27
     *
     * @param string $componentPath
     */
    protected function updateClassAlias(string $componentPath)
    {
        $classAlias = config('classalias');

        $componentName = basename($componentPath);

        $namespacePrefix = "App\\Component\\{$componentName}\\src";
        foreach ($classAlias as $module => $map) {
            foreach ($map as $class => $alias) {
                if (strpos($class, $namespacePrefix) === 0) {
                    unset($classAlias[$module][$class]);
                }
            }
        }

        $this->outputClassAlias($classAlias);
    }

    protected function replaceNamespace($file, $componentName)
    {
        $content = file_get_contents($file);

        $content = str_ireplace(
            "App\\Component\\{$componentName}\\src\\",
            "vhallComponent\\{$componentName}\\",
            $content
        );

        file_put_contents($file, $content);

        $this->verbose && $this->command->info($file);
    }
}
