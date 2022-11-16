<?php

namespace App\Console\Commands\Component;

use App\Console\Support\Helper;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RecyclingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'component:recycling
        {component : 输入一个组件名称}
        {--v : 是否输出详细日志}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '组件回收';

    /**
     * 组件回收到的目录
     * @var string
     */
    protected $targetDir;

    /**
     * 组件名称
     * @var string
     */
    protected $componentName;

    /**
     * 是否输出详细日志
     * @var bool
     */
    protected $verbose;

    /**
     * composer 模板
     * @var string
     */
    protected $composerTpl = <<<EOF
{
    "name": "vhall-component/@composerName",
    "description": "@desc",
    "type": "library",
    "license": "MIT",
    "authors": [
        {
            "name": "@authorName",
            "email": "@authorEmail"
        }
    ],
    "minimum-stability": "dev",
    "autoload" : {
        "psr-4" : {
            "vhallComponent\\\\@namespace\\\\": "src"
        }
    },
    "require": {}
}

EOF;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        try {
            // 1. 检查环境
            $this->checkEnv();

            // 2. 初始化 git 仓库
            $this->gitInit();

            // 3. 提交代码
            $this->commitCode();

            $this->info(PHP_EOL . '代码回收完成');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        return 0;
    }

    /**
     * 检查环境
     * @throws Exception
     * @author fym
     * @since  2021/6/24
     */
    protected function checkEnv()
    {
        $this->componentName = Str::studly($this->argument('component'));
        $this->verbose       = $this->option('v');

        $this->targetDir = Helper::getComponentDir() . '/' . Str::snake($this->componentName, '-');

        if (!is_dir($this->targetDir)) {
            throw new Exception('组件不存在: ' . str_replace(base_path(), '', $this->targetDir));
        }
    }

    /**
     * @author fym
     * @since  2021/6/29
     */
    protected function gitInit()
    {
        if (is_dir($this->targetDir . '/.git')) {
            return;
        }

        $this->info('初始化 GIT 仓库: git init');

        chdir($this->targetDir);
        $this->exec('git init');

        $name       = Str::snake($this->componentName, '-');
        $repository = $this->getGitRepositoryUrl($name);

        $repository = $this->ask('请确定远程仓库地址(直接回车或输入新的仓库路径): ', $repository);

        $this->info('绑定远程仓库: ' . $repository);
        $this->exec("git remote add origin $repository");

        // 默认分支
        $defaultBranch = 'feature-' . Str::camel($this->componentName) . '-1.0.0';

        $this->info('切换开发分支:' . $defaultBranch);
        $this->exec('git checkout -b ' . $defaultBranch);
    }

    /**
     * 提交代码
     * @throws Exception
     * @since  2021/7/7
     * @author yaming.feng@vhall.com
     */
    protected function commitCode()
    {
        $path = explode('vendor', $this->targetDir);
        if (!$this->confirm('是否提交代码', true)) {
            throw new Exception('请到 vendor' . $path[1] . ' 目录下, 手动提交代码到 GIT 仓库');
        }

        chdir($this->targetDir);

        // 获取当前分支
        $branch = trim($this->exec('git symbolic-ref -q --short HEAD'));

        // 提交代码
        $this->exec('git add .');

        $msg = $this->ask('请输入 git commit 信息:', 'init');
        $this->exec("git commit -m '$msg'");

        if (!$this->confirm('是否推送代码到远程仓库', true)) {
            throw new Exception('请到 vendor' . $path[1] . ' 目录下, 手动提交代码到 GIT 仓库');
        }

        // 推送到远程仓库
        $this->info('推送到仓库');
        $config = file_get_contents('.git/config');
        if (strpos($config, $branch) === false) {
            // 第一次推送需要绑定
            $this->exec('git push --set-upstream origin ' . $branch);
            return;
        }
        $this->exec('git push');
    }

    /**
     * 获取 Git 仓库地址，优先使用 ssh, 如果 ssh 不能用，则用 http
     *
     * @param $name
     *
     * @return string
     * @since  2021/7/9
     *
     * @author yaming.feng@vhall.com
     */
    protected function getGitRepositoryUrl($name): string
    {
        $repository = "vhall_pass_vss/webservice/$name.git";
        $ssh        = 'git@chandao.ops.vhall.com';
        $http       = "http://47.94.241.60:8082/";

        // 检查 ssh 是否可用
        $cmd = "ssh -o ConnectTimeout=3 -T $ssh";
        $res = shell_exec($cmd);
        if (strpos($res, 'timed out') !== false) {
            return $http . $repository;
        }

        return $ssh . ':' . $repository;
    }

    /**
     * 执行 shell
     *
     * @param $cmd
     *
     * @return false|string|null
     * @since  2021/7/7
     *
     * @author yaming.feng@vhall.com
     */
    protected function exec($cmd)
    {
        $this->verbose && $this->comment(" > " . $cmd);
        $res = shell_exec($cmd);
        $this->verbose && $this->info($res);
        return $res;
    }
}
