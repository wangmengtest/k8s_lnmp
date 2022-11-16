<?php

namespace App\Console\Support\Generators\Publish;

use App\Console\Support\Helper;
use App\Console\Support\Generators\Publish\Contracts\GeneratorTrait;

class Service extends GeneratorTrait
{
    // 当前模块名称
    const MODULE = 'service';

    const PUBLISH_TRAIT_TPL = <<<EOF

namespace App\\Traits;

@namespace
use Illuminate\Contracts\Container\BindingResolutionException;

trait ServiceTrait
{

@function
}

EOF;

    const TRAIT_FUNCTION_TPL = <<<EOF
    /**
     * @return @className
     *
     * @throws BindingResolutionException
     */
    public function get@funcName(): @className
    {
        return app()->make(@className::class);
    }

EOF;

    // 方法名兼容
    const FUNC_NAME_MAP = [
        'InvitecardService'    => 'InviteCardService',
        'PublicforwardService' => 'PublicForwardService',
        'AccountService'       => 'AccountsService',
        'BIgDataService'       => 'BigDataService',
    ];

    /**
     * 发布组件对应的 service 文件
     * @auther yaming.feng@vhall.com
     * @date 2021/4/14
     *
     * @param $componentDir
     * @param $fileName
     *
     * @return array|false[]
     */
    public function publish($componentDir, $fileName): array
    {
        $serviceName      = Helper::camel(basename($componentDir));
        $serviceDirName   = ucfirst($serviceName);
        $serviceFileName  = basename($fileName);
        $serviceClassName = substr($serviceFileName, 0, -4);

        if (!Helper::hasSuffix($serviceClassName, ['Service', 'Services'], true)) {
            return [false, false];
        }

        return [$serviceDirName, $serviceClassName];
    }

    /**
     * 删除组件对应的 service 文件
     * @auther yaming.feng@vhall.com
     * @date 2021/4/14
     *
     * @param string $componentDir
     * @param string $fileName
     *
     * @return array
     */
    public function remove(string $componentDir, string $fileName): array
    {
        $serviceName    = Helper::camel(basename($componentDir));
        $serviceDirName = ucfirst($serviceName);

        $serviceFileName  = basename($fileName);
        $serviceClassName = substr($serviceFileName, 0, -4);
        return [$serviceDirName, $serviceClassName];
    }

    protected function getNamespace(string $module, string $dir, string $class, bool $isVendor): string
    {
        $module = strtolower($module);
        $dir    = lcfirst($dir);

        if ($isVendor) {
            return "use vhallComponent\\$dir\\$module\\$class;";
        }
        return "use App\Component\\$dir\src\\$module\\$class;";
    }

    /**
     * @param array $namespaces
     * @param array $classNames
     *
     * @return array
     * @author  jin.yang@vhall.com
     * @date    2021-05-20
     */
    protected function getAppendNamespaceStrAndClassNameStr(array $namespaces, array $classNames): array
    {
        $serviceFunctions = [];

        foreach ($classNames as $className) {
            $funcName = Helper::removeSuffix($className, 's');

            if (isset(self::FUNC_NAME_MAP[$funcName])) {
                $funcName = self::FUNC_NAME_MAP[$funcName];
            }

            $serviceFunctions[] = str_replace(
                ['@funcName', '@className'],
                [$funcName, $className],
                self::TRAIT_FUNCTION_TPL
            );
        }

        $namespaceStr       = rtrim(implode(PHP_EOL, $namespaces));
        $serviceFunctionStr = rtrim(implode(PHP_EOL, $serviceFunctions)) . PHP_EOL;

        return [$namespaceStr, $serviceFunctionStr];
    }

    /**
     * 发布 Trait
     * @auther yaming.feng@vhall.com
     * @date 2021/4/14
     *
     * @param array $namespaces
     * @param array $classNames
     */
    protected function publishTrait(array $namespaces, array $classNames)
    {
        [$namespaceStr, $serviceFunctionStr] = $this->getAppendNamespaceStrAndClassNameStr($namespaces, $classNames);

        $file = $this->getTraitPath();

        $this->appendTraitInfo($file, $namespaceStr, $serviceFunctionStr, $this->getPublishTraitTpl());
    }

    protected function getPublishDir(...$paths): string
    {
        return Helper::getPublishDir('Services', $paths);
    }

    protected function getModule(): string
    {
        return self::MODULE;
    }

    protected function getPublishTraitTpl(): string
    {
        return '<?php' . PHP_EOL . self::PUBLISH_TRAIT_TPL;
    }
}
