<?php
/**
 *
 *Created by PhpStorm.
 *DATA: 2020/4/8 19:26
 */

namespace vhallComponent\common\services;

use App\Constants\ResponseCode;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use vhallComponent\common\services\uploads\UploadServiceInterface;
use Vss\Common\Services\WebBaseService;
use Vss\Exceptions\ValidationException;

class UploadService extends WebBaseService
{
    const OSS_TYPE = 1;  //oss

    const COS_TYPE = 2;  //cos

    const BLOB_TYPE = 3;  //Azure Blob 存储

    const LOCAL_TYPE = 4;

    const CUSTOM_TYPE = 5;

    const UPLOAD_TYPE_MAP = [
        'oss'    => self::OSS_TYPE,
        'cos'    => self::COS_TYPE,
        'blob'   => self::BLOB_TYPE,
        'local'  => self::LOCAL_TYPE,
        'custom' => self::CUSTOM_TYPE,
    ];

    private $prefix = 'vss';

    // 默认存储类型
    private $defaultUploadType = self::OSS_TYPE;

    public function __construct()
    {
        $uploadType = trim(vss_config('upload.type'));
        if ($uploadType && !empty(self::UPLOAD_TYPE_MAP[$uploadType])) {
            $this->defaultUploadType = $uploadType;
        }
    }

    /**
     *
     * @param $params
     *
     * @return mixed|void
     *
     */
    public function create($params)
    {
        vss_validator($params, [
            'tag'        => 'in:' . implode(',', self::UPLOAD_TYPE_MAP),
            'file_name'  => 'string',
            'path'       => 'string',
            'force_name' => 'string'
        ]);
        $fileName = $params['file_name'] ?? 'file';
        $file     = new UploadFile($fileName);

        if (!$params['tag']) {
            $params['tag'] = $this->defaultUploadType;
        }

        return $this->uploadFile($file, 'image', $params['path'], $params['force_name'], $params['tag']);
    }

    /**
     * 上传 base64 格式图片
     * @auther yaming.feng@vhall.com
     * @date 2021/3/24
     *
     * @param string $base64Content 图片的 base64 内容
     * @param string $ext           图片的扩展名
     * @param string $urlPath       图片要上传的路径
     *
     * @return string
     * @throws Exception
     */
    private function uploadBase64Img($base64Content, $ext = '', $urlPath = '')
    {
        if (empty($base64Content)) {
            throw new ValidationException(ResponseCode::TYPE_INVALID_IMAGE);
        }

        if (!$urlPath) {
            $hashName = md5($base64Content) . '.' . ($ext ?: $this->getBase64ImgExt($base64Content, 'jpg'));
            $urlPath  = 'upload' . DIRECTORY_SEPARATOR . date('Ym') . DIRECTORY_SEPARATOR . $hashName;
        }

        $filePath = storage_path('/public') . DIRECTORY_SEPARATOR . $urlPath;
        if (UploadFile::mkdirs(dirname($filePath)) === false) {
            throw new ValidationException(ResponseCode::TYPE_INVALID_IMAGE);
        }

        if (strstr($base64Content, ",")) {
            $base64Content = explode(',', $base64Content)[1];
        }

        if (!file_put_contents($filePath, base64_decode($base64Content))) {
            throw new ValidationException(ResponseCode::BUSINESS_UPLOAD_FAILED);
        }

        return $this->localFileUpload($filePath, $urlPath);
    }

    /**
     * 图片上传
     * @auther yaming.feng@vhall.com
     * @date 2021/6/4
     *
     * @param string $file       可以是 $_FILES 的属性名， 也可以是路径， 也可以是 base64 内容
     * @param false  $uploadPath 上传后的文件路径
     * @param false  $ext        指定图片后缀， base64 有用
     *
     * @return string
     * @throws Exception
     */
    public function uploadImg($file, $uploadPath = false, $ext = false)
    {
        if ($_FILES[$file] || is_file($file) || $file instanceof UploadFile) {
            $fileName = false;
            if (strpos($uploadPath, '.') !== false) {
                $fileName   = basename($uploadPath);
                $uploadPath = str_replace($fileName, '', $uploadPath);
            }
            return $this->uploadFile($file, 'image', $uploadPath, $fileName);
        }

        $file = $_REQUEST[$file] ?? $file;

        // 检查长度，是否是 base64 格式
        if (strlen($file) < 200) {
            return '';
        }

        return $this->uploadBase64Img($file, $ext, $uploadPath);
    }

    /**
     * 文件写入
     *
     * @param $module
     * @param $fileName
     * @param $content
     *
     * @return bool
     */
    public function noteLocal($module, $fileName, $content)
    {
        try {
            $ext = strrchr($fileName, '.');
            if (!in_array($ext, ['.json', '.log', '.txt'])) {
                throw new ValidationException(ResponseCode::TYPE_INVALID_FILE);
            }

            $path     = "upload/{$module}/";
            $filePath = storage_path('/public/') . $path;
            if (!is_dir($filePath)) {
                mkdir($filePath, 0777, true);
            }
            $filePath = $filePath . $fileName;

            file_put_contents($filePath, json_encode($content));

            $ossFilePath = "{$path}{$fileName}";
            $ossFileUrl  = $this->localFileUpload($filePath, $ossFilePath);

            return $ossFileUrl;
        } catch (\Exception $e) {
            vss_logger()->error('uploadNoteLocalError', [$e->getMessage()]);
            return false;
        }
    }

