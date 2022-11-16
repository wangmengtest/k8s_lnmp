<?php

namespace App\Console\Commands\Component;

use App\Console\Support\Component\Publish;
use Illuminate\Console\Command;

/**
 * Class PublishCommand
 * @package App\Console\Commands\Component
 */
class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'component:publish
        {component : 输入一个组件名称}
        {--v : 是否输出详细信息}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发布组件代码到框架中';

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
     * @param Publish $publish
     *
     * @return int
     */
    public function handle(Publish $publish): int
    {
        $component = $this->argument('component');
        $verbose   = $this->option('v');
        $publish->init($this, $component, $verbose)->handle();
        return 0;
    }
}
