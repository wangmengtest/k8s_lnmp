<?php

namespace App\Console\Support;

use Illuminate\Console\Command;

abstract class Base
{
    /**
     * @var Command $command
     */
    protected $command;

    protected $component;

    protected $verbose;

    public function init(Command $command, string $component, bool $verbose = false): Base
    {
        $this->command   = $command;
        $this->component = $component;
        $this->verbose   = $verbose;

        return $this;
    }

    public function handle()
    {
        $componentDir = Helper::getComponentDir();

        foreach (glob($componentDir . '/*') as $component) {
            if (!is_dir($component)) {
                continue;
            }

            if (!Helper::matchComponent($this->component, $component)) {
                continue;
            }

            $ok = $this->process($component);
            $ok && $this->command->info("{$component}: success");
        }
    }

    public function exec($cmd)
    {
        $this->verbose && $this->command->comment($cmd);
        $res = shell_exec($cmd);
        $this->verbose && $this->command->info($res);
        return $res;
    }

    abstract public function process(string $component): bool;
}