    /**
     * 获取 base64 图片扩展名
     * @auther yaming.feng@vhall.com
     * @date 2021/6/4
     *
     * @param        $content
     * @param string $defaultExt
     *
     * @return mixed|string
     */
    public function getBase64ImgExt($content, $defaultExt = '')
    {
        if (strpos($content, 'data:') == false) {
            return $defaultExt;
        }

        $prefix = explode(';', $content)[0];
        return explode('/', $prefix)[1] ?? $defaultExt;
    }

    /**
     * Create IReader
     *
     * @param string $extension 文件扩展名
     * @param string $filePath  文件路径，用于检查文件编码，防止中文乱码
     *
     * @return IReader
     * @throws Exception
     */
    public function getExcelReader(string $extension, string $filePath = '')
    {
        $types = ['xls', 'xlsx', 'csv', 'xml', 'html'];

        if (!in_array($extension, $types)) {
            throw new ValidationException(ResponseCode::TYPE_INVALID_UPLOAD);
        }

        $reader = IOFactory::createReader(ucfirst($extension));
        if (strtolower($extension) == 'csv') {
            // 设置默认分隔符
            $reader->setDelimiter(',');
        }

        if ($filePath) {
            // 检查文件编码，防止中文乱码
            $encoding = file_get_contents($filePath, false, null, 0, 4);
            if (bin2hex($encoding)[0] == 'c') {
                $reader->setInputEncoding('GBK');
            }
        }

        return $reader;
    }

    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/1/13
     *
     * @param string|UploadFile $localFilePath  本地文件路径
     * @param string            $uploadFilePath 上传文件路径
     * @param int               $tag            存储类型
     *
     * @return string $ossFileUrl
     */
    public function localFileUpload($localFilePath, $uploadFilePath, $tag = 0)
    {
        $tag = $tag ?: $this->defaultUploadType;

        $file = $localFilePath instanceof UploadFile ? $localFilePath : new UploadFile($localFilePath);

        if (!$file->isFileExist()) {
            throw new ValidationException(ResponseCode::EMPTY_FILE);
        }

        if (is_numeric($tag)) {
            $tag = array_flip(self::UPLOAD_TYPE_MAP)[$tag] ?? '';
            if (!$tag) {
                throw new ValidationException(ResponseCode::TYPE_INVALID_STORAGE, ['type' => $tag]);
            }
        }

        $uploadFilePath = $this->makeUploadService($tag)->upload($file, $uploadFilePath);
        if (!$uploadFilePath) {
            throw new ValidationException(ResponseCode::BUSINESS_UPLOAD_FAILED);
        }

        return $uploadFilePath;
    }

    /**
     *
     * @param      $file
     * @param      $type
     * @param null $path
     * @param bool $forceName
     * @param bool $useLogin
     * @param      $tag
     *
     * @return string
     *
     */
    public function uploadFile($file, $type, $path = null, $forceName = false, $tag = 0)
    {
        $file = $file instanceof UploadFile ? $file : new UploadFile($file);

        if ($file && $file->isValid()) {
            $hash     = md5_file($file->getRealPath());
            $fileName = $forceName ? $forceName : $hash;
            $ext      = strtolower($file->getClientOriginalExtension());

            // 检测上传文件格式
            $this->checkType($type, $ext);

            $savePath = substr($hash, 0, 2) . DIRECTORY_SEPARATOR . substr($hash, 2, 2);

            // 检测上传路径
            if (!is_null($path) && $path = $this->checkAddress($path)) {
                $savePath = $path . DIRECTORY_SEPARATOR . $savePath;
            }
            $uploadFilePath = $savePath . DIRECTORY_SEPARATOR . $fileName . '.' . $ext;
            if ($this->prefix) {
                $uploadFilePath = $this->prefix . DIRECTORY_SEPARATOR . $uploadFilePath;
            }

            return $this->localFileUpload($file, $uploadFilePath, $tag);
        }
    }

    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/2/8
     *
     * @param string $type 上传类型
     *
     * @return UploadServiceInterface
     */
    protected function makeUploadService($type)
    {
        $uploadConfig = (array)vss_config('upload.' . $type, []);
        if (self::UPLOAD_TYPE_MAP[$type] == self::CUSTOM_TYPE) {
            $class = $uploadConfig['class'] ?? '';
            if (!$class || !class_exists($class)) {
                throw new ValidationException(ResponseCode::BUSINESS_UPLOAD_FAILED);
            }
            unset($uploadConfig['class']);
        } else {
            $class = 'vhallComponent\\common\\services\\uploads\\' . ucfirst($type) . 'Upload';
        }
        return vss_make($class, $uploadConfig);
    }

    /**
     *
     * @param $address
     *
     * @return mixed
     */
    public function checkAddress($address)
    {
        return $address;
    }

    /**
     *
     * @param $type
     * @param $ext
     *
     * @throws Exception
     */
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
                    throw new ValidationException(ResponseCode::TYPE_INVALID_UPLOAD);
                }
            }

            if (!in_array($ext, $tmpAllowType)) {
                throw new ValidationException(ResponseCode::TYPE_INVALID_UPLOAD);
            }
        } else {
            if (!isset($allowType[$type]) || !in_array($ext, $allowType[$type])) {
                throw new ValidationException(ResponseCode::TYPE_INVALID_UPLOAD);
            }
        }
    }
}
