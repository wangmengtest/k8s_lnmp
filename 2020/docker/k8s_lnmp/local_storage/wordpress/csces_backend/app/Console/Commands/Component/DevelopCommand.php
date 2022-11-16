<?php

namespace App\Console\Commands\Component;

use App\Console\Support\Helper;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * 开发组件时，使用该命令，初始化组件环境，自动生成相关目录，并关联 git 仓库
 * Class DevelopCommand
 * @package App\Console\Commands\Component
 */
class DevelopCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'component:develop
        {component : 输入一个组件名称}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '组件开发环境初始化';

    // 要生成的组件目录
    protected $componentDirs = [
        'constants',            // 常量目录
        'controllers/admin',    // 对应后台
        'controllers/api',      // 对应app
        'controllers/console',  // 对应控制台
        'controllers/v2',       // 对应直播间
        'crontabs',             // 对应定时任务
        'jobs',                 // 对应队列任务
        'models',               // 模型
        'services',             // 服务
    ];

    /**
     * 各个目录对应的基类
     * @var array
     */
    protected $baseClass = [
        'controllers' => [
            'use vhallComponent\decouple\controllers\BaseController;',
            'BaseController'
        ],
        'crontabs'    => [
            'use vhallComponent\decouple\crontabs\BaseCrontab;',
            'BaseCrontab'
        ],
        'jobs'        => [
            'use Vss\Queue\JobStrategy;',
            'JobStrategy'
        ],
        'models'      => [
            'use vhallComponent\decouple\models\WebBaseModel;',
            'WebBaseModel'
        ],
        'services'    => [
            'use Vss\Common\Services\WebBaseService;',
            'WebBaseService'
        ]
    ];

    /**
     * 组件名称
     * @var string
     */
    protected $componentName;

    /**
     * 根目录
     * @var string
     */
    protected $rootDir;

    /**
     * 类名
     * @var string
     */
    protected $componentClassName;

    const DEFAULT_MSG = '(使用默认值直接回车即可)';

    protected $tpl = '<?php' . PHP_EOL . <<<EOF

namespace @namespace;

@useNamespace

class @className@extends
{
@func
}

EOF;

    protected $funcTpl = <<<EOF

    public function handle()
    {
        // TODO: Implement handle() method.
    }

