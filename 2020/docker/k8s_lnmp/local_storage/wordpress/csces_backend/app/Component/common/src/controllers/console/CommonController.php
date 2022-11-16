<?php

namespace App\Component\common\src\controllers\console;

use App\Component\room\src\constants\UploadConstant;
use App\Constants\ResponseCode;
use App\Http\services\FileUpload;
use Illuminate\Http\Request;
use vhallComponent\decouple\controllers\BaseController;

/**
 * 公共接口
 * Class CommonController
 * @package App\Component\common\src\controllers
 */
class CommonController extends BaseController
{
    /**
     * 图片上传
     * @auther yaming.feng@vhall.com
     * @date 2021/6/10
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Vss\Exceptions\JsonResponseException
     */
    public function uploadImageAction(FileUpload $fileUpload, Request $request)
    {
        if($request->file('file')){
            if(!$this->isImage($_FILES['file']['name'])){
                $this->fail(ResponseCode::TYPE_INVALID_IMAGE);
            }
            $url = $fileUpload->store('file', 'img');
        }
        $this->success($url);
        /*$url = vss_service()->getUploadService()->uploadImg('file');
        $this->success($url);*/
    }

    /*
     * 判断上传的是否是图片
     * */
    protected function isImage($filename) {
        $ext = substr($filename, strrpos($filename, '.')+1);//strrpos返回最后一次出现的索引位置
        return in_array($ext, UploadConstant::IMAGE_TYPE);
    }
}
