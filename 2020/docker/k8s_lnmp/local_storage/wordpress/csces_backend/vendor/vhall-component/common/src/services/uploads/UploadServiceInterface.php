<?php

namespace vhallComponent\common\services\uploads;

interface UploadServiceInterface
{
    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/2/8
     *
     * @param string $localFilePath  待上传的本地文件路径
     * @param string $uploadFilePath 上传后的文件路径
     *
     * @return string 返回上传后的文件路径， 为空则上传失败
     */
    public function upload(string $localFilePath, string $uploadFilePath);
}