EOF;

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
     * 组件开发 GIT 仓库
     * @var string
     */
    protected $defaultGitRepository;

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @author fym
     * @since  2021/7/23
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->defaultGitRepository = $this->getGitRepositoryUrl('vhall_video_studio_projects');
        $this->rootDir              = dirname(dirname(base_path()));
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->componentName      = Str::snake($this->argument('component'), '-');
        $this->componentClassName = Str::studly($this->componentName);

        // 0. 检查组件名称是否已存在
        if ($path = Helper::componentIsExist($this->componentName)) {
            $ok = $this->confirm('组件已存在，是否覆盖: ' . str_replace(base_path(), '', $path));
            if (!$ok) {
                return 0;
            }
            Helper::unlink($path);
        }

        try {
            // 0. 新增忽略文件
            $this->initGitIgnore();

            // 1. 生成组件开发文件
            $this->createFiles();

            // 2. 注册自动加载命名空间
            $this->autoloadNamespace();

            // 3. 关联对应 git 仓库
            $this->gitInit();

            $this->info('组件环境初始化完成');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        return 0;
    }

    /**
     * 初始化 GIT 忽略文件
     * @author fym
     * @since  2021/7/15
     */
    protected function initGitIgnore()
    {
        $file    = $this->rootDir . '/.gitignore';
        $content = is_file($file) ? file_get_contents($file) : '';

        // 忽略 .idea
        if (strpos($content, '.idea') === false) {
            $content .= '.idea' . PHP_EOL;
        }

        // 忽略部署分支缓存文件
        $cacheDir = ltrim(str_replace(
            $this->rootDir,
            '',
            storage_path('framework/cache/*')), '/');

        if (strpos($content, $cacheDir) === false) {
            $content .= $cacheDir . PHP_EOL;
        }

        // 忽略日志文件
        $logDir = ltrim(str_replace(
            $this->rootDir,
            '',
            storage_path('logs/*')), '/');
        if (strpos($content, $logDir)) {
            $content .= $logDir . PHP_EOL;
        }

        file_put_contents($file, $content);
    }

    /**
     * 创建组件文件
     * @author fym
     * @since  2021/6/29
     */
    protected function createFiles()
    {
        // 创建组件目录
        $componentDir = base_path('vendor/vhall-component/' . $this->componentName . '/src');
        foreach ($this->componentDirs as $dir) {
            $module    = explode('/', $dir)[0];
            $className = $this->componentClassName . $this->getFileSuffix($dir);
            $filePath  = $componentDir . '/' . $dir . '/' . $className . '.php';

            $base         = $this->baseClass[$module] ?? [];
            $namespace    = $this->getNamespace($filePath);
            $useNamespace = $base[0] ?? '';
            $extends      = $base[1] ? ' extends ' . $base[1] : '';
            $func         = in_array($base[1], ['JobStrategy', 'BaseCrontab']) ? $this->funcTpl : '';
            $content      = str_replace([
                '@namespace',
                '@useNamespace',
                '@className',
                '@extends',
                '@func'
            ], [
                $namespace,
                $useNamespace,
                $className,
                $extends,
                $func
            ], $this->tpl);

            Helper::mkdir(dirname($filePath));

            file_put_contents($filePath, $content);

            $filePath = str_replace(base_path(), '', $filePath);
            $this->info("Create file: " . $filePath);
        }

        $this->createSqlFile($componentDir);

        $this->createResponseFile($componentDir);

        $this->composerInit(dirname($componentDir));
    }

    /**
     * 初始化 GIT 仓库
     * @throws Exception
     * @since  2021/6/29
     * @author fym
     */
    protected function gitInit()
    {
        $ok = $this->confirm('是否自动关联 GIT 仓库', true);
        if (!$ok) {
            return;
        }

        chdir(base_path('../../'));

        $gitInit = true;

        // 检查当前是否存在 git 仓库
        if (is_dir('./.git')) {
            $repository = shell_exec('git remote get-url --push origin');
            if ($repository != $this->defaultGitRepository) {
                $ok = $this->confirm('已存在 GIT 仓库(' . $repository . '), 是否覆盖', true);
                if ($ok) {
                    Helper::unlink(base_path('../../.git'));
                } else {
                    $gitInit = false;
                }
            }
        }

        // 前端代码
        $front = base_path('../../projectApp/vhall_front_frame');
        if (is_dir($front)) {
            Helper::unlink($front);
        }

        // 默认分支
        $defaultBranch = 'feature-' . Helper::camel($this->componentName) . '-1.0.0';

        if ($gitInit) {
            $repository = $this->ask('请确定 GIT 仓库地址' . self::DEFAULT_MSG, $this->defaultGitRepository);
            $this->info('初始化 GIT 仓库');
            shell_exec('git init');

            $this->info('绑定远程仓库:' . $repository);
            shell_exec('git remote add origin ' . $repository);
        }

        $branch = $this->ask('请确定 GIT 分支' . self::DEFAULT_MSG, $defaultBranch);

        $this->info('切换分支');
        shell_exec('git checkout -B ' . $branch);
        shell_exec('git add .');
        shell_exec("git commit -m 'init component $this->componentName'");

        if (!$this->confirm('是否推送初始化代码到远程仓库', true)) {
            $this->info('代码仓库已绑定成功,请手动推送代码到远程仓库');
            return;
        }

        // 自动推送初始化代码
        shell_exec('git push -u origin ' . $branch);
    }

    /**
     * 创建 sql 文件
     *
     * @param string $componentDir
     *
     * @author yaming.feng@vhall.com
     * @since  2021/7/9
     */
    protected function createSqlFile(string $componentDir)
    {
        // 创建 sql 文件
        $path    = dirname($componentDir) . '/' . Str::snake($this->componentClassName, '-') . '.sql';
        $content = <<<EOF
-- ----------------------------
-- 存放该组件相关的 sql
-- ----------------------------
EOF;
        file_put_contents($path, $content);
        $this->info("Create file: " . str_replace(base_path(), '', $path));
    }

    /**
     * 创建响应码常量文件
     *
     * @param string $componentDir
     *
     * @author yaming.feng@vhall.com
     * @since  2021/7/12
     */
    protected function createResponseFile(string $componentDir)
    {
        $path       = $componentDir . '/constants/ResponseCode.php';
        $content    = '<?php' . <<<EOF


namespace vhallComponent\@name\constants;

/**
 * 响应码常量类
 * 尽量使用父类以后的的Code常量，
 * 当找不到合适的时候，再在这里新增组件的常量
 * 确保常量的命名要满足组件常量命名规范, 命名规范可参考父类
 * 该组件命名前缀为: COMP_@codePrefix_
 *
 * 可以通过如下命令检查错误码是否有重复:
 * php artisan generator:lang {componentName} --c
 *
 * 可以通过如下命令生成中文语言包
 * php artisan generator:lang {componentName}
 *
 * 可以通过如下命令生成英文语言包，前提是已经生成中文语言包了
 * php artisan generator:lang {componentName} --t
 *
 * Class ResponseCode
 * @package vhallComponent\@name\constants
 */
class ResponseCode extends \App\Constants\ResponseCode
{

}

EOF;
        $codePrefix = strtoupper(Str::snake($this->componentClassName));
        $content    = str_replace(['@name', '@codePrefix'], [
            lcfirst($this->componentClassName),
            $codePrefix
        ], $content);

        file_put_contents($path, $content);
        $this->info("Create file: " . str_replace(base_path(), '', $path));
    }

    /**
     * @author fym
     * @since  2021/6/29
     */
    protected function composerInit(string $componentDir)
    {
        $composerFile = $componentDir . '/composer.json';

        if (is_file($composerFile)) {
            return;
        }

        $this->info(PHP_EOL . '初始化 Composer.json, 请输入相关信息');

        $desc        = $this->ask("请输入组件描述信息");
        $authorName  = $this->ask("请输入作者名称");
        $authorEmail = $this->ask("请输入作者邮箱");

        $content = str_replace([
            '@composerName',
            '@desc',
            '@authorName',
            '@authorEmail',
            '@namespace'
        ], [
            Str::snake($this->componentName, '-'),
            $desc,
            $authorName,
            $authorEmail,
            lcfirst($this->componentClassName)
        ], $this->composerTpl);

        file_put_contents($composerFile, $content);
    }

    /**
     * 注册自动加载命名空间
     * @author yaming.feng@vhall.com
     * @since  2021/7/9
     */
    protected function autoloadNamespace()
    {
        $namespace    = '"vhallComponent\\\\' . lcfirst($this->componentClassName);
        $composerJson = file_get_contents(base_path('composer.json'));
        if (strpos($composerJson, $namespace) === false) {
            $autoloadPsr4 = $namespace . '\\\\": "vendor/vhall-component/' . $this->componentName . '/src",';
            $composerJson = str_replace(
                '"vss/",',
                '"vss/",' . PHP_EOL . '            ' . $autoloadPsr4,
                $composerJson
            );

            file_put_contents(base_path('composer.json'), $composerJson);
        }

        // composer autoload
        $this->info('执行 composer dump-autoload 注册命名空间');
        chdir(base_path());
        shell_exec('composer dump-autoload');
    }

    /**
     * 获取生成文件后缀
     *
     * @param string $path
     *
     * @return string
     * @author fym
     * @since  2021/6/29
     */
    private function getFileSuffix(string $path): string
    {
        $dir = explode('/', $path)[0];
        $dir = rtrim($dir, 's');
        return ucfirst($dir);
    }

    /**
     * 获取仓库 url, ssh 或 http
     *
     * @param $name
     *
     * @return string
     * @since  2021/7/9
     *
     * @author yaming.feng@vhall.com
     */
    protected function getGitRepositoryUrl($name)
    {
        $repository = "vhall_pass_component/$name.git";
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
     * 获取命名空间
     *
     * @param string $path
     *
     * @return string
     * @author fym
     * @since  2021/6/29
     */
    private function getNamespace(string $path): string
    {
        $path      = 'vhallComponent\\' . lcfirst($this->componentClassName) . explode('src', $path)[1];
        $namespace = substr($path, 0, strrpos($path, '/'));
        return str_replace('/', '\\', $namespace);
    }
}
