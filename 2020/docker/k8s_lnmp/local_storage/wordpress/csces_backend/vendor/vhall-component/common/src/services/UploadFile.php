<?php

namespace vhallComponent\common\services;

use SplFileInfo;

class UploadFile extends SplFileInfo
{
    public $file = false;

    public $sourceFile = false;

    public function __construct($fileName)
    {
        if (isset($_FILES[$fileName])) {
            $this->sourceFile = $_FILES[$fileName]['tmp_name'];
            $this->file       = $_FILES[$fileName];
            parent::__construct($this->sourceFile);
        } elseif (is_file($fileName)) {
            $this->sourceFile = $fileName;
            parent::__construct($this->sourceFile);
            $this->file = [
                'name' => $this->getFilename()
            ];
        }
    }

    public function isFileExist()
    {
        return empty($this->file) ? false : true;
    }

    /**
     * 获取扩展名
     * @return bool
     */
    public function getClientOriginalExtension()
    {
        if (isset($this->file['name'])) {
            $nameMsg = explode('.', $this->file['name']);

            $countMsg = count($nameMsg);
            if ($countMsg > 1) {
                return $nameMsg[$countMsg - 1];
            }
        }

        return false;
    }

    /**
     * 获取文件名
     * @return bool
     */
    public function getClientOriginalName()
    {
        if (isset($this->file['name'])) {
            return $this->file['name'];
        }

        return false;
    }

    /**
     * 获取文件是否有效
     */
    public function isValid()
    {
        return empty($this->file) ? false : true;
    }

    public static function move($sourceFile, $rawFile)
    {
        if (self::mkdirs(dirname($rawFile)) === false) {
            return false;
        }

        return move_uploaded_file($sourceFile, $rawFile);
    }

    public static function mkdirs($dir, $mode = 0777)
    {
        if (is_dir($dir) || @mkdir($dir, $mode, true)) {
            return true;
        }
    }

    public static function checkType($type, $ext)
    {
        $allowType = [
            'image' => [
                'bmp',
                'gif',
                'jpg',
                'psd',
                'png',
                'jpeg',
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
                'mov',
            ],
            'app'   => [
                'apk',
                'ipa',
            ],
            'exe'   => [
                'exe',
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
                'csv',
                'xls',
                'xlsx',
            ],
            'audio' => [
                'mp3',
                'wav',
            ],
            'zip'   => [
                'zip',
                'rar',
            ],
        ];

        if (is_array($type)) {
            $tmpAllowType = [];
            foreach ($type as $value) {
                if (isset($allowType[$value])) {
                    $tmpAllowType = array_merge($tmpAllowType, $allowType[$value]);
                } else {
                    return false;
                }
            }

            if (!in_array($ext, $tmpAllowType)) {
                return false;
            }
        } else {
            if (!isset($allowType[$type]) || !in_array($ext, $allowType[$type])) {
                return false;
            }
        }

        return true;
    }

}
