<?php

namespace App\Console\Support\Generators\Publish;

use App\Console\Support\Helper;
use App\Console\Support\Generators\Publish\Contracts\GeneratorClass;

/**
 * 组件 Controller 代码生成
 * Class Controller
 * @package App\Console\Support\Generators
 */
class Controller extends GeneratorClass
{
    // 当前模块名称
    const MODULE = 'controller';

    const PUBLISH_TPL = <<<EOF

namespace @namespace;

@useBaseNamespace;

class @controllerName extends @baseController
{
}

EOF;

    // 命名兼容
    const CONTROLLER_NAME_MAP = [
        'Api'     => [
//            'Livegoods' => 'LiveGoods',
        ],
        'Console' => [
        ],
        'V2'      => [
        ],
    ];

    /**
     * 发布组件对应的控制器文件
     * @auther yaming.feng@vhall.com
     * @date 2021/4/13
     *
     * @param $componentDir
     * @param $fileName
     */
    public function publish($componentDir, $classFilePath)
    {
        $componentName        = Helper::camel(basename($componentDir));
        $controllerFileName   = basename($classFilePath);
        $controllerModuleName = ucfirst(basename(dirname($classFilePath)));

        [$controllerName, $controllerFileName] = $this->getControllerName($controllerFileName, $controllerModuleName);
        $baseControllerName = $controllerName . "Controller";

        // 基类命名空间引用
        $useBaseNamespace = $this->getUseBaseNamespace(
            $componentName,
            $controllerModuleName,
            $baseControllerName,
            $classFilePath
        );

        $tpl = str_replace([
            '@namespace',
            '@baseController',
            '@controllerName',
            '@useBaseNamespace',
        ], [
            $this->getNamespace($controllerModuleName),
            $baseControllerName,
            $controllerName,
            $useBaseNamespace
        ], $this->getPublishTpl());

        $publishDir = $this->getPublishDir($controllerModuleName, Helper::plural($this->getModule(), 'ucfirst'));
        Helper::mkdir($publishDir);

        $file = $publishDir . '/' . $controllerFileName;
        file_put_contents($file, $tpl);
    }

    /**
     * 删除组件对应的控制器文件
     * @auther yaming.feng@vhall.com
     * @date 2021/4/14
     *
     * @param $componentDir
     * @param $fileName
     */
    public function remove($componentDir, $fileName)
    {
        $controllerFileName   = basename($fileName);
        $controllerModuleName = ucfirst(basename(dirname($fileName)));

        $publishDir = $this->getPublishDir($controllerModuleName, Helper::plural($this->getModule()));
        $file       = $publishDir . '/' . str_replace(['ControllerTrait', 'Controller'], '', $controllerFileName);

        Helper::unlink($file);
    }

    /**
     * 根据控制器的文件名，获取应用层控制器名称和文件名
     * @auther yaming.feng@vhall.com
     * @date 2021/4/15
     *
     * @param string $controllerFileName
     * @param string $controllerModuleName
     *
     * @return array
     */
    protected function getControllerName(string $controllerFileName, string $controllerModuleName): array
    {
        $pathInfo = pathinfo($controllerFileName);

        $controllerName = str_replace(['ControllerTrait', 'Controller'], '', $pathInfo['filename']);

        if (isset(self::CONTROLLER_NAME_MAP[$controllerModuleName][$controllerName])) {
            $controllerName = self::CONTROLLER_NAME_MAP[$controllerModuleName][$controllerName];
        }

        $controllerFileName = $controllerName . '.' . $pathInfo['extension'];

        return [$controllerName, $controllerFileName];
    }

    /**
     * 获取组件应用层控制器的目录
     * @auther yaming.feng@vhall.com
     * @date 2021/4/14
     *
     * @param mixed ...$paths
     *
     * @return string
     */
    protected function getPublishDir(...$paths): string
    {
        return Helper::getPublishDir('Http/Modules', $paths);
    }

    public function getNamespace($moduleName): string
    {
        return "App\\Http\\Modules\\$moduleName\\Controllers";
    }

    /**
     * 获取基类 use 命名空间路径
     * @return string
     * @since  2021/7/2
     * @author fym
     */
    public function getUseBaseNamespace(
        string $componentName,
        string $moduleName,
        string $baseController,
        string $path
    ): string {
        if (strpos($path, 'vendor') !== false) {
            $moduleName = strtolower($moduleName);
            return "use vhallComponent\\$componentName\controllers\\$moduleName\\$baseController";
        }
        return "use App\Component\\$componentName\src\controllers\\$moduleName\\$baseController";
    }

    protected function getPublishTpl(): string
    {
        return '<?php' . PHP_EOL . self::PUBLISH_TPL;
    }

    protected function getModule(): string
    {
        return self::MODULE;
    }
}
