<?php

namespace VhallDisk;

use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class UploadServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('vhall', function ($app, $config) {
            $filesystem = new Filesystem($storageAdapter = new StorageAdapter($config), $config);
            $filesystem->addPlugin(new PluginContest( $storageAdapter ));

            return $filesystem;
        });
    }
}
