<?php

namespace VhallDisk;

use Illuminate\Support\Facades\Log;
use League\Flysystem\Plugin\AbstractPlugin;

class PluginContest extends AbstractPlugin
{
    protected  $storageAdapter;
    public function __construct(StorageAdapter $storageAdapter) {
        $this->storageAdapter = $storageAdapter;
    }

    public function getMethod() {
        return 'pluginContest';
    }

    public function handle($argvs = array())
    {
        if('uploadedFilesize' === $argvs['method']){
            return $this->storageAdapter->uploadManager()->uploadedFilesize($argvs);
        }else{
            return $this->storageAdapter->uploadManager()->info($argvs);
        }
    }
}
