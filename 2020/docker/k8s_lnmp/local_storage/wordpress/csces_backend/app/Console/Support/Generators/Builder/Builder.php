<?php

namespace App\Console\Support\Generators\Builder;

use App\Console\Support\Helper;
use Illuminate\Console\Command;

class Builder
{
    const CONFIG_FILE = '/src/config.php';

    const OPERATIONS = [
        'insert',
        'remove'
    ];

    /**
     * @var Command
     */
    protected $command;
    protected $component;
    protected $operation;

    // 保存所有发布过的文件， 做文件名重复校验
    protected $filePaths = [];

    public function init(Command $command, string $component, string $operation): Builder
    {
        $this->command   = $command;
        $this->component = $component == 'all' ? '' : $component;
        $this->operation = $operation;
        return $this;
    }

    public function handle()
    {
        if (!in_array($this->operation, self::OPERATIONS)) {
            $this->command->error("operation {$this->operation} not exist.");
            return;
        }

        $componentList = Helper::getComponentDirList();

        foreach ($componentList as $componentDir) {
            foreach (glob($componentDir . '/*') as $component) {
                if (!is_dir($component)) {
                    continue;
                }

                if (!Helper::matchComponent($this->component, $component)) {
                    continue;
                }

                $configFilePath = $component . self::CONFIG_FILE;
                if (!is_file($configFilePath)) {
                    continue;
                }

                $componentName = basename($component);
                $this->command->comment(PHP_EOL . "parse {$componentName}: " . $configFilePath);

                $config = $this->getConfigContent($configFilePath);
                if (!$config) {
                    $this->command->error("config parse fail: {$configFilePath}");
                    continue;
                }

                $this->builder($config);
            }
        }
        $this->command->info("builder {$this->operation} success");
    }

    protected function getConfigContent(string $configFilePath): array
    {
        return require_once($configFilePath);
    }

    protected function builder(array $config)
    {
        $componentDir  = Helper::getComponentDir();
        $condeContents = $config['snippets'][0]['codeContents'];

        foreach ($condeContents as $codeContent) {
            foreach ($codeContent['content'] as $content) {
                // 要插入代码的文件路径
                $targetFilePath = $componentDir . $codeContent['parentDirectory'] . $content['target'];

                foreach ($content['block'] as $block) {
                    // 要插入代码的锚点
                    $anchor = $block['name'];

                    // 要插入的代码
                    $code = $block['content'];

                    $this->command->comment("{$this->operation} code: " . $targetFilePath);
                    $this->command->comment("code anchor: $anchor");

                    $funcName = "{$this->operation}Code";
                    if (!method_exists($this, $funcName)) {
                        $this->command->comment("function   : {$funcName} not exist");
                        continue;
                    }

                    $this->{$funcName}($targetFilePath, $anchor, $code);
                }
            }
        }
    }

    protected function insertCode(string $targetFilePath, string $anchor, string $code)
    {
        if (!is_file($targetFilePath)) {
            $this->command->error("{$targetFilePath}: not exist. skip...");
            return;
        }

        if (!is_file($targetFilePath) && strripos($targetFilePath, 'Trait.php') !== false) {
            $targetFilePath = str_ireplace('Trait', '', $targetFilePath);
        }

        $targetContent = file_get_contents($targetFilePath);
        $reg           = "/#\s{0,}vhallEOF-{$anchor}-start\s*?\n([\s\S]*?)\s*?#\s{0,}vhallEOF-{$anchor}-end/i";
        $content       = "# vhallEOF-{$anchor}-start
        {$code}
        # vhallEOF-{$anchor}-end";

        $targetContent = preg_replace($reg, $content, $targetContent);

        file_put_contents($targetFilePath, $targetContent);
    }

    protected function removeCode(string $targetFilePath, string $anchor)
    {
        $targetContent = file_get_contents($targetFilePath);

        $reg     = "/#\s{0,}vhallEOF-{$anchor}-start\s*?\n([\s\S]*?)\s*?#\s{0,}vhallEOF-{$anchor}-end/i";
        $content = "# vhallEOF-{$anchor}-start
        # vhallEOF-{$anchor}-end";

        $targetContent = preg_replace($reg, $content, $targetContent);

        file_put_contents($targetFilePath, $targetContent);
    }
}
