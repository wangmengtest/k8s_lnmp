<?php

namespace App\Console\Commands\Qa;

use App\Console\Support\Helper;
use Illuminate\Console\Command;

/**
 * QA 质量保证
 * 使用 phpcbf  修复不符合 PSR2 规范的代码
 * Class PhpcbfCommand
 * @package App\Console\Commands\Qa
 */
class PhpcbfCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qa:phpcbf
        {path :  组件名或目录路径或文件路径}
        {--push : 是否提交到 GIT 仓库, 指对组件组件有效}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $phpcbfBin = PHP_BINARY . ' ' . resource_path('bin/phpcbf.phar');
        $old       = $path = $this->argument('path');

        if (!is_file($path) && !is_dir($path)) {
            $path = Helper::componentIsExist($path);
        };

        if (!is_file($path) && !is_dir($path)) {
            $this->error('文件或目录不存在:' . $old);
            return 1;
        }

        echo shell_exec("$phpcbfBin $path");

        $this->info('已修复所有 [x] 标记的错误, 剩下的错误请手动修复');
        $this->info('修复完请记得提交代码');
        return 0;
    }
}
