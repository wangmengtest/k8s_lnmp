<?php

namespace App\Component\common\src\services;

/**
 * FormService
 * 表单服务公共方法
 *
 * @author   jin.yangjin@vhall.com
 * @uses     yangjin
 * @date     2020-11-03
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class FormService
{
    /**
     * @param int    $id
     * @param string $module
     * @param array  $extend
     *
     * @return string
     *
     * @author  jin.yang@vhall.com
     * @date    2020-11-03
     */
    public function writeInfoLocal(int $id, string $module, array $extend = [])
    {
        try {
            $detail = vss_service()->getPaasService()->getFormInfo($id);
            if (is_array($detail)) {
                $detail = array_merge($detail, $extend);
            }
            $path     = "upload/{$module}";
            $filePath = storage_path('/') . 'public/' . $path;
            if (!is_dir($filePath)) {
                mkdir($filePath, 0777, true);
            }

            $fileName = "{$id}.json";

            $filePath    = "{$filePath}/{$fileName}";
            $ossFilePath = "{$path}/{$fileName}";

            file_put_contents($filePath, json_encode($detail));
            $ossFileUrl = vss_service()->getUploadService()->localFileUpload($filePath, $ossFilePath);

            return $ossFileUrl;
        } catch (\Exception $e) {
            vss_logger()->error('发布-写入文件错误', [
                'errCode' => $e->getCode(),
                'errMsg'  => $e->getMessage()
            ]);
        }

        return '';
    }
}
