<?php

namespace App\Console\Commands\Component;

use App\Console\Support\Helper;
use Illuminate\Console\Command;

/**
 * 代码格式化
 * Class CodeFormatCommand
 * @package App\Console\Commands\Tools
 */
class CodeFormatCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'component:code-fmt
        {component=all : 请输入一个组件的名称}
        {--p : 是否提交到 GIT 仓库}
        {--v : 是否显示提示信息}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '组件代码格式化';

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
     * @return int
     */
    public function handle(): int
    {
        $allowComponent = $this->argument('component');
        $verbose        = $this->option('v');
        $push           = $this->option('p');

        $basePath     = base_path();
        $componentDir = Helper::getComponentDir();
        foreach (glob($componentDir . '/*') as $component) {
            if (!is_dir($component)) {
                continue;
            }

            if (!Helper::matchComponent($allowComponent, $component, false)) {
                continue;
            }

            $componentName = basename($component);

            $this->info("code format: $componentName");

            $cmd = "composer code-fmt $component";

            chdir($basePath);

            $this->exec($cmd, $verbose);

            // 提交到 GIT
            if ($push) {
                $cmdArr = [
                    'git add .',
                    'git commit -m "code format"',
                    'git push'
                ];

                chdir($component);
                foreach ($cmdArr as $cmd) {
                    $this->exec($cmd, $verbose);
                }
            }
        }

        return 0;
    }

    protected function exec($cmd, $verbose = false)
    {
        $res = shell_exec($cmd);
        $verbose && $this->comment($res);
    }
}
