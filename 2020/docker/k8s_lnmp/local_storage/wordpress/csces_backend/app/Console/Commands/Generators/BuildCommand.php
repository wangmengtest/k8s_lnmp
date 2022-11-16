<?php

namespace App\Console\Commands\Generators;

use App\Console\Support\Generators\Builder\Builder;
use Illuminate\Console\Command;

class BuildCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generator:build
        {component=all : 输入一个组件名称}
        {--o=insert : 输入操作，eg: insert or remove}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '读取 config.js 向被依赖的组件插入代码';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param Builder $builder
     *
     * @return int
     */
    public function handle(Builder $builder): int
    {
        $component = $this->argument('component');
        $operation = $this->option('o');
        $builder->init($this, $component, $operation)->handle();
        return 0;
    }
}
