<?php
namespace App\Http\services;

use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FileUpload
{
    protected $filesystem;
    protected $request;

    protected $extList = ['php', 'part', 'html', 'shtml', 'htm', 'shtm', 'js', 'jsp', 'asp', 'node', 'py', 'sh', 'bat', 'exe'];

    public function __construct(FilesystemManager $filesystem, Request $request)
    {
        $this->filesystem = $filesystem;
        $this->request    = $request;
    }

    /**
     * 移动文件
     *
     * @param string $path
     * @param string $name
     * @param string $type
     * @return void
     */
    public static function moveFile(string $path, string $name, string $type)
    {
        if (file_exists($path)) {
            $dir = $type . "/" .date('Ym');
            $disk = config('filesystems.default');
            $result = Storage::disk($disk)->putFileAs($dir, $path, $name);

            if($result) {
                unlink($path);
                return Storage::disk($disk)->url($result);
            }
        }

        return "";
    }

    /**
     * 上传文件
     * @param string $fileName
     * @return mixed|string
     */
    public function store(string $fileName, $type)
    {
        $dir = $type . "/" .date('Ym');
        $fileMsg = $this->doStore($this->request->file($fileName), $dir);

        return $fileMsg['url'] ?? '';
    }

    /**
     * @param UploadedFile $file
     * @param string $dir
     * @return array
     */
    public function doStore(UploadedFile $file, $dir = '')
    {
        $disk = config('filesystems.default');
        $hashName = str_ireplace('.jpeg', '.jpg', $file->hashName());
        $extension = pathinfo($file->getClientOriginalName())['extension'];
        $filename = pathinfo($hashName)['filename'] . '.' . $extension;
        $mime = $file->getMimeType();
        try {
            $path = $this->filesystem->disk($disk)->putFileAs($dir, $file, $filename);
        } catch (\Exception $e) {
            Log::error("upload error: " . $e->getMessage());

            return [
                'success' => false,
                'url'     => '',
            ];
        }


        return [
            'success' => true,
            'filename' => $hashName,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $mime,
            'size' => $file->getSize(),
            'relative_url' => $path,
            'url' => Storage::disk($disk)->url($path),
        ];
    }

    protected function fixMissingStorageSymlink()
    {
        app('files')->link(storage_path('app/public'), public_path('storage'));
    }

    public function checkValid($file, $mimeTypes, $size = null)
    {
        if ($size && $file->getClientSize() > $size) {
            return false;
        }

        $mimeType = strtolower($file->getClientOriginalExtension());

        if ($mimeType && in_array($mimeType, $mimeTypes) && !in_array($mimeType, $this->extList)) {
            return true;
        }
        return false;
    }
}
