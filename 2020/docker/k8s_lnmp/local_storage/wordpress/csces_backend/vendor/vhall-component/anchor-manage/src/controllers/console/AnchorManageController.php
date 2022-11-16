<?php

namespace vhallComponent\anchorManage\controllers\console;

use App\Constants\ResponseCode;
use vhallComponent\common\services\UploadFile;
use vhallComponent\decouple\controllers\BaseController;

/**
 * Class AnchorManageController
 * @authro wei.yang@vhall.com
 * @date 2021/6/15
 */
class AnchorManageController extends BaseController
{

    /**
     * 主播列表
     *
     * @author wei.yang@vhall.com
     * @date   2021/6/11
     */
    public function listAction()
    {
        $page      = $this->getParam('page', 1);
        $pageSize  = $this->getParam('page_size', 10);
        $search    = $this->getParam('search', '');
        $accountId = $this->accountInfo['account_id'];
        $ret       = vss_service()->getAnchorManageService()->getListByPage($page, $pageSize, $search, $accountId);
        $this->success($ret);
    }

    /**
     * 获取主播详情
     *
     * @author wei.yang@vhall.com
     * @date   2021/6/15
     */
    public function detailAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'anchor_id' => 'required'
        ]);
        $ret = vss_service()->getAnchorManageService()->getAnchorInfo($params['anchor_id']);
        $this->success($ret);
    }

    /**
     * 新增主播
     *
     * @author wei.yang@vhall.com
     * @date   2021/6/15
     */
    public function createAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'phone'     => 'required',
            'nickname'  => 'required',
            'real_name' => 'required',
            'avatar'    => 'required|file'
        ]);
        $file = new UploadFile('avatar');
        if (!$file->isValid()) {
            $this->fail(ResponseCode::BUSINESS_UPLOAD_IMAGE);
        }
        $urlPath   = vss_service()->getUploadService()->uploadImg($file, 'anchor_manage');
        $urlPath   = str_replace('\\', '/', $urlPath);
        $accountId = $this->accountInfo['account_id'];
        $ret       = vss_service()->getAnchorManageService()->create($accountId, $params, $urlPath);
        $this->success($ret);
    }

    /**
     * 编辑主播
     *
     * @author wei.yang@vhall.com
     * @date   2021/6/15
     */
    public function updateAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'anchor_id' => 'required',
            'nickname'  => 'required',
            'real_name' => 'required',
        ]);

        $file    = new UploadFile('avatar');
        $urlPath = '';
        if ($file->isValid()) {
            $urlPath = vss_service()->getUploadService()->uploadImg($file, 'anchor_manage');
            $urlPath = str_replace('\\', '/', $urlPath);
        }
        $accountId = $this->accountInfo['account_id'];
        $ret       = vss_service()->getAnchorManageService()->update($accountId, $params, $urlPath);
        $this->success($ret);
    }

    /**
     * 检查主播是否存在关联
     *
     * @author wei.yang@vhall.com
     * @date   2021/6/16
     */
    public function checkLinkAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'anchor_id' => 'required'
        ]);
        $ret = vss_service()->getAnchorManageService()->checkLink($params['anchor_id']);
        $this->success($ret);
    }

    /**
     * 删除主播
     *
     * @author wei.yang@vhall.com
     * @date   2021/6/15
     */
    public function deleteAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'anchor_id' => 'required'
        ]);
        $accountId = $this->accountInfo['account_id'];
        $ret       = vss_service()->getAnchorManageService()->deleteAnchor($params['anchor_id'], $accountId);
        $this->success($ret);
    }
}
