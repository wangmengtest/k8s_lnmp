<?php

namespace App\Console\Support\Generators\Publish\Contracts;

use Illuminate\Console\Command;

abstract class GeneratorBase
{
    /**
     * @var Command
     */
    protected $command;
    protected $component;
    protected $operation;
    protected $hasSubModule = false; // 该模块是否有子目录

    // 保存所有发布过的文件， 做文件名重复校验
    protected $filePaths = [];

    public function init(Command $command, string $component, string $operation): GeneratorBase
    {
        $this->command   = $command;
        $this->component = $component;
        $this->operation = $operation;
        return $this;
    }

    abstract public function handle();

    abstract protected function getModule(): string;

    abstract public function publish(string $componentDir, string $fileName);

    abstract public function remove(string $componentDir, string $fileName);

    abstract protected function getPublishDir(...$paths): string;
}
