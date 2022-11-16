<?php

namespace App\Console\Support\Component;

use App\Console\Support\Base;
use App\Console\Support\Helper;

class Publish extends Base
{
    protected $publishDir;

    public function __construct()
    {
        $this->publishDir = app_path('Component');
    }

    public function process(string $componentPath): bool
    {
        Helper::mkdir($this->publishDir);

        $componentName = basename($componentPath);

        // 组件被发布后的目录
        $publishComponentPath = $this->publishDir . DIRECTORY_SEPARATOR . $componentName;

        // 检查组件是否存在
        if (!is_dir($componentPath)) {
            $this->command->error("{$componentPath}: not exist.");
            return false;
        }

        // 检查组价是否已发布
        if (is_dir($publishComponentPath)) {
            $this->command->error("{$publishComponentPath}: already exist.");
            return false;
        }

        // 1. copy 组价目录到框架目录下
        $this->exec("cp -r {$componentPath} {$this->publishDir}");

        // 2. 删除组件的 .git
        $this->exec("rm -fr {$publishComponentPath}/.git");
        $this->exec("rm -f {$publishComponentPath}/.gitignore");

        // 3. 修改命名空间
        $this->updateNamespace($publishComponentPath, $componentName);

        // 4. 修改 modules 下 Controller 的继承类的命名空间
        $this->updateNamespace(app_path("Http/Modules"), $componentName);

        // 5. 修改其他发布过的组件对当前组件引用的命名空间
        $this->updateOtherComponentUseNamespace($this->publishDir, $componentName);

        // 6. 修改 ModelTrait 和 ServiceTrait 里该组件的命名空间
        $this->updateNamespace(app_path("Traits"), $componentName);

        // 7. 增加 常量 和 规则的 类别名
        $this->updateClassAlias($componentPath);

        return true;
    }

    /**
     * 修改命名空间
     * @auther yaming.feng@vhall.com
     * @date 2021/4/27
     *
     * @param string $componentPath
     * @param string $componentName
     */
    protected function updateNamespace(string $componentPath, string $componentName)
    {
        foreach (glob($componentPath . '/*') as $file) {
            if (is_dir($file)) {
                $this->updateNamespace($file, $componentName);
                continue;
            }

            if (strrpos($file, '.php') === false) {
                continue;
            }
            $this->replaceNamespace($file, $componentName);
        }
    }

    /**
     * 修改已发布的其他组件对当前组件引用的命名空间
     * @auther yaming.feng@vhall.com
     * @date 2021/4/27
     *
     * @param $componentDir
     * @param $currComponentName
     */
    protected function updateOtherComponentUseNamespace($componentDir, $currComponentName)
    {
        foreach (glob($componentDir . '/*') as $file) {
            $componentName = basename($file);
            if ($componentName == $currComponentName) {
                continue;
            }

            $this->updateNamespace($file, $currComponentName);
        }
    }

    /**
     * 修改 常量 和 验证规则 的类别名
     * @auther yaming.feng@vhall.com
     * @date 2021/4/27
     *
     * @param string $componentPath
     */
    protected function updateClassAlias(string $componentPath)
    {
        $allowModule = ['constants', 'rule'];

        $componentName = basename($componentPath);

        $classAlias = config('classalias');
        foreach (glob($componentPath . '/src/*') as $path) {
            $module = basename($path);

            if (!in_array($module, $allowModule)) {
                continue;
            }

            foreach (glob($path . '/*.php') as $file) {
                $className      = str_ireplace('.php', '', basename($file));
                $aliasNamespace = "vhallComponent\\$componentName\\$module\\$className";
                $namespace      = "App\\Component\\$componentName\\src\\$module\\$className";

                $classAlias[$module][$namespace] = $aliasNamespace;
            }
        }

        $this->outputClassAlias($classAlias);
    }

    protected function outputClassAlias($classAlias)
    {
        $tpl = '<?php' . PHP_EOL . <<<EOF

/**
 * 保存类别名配置, 组件发布到框架中时自动生成，无需修改
 */

return %s;
EOF;

        // 保存到 classalias 配置文件中
        $configStr  = sprintf($tpl, var_export($classAlias, true));
        $configPath = config_path('classalias.php');
        file_put_contents($configPath, $configStr);

        // 输出 deprecated 文件
        $file = config_path('deprecated.php');
        $body = "";
        foreach ($classAlias as $arr) {
            foreach ($arr as $class => $alias) {
                $body .= "@class_alias('$class', '$alias');" . PHP_EOL;
            }
        }

        $content = file_get_contents($file);
        $docStr  = substr($content, 0, stripos($content, '*/') + 2);
        $content = $docStr . PHP_EOL . PHP_EOL . $body;
        file_put_contents($file, $content);
    }

    protected function replaceNamespace($file, $componentName)
    {
        $content = file_get_contents($file);

        $content = str_ireplace(
            "vhallComponent\\$componentName\\",
            "App\\Component\\$componentName\\src\\",
            $content
        );

        file_put_contents($file, $content);

        $this->verbose && $this->command->info($file);
    }
}
