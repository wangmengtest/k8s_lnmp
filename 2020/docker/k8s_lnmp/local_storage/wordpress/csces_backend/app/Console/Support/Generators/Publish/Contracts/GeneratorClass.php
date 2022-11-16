<?php

namespace App\Console\Support\Generators\Publish\Contracts;

use App\Console\Support\Helper;

abstract class GeneratorClass extends GeneratorBase
{
    // 是否存在子目录
    protected $hasSubModule = false;

    public function handle()
    {
        $modulePlural     = Helper::plural($this->getModule());
        $componentDirList = Helper::getComponentDirList();

        foreach ($componentDirList as $componentDir) {
            foreach (glob($componentDir . '/*') as $component) {
                if (!is_dir($component)) {
                    continue;
                }

                if (!Helper::matchComponent($this->component, $component)) {
                    continue;
                }

                $componentModuleDir = "$component/src/$modulePlural";
                $this->command->comment("$this->operation $modulePlural: " . basename($component));

                $this->process($componentModuleDir, $component);
            }
        }

        $this->command->info("$this->operation success");
    }

    public function process($dir, $component)
    {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $this->process($file, $component);
                continue;
            }

            $this->{$this->operation}($component, $file);
        }
    }

    public function publish($componentDir, $classFilePath)
    {
        $fileName   = basename($classFilePath);
        $className  = substr($fileName, 0, -4);
        $moduleName = strtolower(basename($componentDir));
        $moduleName = Helper::getComponentName($moduleName, $classFilePath);

        $tpl        = str_replace(['@className', '@moduleName'], [$className, $moduleName], $this->getPublishTpl());
        $publishDir = $this->getPublishDir();
        Helper::mkdir($publishDir);

        $path = $publishDir . '/' . $fileName;

        if (!Helper::addNotExist($this->filePaths, $path)) {
            $this->command->error("error: file already exist, module name: $moduleName, file path: $path");
            die;
        }

        file_put_contents($path, $tpl);
    }

    public function remove($componentDir, $classFilePath)
    {
        $serviceFileName = basename($classFilePath);
        $file            = $this->getPublishDir($serviceFileName);
        Helper::unlink($file);
    }

    abstract protected function getPublishTpl(): string;

    abstract protected function getPublishDir(...$paths): string;
}
