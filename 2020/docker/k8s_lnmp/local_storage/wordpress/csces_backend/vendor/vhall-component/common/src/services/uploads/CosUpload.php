<?php

namespace vhallComponent\common\services\uploads;

use Qcloud\Cos\Client;

class CosUpload implements UploadServiceInterface
{
    public $bucket;

    public $prefix;

    public $secretId;

    public $secretKey;

    public $region;

    /**
     * CosUploadServices constructor.
     *
     * @param $secretId
     * @param $bucket
     * @param $secretKey
     */
    public function __construct($region, $secretId, $secretKey, $bucket)
    {
        $this->bucket    = $bucket;
        $this->secretId  = $secretId;
        $this->secretKey = $secretKey;
        $this->region    = $region;
    }

    /**
     *
     * @param        $fileObject
     * @param string $dir
     * @param string $newFile
     * @param bool   $isShortUrl
     *
     * @return array|bool
     * @throws \OSS\Core\OssException
     */
    public function uploadToCos($file, $filePath, $newFile = '', $dir = 'images')
    {
        try {
            $cosClient = new  Client([
                'region'      => $this->region,
                'credentials' => [
                    'secretId'  => $this->secretId,
                    'secretKey' => $this->secretKey
                ]
            ]);
            $result    = $cosClient->putObject(
                [
                    //bucket的命名规则为{name}-{appid} ，此处填写的存储桶名称必须为此格式
                    'Bucket' => $this->bucket,
                    'Key'    => $filePath,
                    'Body'   => file_get_contents($file)
                ]
            );
            if ($result['Location']) {
                @unlink($file->getPathname());
                return 'https://' . $result['Location'];
            }
            return false;
        } catch (\Exception $e) {
            return "$e\n";
        }
    }

    public function upload($localFilePath, $uploadFilePath)
    {
        return $this->uploadToCos($localFilePath, $uploadFilePath);
    }
}
