<?php

namespace App\Console\Commands\Generators;

use App\Console\Support\Generators\Publish\Generator;
use Illuminate\Console\Command;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generator:publish
        {component=all : 输入一个组件名称}
        {--m=all : 输入模块名，如: controller, model, service, rule, constant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '组件应用层代码生成';

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
     * @param Generator $generator
     *
     * @return int
     */
    public function handle(Generator $generator): int
    {
        $component = $this->argument('component');
        $module    = $this->option('m');
        $generator->init($this, $component, $module)->publish();
        return 0;
    }
}
