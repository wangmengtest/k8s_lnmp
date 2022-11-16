<?php

namespace App\Console\Support\Generators\Publish\Contracts;

use App\Console\Support\Helper;

abstract class GeneratorTrait extends GeneratorBase
{
    public function handle()
    {
        $namespaces     = [];
        $className      = [];
        $componentNames = []; // 记录已经扫描过的组件名，防止组件名冲突

        $componentList = Helper::getComponentDirList();
        $modulePlural  = Helper::plural($this->getModule());

        foreach ($componentList as $componentDir) {
            // 1. 发布 services or models 文件
            foreach (glob($componentDir . '/*') as $component) {
                if (!is_dir($component)) {
                    continue;
                }

                if (!Helper::matchComponent($this->component, $component)) {
                    continue;
                }

                $componentNamespaces = [];
                $componentClassName  = [];

                $componentName = basename($component);
                // 组件已经扫描过，则跳过 app/Component 下存在的组件会覆盖 vhall-component 下的组件
                if (in_array($componentName, $componentNames)) {
                    continue;
                }
                $componentNames[] = $componentName;

                $componentServiceDir = "$component/src/$modulePlural";
                $this->command->comment("$this->operation $modulePlural: " . basename($component));
                foreach (glob($componentServiceDir . '/*') as $serviceFile) {
                    [$serviceDirName, $serviceClassName] = $this->{$this->operation}($component, $serviceFile);

                    if ($serviceClassName) {
                        $isVendor              = strpos($serviceFile, 'vendor') !== false;
                        $componentNamespaces[] = $this->getNamespace(
                            $modulePlural, $serviceDirName, $serviceClassName, $isVendor);
                        $componentClassName[]  = $serviceClassName;
                    }
                }

                // 每个组件中的类排序，方便删除
                sort($componentNamespaces);
                sort($componentClassName);

                $namespaces = array_merge($namespaces, $componentNamespaces);
                $className  = array_merge($className, $componentClassName);
            }
        }

        $namespaces = array_unique($namespaces);
        $className  = array_unique($className);

        if (!$this->component) {
            $file = $this->getTraitPath();
            Helper::unlink($file);
        }

        // 2. 发布 trait.php 文件
        $this->command->comment("$this->operation $modulePlural: " . ucfirst($this->getModule()) . "Trait");
        $traitFn = "{$this->operation}Trait";
        $this->$traitFn($namespaces, $className);
    }

    public function removeTrait($namespaces, $className)
    {
        [$namespaceStr, $serviceFunctionStr] = $this->getAppendNamespaceStrAndClassNameStr($namespaces, $className);

        $file = $this->getTraitPath();

        $this->removeTraitInfo($file, $namespaceStr, $serviceFunctionStr);
    }

    protected function appendTraitInfo($file, $namespaceStr, $functionStr, $tpl, $traitName = 'ServiceTrait')
    {
        if (is_file($file)) {
            // 先删除在添加， 防止重复添加
            $this->removeTraitInfo($file, $namespaceStr, $functionStr);

            $content    = file_get_contents($file);
            $traitIndex = strpos($content, 'trait ' . $traitName);
            $lastIndex  = strrpos($content, '}');
            $header     = substr($content, 0, $traitIndex - 1);
            $body       = substr($content, $traitIndex, $lastIndex - $traitIndex - 1);
            $footer     = substr($content, $lastIndex);

            $header  = rtrim($header) . PHP_EOL . $namespaceStr . PHP_EOL . PHP_EOL;
            $body    = rtrim($body) . PHP_EOL . PHP_EOL . $functionStr . PHP_EOL;
            $content = $header . $body . $footer;
            file_put_contents($file, $content);
        } else {
            $tpl = str_replace(
                ['@namespace', '@function'],
                [$namespaceStr, $functionStr],
                $tpl
            );
            file_put_contents($file, $tpl);
        }
    }

    protected function removeTraitInfo($file, $namespaceStr, $functionStr)
    {
        if (!is_file($file)) {
            return;
        }

        $content = file_get_contents($file);
        $content = str_replace([
            $namespaceStr,
            $functionStr
        ], ['', ''], $content);

        file_put_contents($file, $content);
    }

    protected function getTraitPath()
    {
        $module = ucfirst($this->getModule());
        $dir    = app_path('Traits');
        Helper::mkdir($dir);
        return "$dir/{$module}Trait.php";
    }

    abstract protected function getNamespace(string $module, string $dir, string $class, bool $isVendor): string;

    abstract protected function publishTrait(array $namespaces, array $classNames);

    abstract protected function getAppendNamespaceStrAndClassNameStr(array $namespaces, array $classNames): array;
}
