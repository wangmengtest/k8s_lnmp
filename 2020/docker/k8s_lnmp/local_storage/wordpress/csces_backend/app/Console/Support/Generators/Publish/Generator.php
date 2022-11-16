<?php

namespace App\Console\Support\Generators\Publish;

use Illuminate\Console\Command;

/**
 * 组件应用层代码生成器
 * Class Index
 * @package App\Console\Support\Generators
 */
class Generator
{
    protected $command;

    // 组件名
    protected $component;

    // 模块名称
    protected $module;

    protected $generators = [
        'controller' => Controller::class,
        'service'    => Service::class,
        'model'      => Model::class,
    ];

    public function init(Command $command, $component, $module): Generator
    {
        $component = strtolower($component);
        $module    = strtolower($module);

        $this->command   = $command;
        $this->component = $component == 'all' ? '' : $component;
        $this->module    = $module == 'all' ? '' : $module;

        return $this;
    }

    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/4/13
     */
    public function publish()
    {
        $this->dispatch('publish');
    }

    public function remove()
    {
        $this->dispatch('remove');
    }

    protected function dispatch($operation)
    {
        if ($this->module && !isset($this->generators[$this->module])) {
            $this->command->error("module: $this->module not exists.");
            return;
        }

        foreach ($this->generators as $module => $generator) {
            if ($this->module && $this->module != $module) {
                continue;
            }

            app($generator)->init($this->command, $this->component, $operation)->handle();
        }
    }
}
