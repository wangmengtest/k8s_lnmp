<?php

namespace App\Console\Commands\Component;

use App\Console\Support\Helper;
use Illuminate\Console\Command;
use Matrix\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * 将当前项目推送到指定分支
 * Class DeployCommand
 * @package App\Console\Commands\Component
 */
class DeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'component:deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将项目代码推送到某个分支上,达到自动部署代码的目的';

    // 当前开发分支
    protected $defaultBranch;

    // 项目根目录
    protected $rootDir;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->defaultBranch = $this->getCurrBranchName();
        $this->rootDir       = dirname(dirname(base_path()));
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // 检查当前是否有未提交代码
        $ok = $this->checkExistStagingCode();
        if ($ok) {
            $this->error('当前项目有代码未提交，请先提交代码');
            return 0;
        }

        // 用户确认
        $ok = $this->confirm('是否要将代码推送的测试分支上(确保以分配了该分支)', true);
        if (!$ok) {
            return 0;
        }

        try {
            // 切换分支
            $this->checkoutBranch();

            // 合并分支
            $this->mergeBranch();

            $this->exec('git push');

            // 切换到开发分支
            $this->exec("git checkout $this->defaultBranch");

            $this->info('代码部署成功');
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }

        return 0;
    }

    protected function mergeBranch()
    {
        // 检查是否合并过该分支
        $logs = shell_exec('git log --oneline --decorate -n 10');

        // 第一次合并，需要先删除该分支的代码，再合并
        if (strpos($logs, $this->defaultBranch) === false) {
            $this->exec('rm -fr ' . $this->rootDir . '/*');
            if (is_file($this->rootDir . '/.gitignore')) {
                $this->exec('rm -f ' . $this->rootDir . '/.gitignore');
            }

            $currDir = __DIR__;
            chdir($this->rootDir);
            $this->exec('git add .');
            $this->exec('git commit -m "clean code"');
            chdir($currDir);
        }

        // 合并开发分支代码到当前分支
        $this->exec("git merge $this->defaultBranch --allow-unrelated-histories --rerere-autoupdate");
    }

    /**
     * 切换分支
     * @since  2021/7/15
     * @author fym
     */
    protected function checkoutBranch()
    {
        // 获取分支名
        $branch = $this->getTestEvnBranchName();
        if (!$branch) {
            throw new Exception('分支名不能为空，请重新操作');
        }

        // 检查远程分支是否存在
        $isExist = $this->checkBranchIsExist($branch);
        if (!$isExist) {
            throw new Exception('当前分支不存在, 请确定分支名是否有误');
        }

        // 切换分支
        $this->exec('git checkout ' . $branch);

        // 检查分支是否切换成功
        if ($this->getCurrBranchName() != $branch) {
            throw new Exception('分支切换失败，请检查');
        }

        // 拉取当前分支代码
        $this->exec('git pull origin ' . $branch);
    }

    /**
     * 检查指定分支是否存在
     *
     * @param string $branch
     *
     * @return bool
     * @since  2021/7/9
     *
     * @author yaming.feng@vhall.com
     */
    protected function checkBranchIsExist(string $branch): bool
    {
        $this->info('拉取分支代码');
        $this->exec("git fetch origin $branch:$branch");
        $this->exec("git branch --set-upstream-to=origin/$branch $branch");
        $res = shell_exec('git branch --list ' . $branch);
        return strpos($res, $branch) !== false;
    }

    /**
     * 检查是否存在未提交的代码
     * @return bool
     * @since  2021/7/9
     * @author yaming.feng@vhall.com
     */
    protected function checkExistStagingCode(): bool
    {
        $finish = 'nothing to commit, working tree clean';
        $res    = shell_exec('git status');
        return strpos($res, $finish) === false;
    }

    /**
     * 获得当前分支
     * @return string
     * @since  2021/7/9
     * @author yaming.feng@vhall.com
     */
    protected function getCurrBranchName(): string
    {
        return trim(shell_exec('git symbolic-ref -q --short HEAD'));
    }

    /**
     * 获得测试环境分支名称
     * @return string
     * @since  2021/7/12
     * @author yaming.feng@vhall.com
     */
    protected function getTestEvnBranchName(): string
    {
        $path          = storage_path('framework/cache/deploy.branch');
        $defaultBranch = is_file($path) ? trim(file_get_contents($path)) : '';

        // 获取分支名
        $branch = $this->ask('请输入要推送的分支名:', $defaultBranch);
        $branch = trim($branch);

        if ($branch != $defaultBranch) {
            Helper::mkdir(dirname($path));
            file_put_contents($path, $branch);
        }

        return $branch;
    }

    protected function exec($cmd)
    {
        $this->comment($cmd);
        return shell_exec($cmd);
    }
}
