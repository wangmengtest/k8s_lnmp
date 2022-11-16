<?php

namespace App\Console\Commands\Component;

use App\Console\Support\Component\Remove;
use Illuminate\Console\Command;

class RemoveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'component:remove
        {component : 输入一个组件名称}
        {--v : 是否输出详细信息}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '删除发布到框架中的组件代码';

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
     * @param Remove $remove
     *
     * @return int
     */
    public function handle(Remove $remove): int
    {
        $component = $this->argument('component');
        $verbose   = $this->option('v');
        $remove->init($this, $component, $verbose)->handle();
        return 0;
    }
}
