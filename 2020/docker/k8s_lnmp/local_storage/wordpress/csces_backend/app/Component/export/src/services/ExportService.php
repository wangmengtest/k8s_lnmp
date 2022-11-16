<?php

namespace App\Component\export\src\services;

use App\Http\services\FileUpload;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;
use App\Component\common\src\services\UploadFile;
use Vss\Common\Services\WebBaseService;

/**
 * ExportService
 *
 * @author   jin.yangjin@vhall.com
 * @uses     yangjin
 * @date     2020-10-28
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class ExportService extends WebBaseService
{
    /**
     * 获取导出列表
     *
     * @param $ilId
     * @param $accountId
     * @param $sourceId
     * @param $export
     * @param $status
     * @param $beginTime
     * @param $endTime
     * @param $page
     * @param $pageSize
     *
     * @return LengthAwarePaginator
     * @author  jin.yang@vhall.com
     * @date    2020-10-23
     */
    public function getList($ilId, $accountId, $sourceId, $export, $status, $beginTime, $endTime, $page, $pageSize)
    {
        $condition = [
            'export'     => $export,
            'status'     => $status,
            'begin_time' => $beginTime,
            'end_time'   => $endTime,
        ];
        if ($ilId) {
            $condition['il_id'] = $ilId;
        }
        if ($accountId) {
            $condition['account_id'] = $accountId;
        }
        if ($sourceId) {
            $condition['source_id'] = $sourceId;
        }
        return vss_model()->getExportModel()->setPerPage($pageSize)->getList($condition, [], $page);
    }

    /**
     * 导出执行
     * @return bool
     *
     */
    public function execute()
    {
        $exportList = vss_model()->getExportModel()->getInstance()
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->limit(1000)
            ->get()->toArray();
        if (empty($exportList)) {
            return true;
        }

        $path = storage_path('/') . 'public/upload/export/';
        //$path = storage_path('/') . 'app/public/' . "csv/" .date('Ym') . '/';
        $mk   = UploadFile::mkdirs($path);

        if (!$mk) {
            vss_logger()->error('Export@execute:', [
                'message' => "文件目录创建失败:" . $path
            ]);
            return false;
        }

        foreach ($exportList as $export) {
            // 修改任务为执行中
            $res = vss_model()->getExportModel()->getInstance()
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->where('id', $export['id'])
                ->update(['status' => 2]);

            if (!$res) {
                continue;
            }

            try {
                if (!$this->exportCallback($export, $path)) {
                    throw new Exception('导出函数不存在');
                }

                // 将本地文件存储到 OSS 上
                $ext         = $this->getFileExt($path, $export['file_name']);
                $fileName    = $export['file_name'] . '.' . $ext;
                $filePath    = $path . $fileName;
                $ossFileUrl  = FileUpload::moveFile($filePath, $fileName, 'csv');

                //修改导出表状态
                vss_model()->getExportModel()->getInstance()
                    ->where('id', $export['id'])
                    ->update(['status' => 3, 'oss_file' => $ossFileUrl, 'ext' => $ext]);
            } catch (Throwable $e) {
                vss_logger()->error('export-execute', [
                    'id'       => $export['id'],
                    'export'   => $export['export'],
                    'callback' => $export['callback'],
                    'message'  => $e->getMessage(),
                ]);
                vss_model()->getExportModel()->getInstance()->where('id', $export['id'])->update(['status' => 4]);
            }
        }
        return true;
    }

    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/1/15
     *
     * @param array  $export
     * @param string $path
     *
     * @return bool
     */
    public function exportCallback($export, $path)
    {
        if ($export['callback'] && strpos($export['callback'], ':') !== false) {
            list($serviceName, $funcName) = explode(':', $export['callback']);

            $serviceName = 'get' . ucfirst($serviceName) . 'Service';
            $vssService  = vss_service();
            if (method_exists($vssService, $serviceName)) {
                $service = $vssService->$serviceName();
                if (method_exists($service, $funcName)) {
                    $service->$funcName($export, $path);
                    return true;
                }
            }
        }

        $funcName = 'get' . ucfirst($export['export']) . 'ExportData';
        if (method_exists($this, $funcName)) {
            $this->$funcName($export, $path);
            return true;
        }
        return false;
    }

    /**
     * 获取文件扩展名
     * @auther yaming.feng@vhall.com
     * @date 2021/5/19
     *
     * @param $dir
     * @param $fileName
     *
     * @return string
     */
    public function getFileExt($dir, $fileName): string
    {
        $files = glob("$dir/$fileName.*");
        if (!$files) {
            return '';
        }
        return pathinfo($files[0], PATHINFO_EXTENSION);
    }
}
