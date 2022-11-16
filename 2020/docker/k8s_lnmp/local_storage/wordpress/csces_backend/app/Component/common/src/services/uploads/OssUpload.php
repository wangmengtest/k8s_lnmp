<?php

namespace App\Component\common\src\services\uploads;

use Exception;

class OssUpload implements UploadServiceInterface
{
    public $appId;

    public $secret;

    public $endPoint;

    public $bucket;

    public $prefix;

    public $host;

    private $uploadModel;

    public function __construct($appId, $secret, $bucket, $endPoint, $prefix = null, $host = '')
    {
        $this->appId    = $appId;
        $this->secret   = $secret;
        $this->bucket   = $bucket;
        $this->endPoint = $endPoint;
        $this->prefix   = $prefix;
        $this->host     = $host;
    }

    /**
     * @param      $file
     * @param      $type
     * @param null $path
     * @param bool $forceName
     * @param bool $useLogin
     *
     * @return string
     * @throws Exception
     */
    public function uploadFile($file, $type, $path = null, $forceName = false, $useLogin = true)
    {
        $file = $file instanceof UploadFile ? $file : new UploadFile('file');

        if ($file && $file->isValid()) {
            $hash = md5_file($file->getRealPath());
            //$file->getClientOriginalName();
            $file_name = $forceName ? $forceName : $hash;
            $ext       = strtolower($file->getClientOriginalExtension());

            // 检测上传文件格式
            $this->checkType($type, $ext);
            $savePath = substr($hash, 0, 2) . DIRECTORY_SEPARATOR . substr($hash, 2, 2);

            // 检测上传路径
            if (!is_null($path) && $path = $this->checkAddress($path)) {
                $savePath = $path . DIRECTORY_SEPARATOR . $savePath;
            }
            $file_src = $savePath . DIRECTORY_SEPARATOR . $file_name . '.' . $ext;
            if ($this->prefix) {
                $file_src = $this->prefix . DIRECTORY_SEPARATOR . $file_src;
            }
            if (!$this->uploadToOSS($file, $file_src)) {
                throw new JsonException('oss error', 500);
            }
            return $file_src;
        }

        throw new Exception('上传文件不存在');
    }

    public function getUploadTmp($file, $type)
    {
        $file = $file instanceof UploadFile ? $file : new UploadFile('file');

        if ($file && $file->isValid()) {
            $ext = strtolower($file->getClientOriginalExtension());
            // 检测上传文件格式
            $this->checkType($type, $ext);
            return $file;
        }

        throw new Exception('上传文件不存在');
    }

    public function checkAddress($address)
    {
        return $address;
        $match = '/^\w+$/';

        // 数组类型
        if (is_array($address)) {
            if (isset($address['table']) && isset($address['field'])) {
                if (!preg_match($match, $address['table'] . $address['field'])) {
                    throw new Exception('上传路径中含有特殊字符');
                }

                return $address['table'] . DIRECTORY_SEPARATOR . $address['field'];
            }
            throw new Exception('上传数据内容不全');
        }

        if (preg_match($match, $address)) {
            return $address;
        }

        throw new Exception('上传路径中含有特殊字符');
    }

    public function checkType($type, $ext)
    {
        $allowType = [
            'image' => [
                'bmp',
                'gif',
                'jpg',
                'psd',
                'png',
                'jpeg'
            ],
            'video' => [
                'rm',
                'rmvb',
                'wmv',
                'avi',
                'mp4',
                '3gp',
                'mkv',
                'flv',
                'mov'
            ],
            'app'   => [
                'apk',
                'ipa'
            ],
            'exe'   => [
                'exe'
            ],
            'doc'   => [
                'txt',
                'doc',
                'docx',
                'xls',
                'xlsx',
                'ppt',
                'pptx',
                'pdf',
                'gif',
                'jpeg',
                'jpg',
                'png',
                'bmp',
                'csv'
            ],
            'exel'  => [
                'xls',
                'xlsx'
            ],
            'audio' => [
                'mp3',
                'wav'
            ],
            'zip'   => [
                'zip',
                'rar'
            ]
        ];

        if (is_array($type)) {
            $tmpAllowType = [];

            foreach ($type as $value) {
                if (isset($allowType[$value])) {
                    $tmpAllowType = array_merge($tmpAllowType, $allowType[$value]);
                } else {
                    throw new Exception('当前上传类型，含有不被允许类型');
                }
            }

            if (!in_array($ext, $tmpAllowType)) {
                throw new Exception('当前上传类型不被允许');
            }
        } else {
            if (!isset($allowType[$type]) || !in_array($ext, $allowType[$type])) {
                throw new Exception('当前上传类型不被允许');
            }
        }
    }

