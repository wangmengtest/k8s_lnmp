<?php

namespace vhallComponent\filterWord\controllers\admin;

use App\Constants\ResponseCode;
use vhallComponent\common\services\UploadFile;
use vhallComponent\decouple\controllers\BaseController;
use vhallComponent\filterWord\constants\FilterwordsConstant;

class FilterwordsController extends BaseController
{
    /**
     * 列表
     */
    public function listAction()
    {
        $search   = $this->getParam('search');
        $page     = $this->getParam('page', 1);
        $pageSize = $this->getParam('pagesize', 10);

        $filterWords = vss_service()->getFilterWordsService()->list($search, $page, $pageSize);

        $this->success($filterWords);
    }

    /**
     * 添加敏感词
     *
     *
     */
    public function createAction()
    {
        $filterWord = vss_service()->getFilterWordsService()
            ->create($this->getParam(), 0, $this->admin['admin_id']);
        $this->success($filterWord ?? []);
    }

    /**
     * 修改敏感词
     *
     *
     */
    public function updateAction()
    {
        $filterWord = vss_service()->getFilterWordsService()->update(
            $this->getParam(),
            0,
            $this->admin['admin_id']
        );
        $this->success($filterWord ?? []);
    }

    /**
     * 删除敏感词
     *
     *
     */
    public function deleteAction()
    {
        $filterWord = vss_service()->getFilterWordsService()->delete($this->getParam());
        $this->success($filterWord ?? []);
    }

    /**
     * 敏感词模板
     *
     * @author  jin.yang@vhall.com
     * @date    2020-03-13
     */
    public function templateAction()
    {
        //Excel文件名
//        $fileName = '敏感词模板';
        $fileName = "FilterWord" . date('YmdHis');
        vss_service()->getExportProxyService()->init($fileName)->putRow([FilterwordsConstant::TEMPLATE])->download();
    }

    /**
     * 导入敏感词
     *
     *
     * @author   ming.wang@vhall.com
     * @uses     wang-ming
     */
    public function importAction()
    {
        // 接收上传文件
        $file = new UploadFile('filterwords');
        if ($file->file == false) {
            $this->fail(ResponseCode::EMPTY_FILE);
        }

        // 上传文件
        $extension = strtolower($file->getClientOriginalExtension());
        if (UploadFile::checkType('exel', $extension) === false) {
            $this->fail(ResponseCode::TYPE_INVALID_UPLOAD);
        }

        $result = vss_service()->getFilterWordsService()
            ->importFile($file, $extension, 0, 0, $this->admin['admin_id']);
        $this->success($result ?? []);
    }
}
