<?php

namespace App\Console\Support\Generators\Publish;

use App\Console\Support\Helper;
use App\Console\Support\Generators\Publish\Contracts\GeneratorTrait;

class Model extends GeneratorTrait
{
    const MODULE = 'model';

    const PUBLISH_TRAIT_TPL = <<<EOF

namespace App\\Traits;

@namespace

trait ModelTrait
{

@function
}

EOF;

    const TRAIT_FUNCTION_TPL = <<<EOF
    /**
     * @return @className
     */
    public function get@funcName(): @className
    {
        return new @className();
    }

EOF;

    // 方法名兼容
    const FUNC_NAME_MAP = [];

    public function publish($componentDir, $fileName): array
    {
        $serviceName      = Helper::camel(basename($componentDir));
        $serviceDirName   = ucfirst($serviceName);
        $serviceFileName  = basename($fileName);
        $serviceClassName = substr($serviceFileName, 0, -4);

        return [$serviceDirName, $serviceClassName];
    }

    public function remove($componentDir, $fileName): array
    {
        $serviceFileName = basename($fileName);
        $file            = $this->getPublishDir($serviceFileName);
        Helper::unlink($file);

        $serviceName      = Helper::camel(basename($componentDir));
        $serviceDirName   = ucfirst($serviceName);
        $serviceClassName = substr($serviceFileName, 0, -4);
        return [$serviceDirName, $serviceClassName];
    }

    protected function getPublishDir(...$paths): string
    {
        return Helper::getPublishDir('Models', $paths);
    }

    protected function getModule(): string
    {
        return self::MODULE;
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

    protected function publishTrait(array $namespaces, array $classNames)
    {
        [$namespaceStr, $functionStr] = $this->getAppendNamespaceStrAndClassNameStr($namespaces, $classNames);

        $file = $this->getTraitPath();

        $this->appendTraitInfo($file, $namespaceStr, $functionStr, $this->getPublishTraitTpl(), 'ModelTrait');
    }

    protected function getAppendNamespaceStrAndClassNameStr(array $namespaces, array $classNames): array
    {
        $serviceFunctions = [];

        foreach ($classNames as $className) {
            $funcName           = self::FUNC_NAME_MAP[$className] ?? $className;
            $serviceFunctions[] = str_replace(['@className', '@funcName'], [
                $className,
                $funcName
            ], self::TRAIT_FUNCTION_TPL);
        }

        $namespaceStr       = rtrim(implode(PHP_EOL, $namespaces));
        $serviceFunctionStr = rtrim(implode(PHP_EOL, $serviceFunctions));

        return [$namespaceStr, $serviceFunctionStr];
    }

    protected function getPublishTraitTpl(): string
    {
        return '<?php' . PHP_EOL . self::PUBLISH_TRAIT_TPL;
    }
}