    public function putContent($content, $object)
    {
        try {
            $ossClient = new \OSS\OssClient($this->appId, $this->secret, $this->endPoint);
            $ossClient->putObject($this->bucket, $object, $content, [\OSS\OssClient::OSS_CHECK_MD5 => true]);
            return true;
        } catch (\OSS\Core\OssException $e) {
            return false;
        }
    }

    public function upload($localFilePath, $uploadFilePath)
    {
        $this->uploadToOSS($localFilePath, $uploadFilePath);
        return $this->host . '/' . $uploadFilePath;
    }

    public function uploadToOSS($file, $object)
    {
        try {
            $ossClient = new \OSS\OssClient($this->appId, $this->secret, $this->endPoint);
            $ossClient->uploadFile($this->bucket, $object, $file->getPathname(),
                [\OSS\OssClient::OSS_CHECK_MD5 => true]);
            @unlink($file->getPathname());
            return true;
        } catch (\OSS\Core\OssException $e) {
            throw $e;
        }
    }

    public function listObject($prefix)
    {
        try {
            $ossClient = new \OSS\OssClient($this->appId, $this->secret, $this->endPoint);
            $list      = $ossClient->listObjects($this->bucket, [
                \OSS\OssClient::OSS_CHECK_MD5 => true,
                \OSS\OssClient::OSS_PREFIX    => $prefix
            ]);

            return $list->getObjectList();
        } catch (\OSS\Core\OssException $e) {
            return false;
        }
    }

    public function deleteObjFromOSS(array $objects)
    {
        try {
            $ossClient = new \OSS\OssClient($this->appId, $this->secret, $this->endPoint);
            $ossClient->deleteObjects($this->bucket, $objects, [\OSS\OssClient::OSS_CHECK_MD5 => true]);
            return true;
        } catch (\OSS\Core\OssException $e) {
            return false;
        }
    }

    public function putToOSS($object, $content)
    {
        $ossClient = new \OSS\OssClient($this->appId, $this->secret, $this->endPoint);
        try {
            $ossClient->putObject($this->bucket, $object, $content, [
                \OSS\OssClient::OSS_CHECK_MD5   => true,
                \OSS\OssClient::OSS_CONTENT_MD5 => base64_encode(md5($content))
            ]);
            return true;
        } catch (\OSS\Core\OssException $e) {
            return false;
        }
    }

    public function appendToOSS($object, $content, $position = 0)
    {
        try {
            $ossClient = new \OSS\OssClient($this->appId, $this->secret, $this->endPoint);
            if ($this->prefix) {
                $object = $this->prefix . DIRECTORY_SEPARATOR . $object;
            }
            if ($position == 0) {
                $ossClient->deleteObject($this->bucket, $object);
            }
            return $ossClient->appendObject($this->bucket, $object, $content, $position);
        } catch (\OSS\Core\OssException $e) {
            return false;
        }
    }

    public function saveToDb($businessUid, $consumerUid, $realName, $remoteName)
    {
        $this->uploadModel->create([
            'business_uid' => $businessUid,
            'consumer_uid' => $consumerUid,
            'real_name'    => $realName,
            'remote_name'  => $remoteName
        ]);
    }
}
