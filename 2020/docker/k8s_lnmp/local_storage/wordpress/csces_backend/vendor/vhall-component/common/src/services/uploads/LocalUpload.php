<?php

namespace vhallComponent\common\services\uploads;

use vhallComponent\common\services\UploadFile;

class LocalUpload implements UploadServiceInterface
{
    private $prefix;

    private $suffix;

    private $host;

    private $uploadDir;

    public function __construct($prefix = '', $suffix = '', $host = '', $upload_dir = 'uploads')
    {
        $this->prefix    = $prefix;
        $this->suffix    = $suffix;
        $this->host      = $host;
        $this->uploadDir = $upload_dir;

        if ($this->host == 'auto') {
            $this->host = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/';
        }

        if ($this->host && substr($this->host, -1) != '/') {
            $this->host .= '/';
        }
    }

    public function upload($localFilePath, string $uploadFilePath)
    {
        if ($localFilePath instanceof UploadFile) {
            $localFilePath = $localFilePath->getRealPath();
        }

        if (!is_file($localFilePath)) {
            return false;
        }

        $uploadFilePath = $this->uploadDir . DIRECTORY_SEPARATOR . $this->getUploadFilePath($uploadFilePath);
        if (!UploadFile::mkdirs(dirname($uploadFilePath))) {
            return false;
        }
        rename($localFilePath, $uploadFilePath);

        return $this->host . $uploadFilePath;
    }

    public function getUploadFilePath($filePath)
    {
        if (!$this->prefix && !$this->suffix) {
            return $filePath;
        }

        $pathInfo = pathinfo($filePath);
        $filePath = $pathInfo['dirname'] . '/' . $this->prefix . $pathInfo['filename'] . $this->suffix;

        if ($pathInfo['extension']) {
            $filePath .= '.' . $pathInfo['extension'];
        }

        return $filePath;
    }
}
