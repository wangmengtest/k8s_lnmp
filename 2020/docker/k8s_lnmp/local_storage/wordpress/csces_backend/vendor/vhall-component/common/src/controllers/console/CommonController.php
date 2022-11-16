<?php

namespace vhallComponent\common\controllers\console;

use vhallComponent\decouple\controllers\BaseController;

/**
 * 公共接口
 * Class CommonController
 * @package vhallComponent\common\controllers
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
    public function uploadImageAction()
    {
        $url = vss_service()->getUploadService()->uploadImg('file');
        $this->success($url);
    }
}
