<?php

namespace App\Console\Commands\Qa;

use App\Console\Support\Helper;
use Illuminate\Console\Command;

/**
 * QA 质量保证
 * 使用 phpcs 检查代码是否符合 PSR2 规范
 * Class PhpcsCommand
 * @package App\Console\Commands\QA
 */
class PhpcsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qa:phpcs
        {path :  组件名或目录路径或文件路径}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检查代码是否规范';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $phpcsBin = PHP_BINARY . ' ' . resource_path('bin/phpcs.phar');
        $old      = $path = $this->argument('path');

        if (!is_file($path) && !is_dir($path)) {
            $path = Helper::componentIsExist($path);
        }

        if (!is_file($path) && !is_dir($path)) {
            $this->error('文件或目录不存在:' . $old);
            return 1;
        }

        $res = shell_exec("$phpcsBin $path");
        echo $res;

        if ($res) {
            if (strpos($res, '[x]') !== false) {
                $this->info("使用命令修复 [x] 标志的错误: php artisan qa:phpcbf $old");
            }

            $this->info('请根据提示修复错误后，再检查; 直至修复所有错误，再提交代码'); // asdfkaslf as 桑德拉接口发送阿卡士大夫啦啊速度快放假案例三大法师的
            return 1;
        }

        $this->info('代码符合 PSR2 规范，very good');
        return 0;
    }
}
