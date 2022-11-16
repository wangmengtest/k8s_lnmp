<?php

namespace VhallDisk;

use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Config;

class StorageAdapter extends AbstractAdapter
{

    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Write a new file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function write($path, $contents, Config $config)
    {
        return $this->uploadManager()->upload($path, $contents, ['appendFile' => $config->get('appendFile')]);
    }

    /**
     * Write a new file using a stream.
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function writeStream($path, $resource, Config $config)
    {
        return $this->uploadManager()->upload($path, $resource);
    }

    /**
     * Update a file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function update($path, $contents, Config $config)
    {
        return $this->write($path, $contents, $config);
    }

    /**
     * Update a file using a stream.
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function updateStream($path, $resource, Config $config)
    {
        return $this->writeStream($path, $resource, $config);
    }

    /**
     * Rename a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function rename($path, $newpath)
    {
        $this->writeStream($newpath, $this->readStream($path)['stream'], new Config());
        $this->delete($path);
        return true;
    }

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function copy($path, $newpath)
    {
        $this->writeStream($newpath, $this->readStream($path)['stream'], new Config());
        return true;
    }

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete($path)
    {
        return $this->uploadManager()->delete($path);
    }

    /**
     * Delete a directory.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function deleteDir($dirname)
    {
        return $this->uploadManager()->delete($dirname);
    }

    /**
     * Create a directory.
     *
     * @param string $dirname directory name
     * @param Config $config
     *
     * @return array|false
     */
    public function createDir($dirname, Config $config)
    {
        if ($this->uploadManager()->createDir($dirname)) {
            return ['path' => $dirname, 'type' => 'dir'];
        }
        return false;
    }

    /**
     * Set the visibility for a file.
     *
     * @param string $path
     * @param string $visibility
     *
     * @return array|false file meta data
     */
    public function setVisibility($path, $visibility)
    {
        return true;
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return array|bool|null
     */
    public function has($path)
    {
        return $this->uploadManager()->has($path);
    }

    /**
     * Read a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function read($path)
    {
        $request = $this->uploadManager()->read($path);
        return ['path' => $path, 'contents' => $request['contents']];
    }

    /**
     * Read a file as a stream.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function readStream($path)
    {
        $request = $this->uploadManager()->read($path);
        return ['path' => $path, 'contents' => $request['contents']];
    }

    /**
     * List contents of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     *
     * @return array
     */
    public function listContents($directory = '', $recursive = false)
    {
        return [];
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMetadata($path, $argvs = array())
    {
        return $this->uploadManager()->getMetadata($path, $argvs);
    }

    /**
     * Get the size of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getSize($path, $argvs = array())
    {
        $fileInfo = $this->getMetadata($path, $argvs);
        return $fileInfo != false ? ['size' => $fileInfo['size'], 'fileInfo' => $fileInfo] : ['size' => 0, 'fileInfo' => $fileInfo];
    }

    /**
     * Get the mimetype of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMimetype($path)
    {
        $fileInfo = $this->getMetadata($path);
        return ['path' => $path, 'type' => 'file', 'mimetype' => $fileInfo['type']];
    }

    /**
     * Get the timestamp of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getTimestamp($path)
    {
        return false;
    }

    /** Get the URL for the file at the given path.
     * @param $path
     * @return string
     */
    public function getUrl($path)
    {
        $config = $this->config;

        if (isset($config['url'])) {
            return rtrim($config['url'], '/').'/' . $config['bucket'] . '/' .ltrim($path, '/');
        }

        return rtrim($config['bucket'], '/').'/'.ltrim($path, '/');
    }

    /**
     * Get the visibility of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getVisibility($path)
    {
        // TODO
    }

    public function uploadManager()
    {
        return new UploadManager(new Request($this->config));
    }

}
