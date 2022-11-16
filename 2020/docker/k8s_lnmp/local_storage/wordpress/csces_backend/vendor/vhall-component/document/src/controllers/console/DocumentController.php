<?php

namespace vhallComponent\document\controllers\console;

use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;
use vhallComponent\common\services\UploadFile;

/**
 * ExamControllerTrait
 *
 * @uses     yangjin
 * @date     2020-07-21
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class DocumentController extends BaseController
{
    /**
     * @return ValidatorUtils
     */
    public function validate($data, $rules, $messages = [], $customAttributes = [])
    {
        return new ValidatorUtils($data, $rules, $messages, $customAttributes);
    }

    /**
     * 文档-上传文档
     *
     * @author ensong.liu@vhall.com
     * @date   2019-05-21 14:50:54
     */
    public function uploadAction()
    {
        $file = new UploadFile('document');
        if ($file::checkType('doc', strtolower($file->getClientOriginalExtension())) === false) {
            $this->fail(ResponseCode::TYPE_INVALID_UPLOAD);
        }

        $params           = [];
        $params['app_id'] = vss_service()->getTokenService()->getAppId();
        if ($this->getParam('room_id')) {
            $params['room_id'] = $this->getParam('room_id');
        } else {
            $params['account_id'] = $this->accountInfo['account_id'];
        }

        $doc = vss_service()->getDocumentService()->upload($params);
        if (!isset($doc['document_id']) && $doc['document_id'] < 1) {
            $this->fail(ResponseCode::BUSINESS_UPLOAD_FAILED);
        }

        $this->success();
    }

    /**
     * 文档-删除记录
     *
     * @throws Exception
     * @author ensong.liu@vhall.com
     * @date   2019-05-21 15:17:15
     */
    public function deleteAction()
    {
        //参数列表
        $documentIds = $this->getParam('document_ids');

        $data                  = explode(',', $documentIds);
        $params                = [];
        $params['app_id']      = vss_service()->getTokenService()->getAppId();
        $params['document_id'] = $documentIds;
        vss_service()->getDocumentService()->delete($params);
        //返回数据
        $this->success($data);
    }

    /**
     * 文档-获取记录
     *
     * @throws Exception
     * @author ensong.liu@vhall.com
     * @date   2019-05-21 14:49:15
     */
    public function getAction()
    {
        //参数列表
        $documentId = $this->getParam('document_id');

        //文档信息
        $condition = [
            'document_id' => $documentId,
            'app_id'      => vss_service()->getTokenService()->getAppId(),
        ];

        $documentInfo = vss_service()->getDocumentService()->info($condition);

        if (empty($documentInfo)) {
            $this->fail(ResponseCode::EMPTY_DOCUMENT);
        }

        $this->success($documentInfo);
    }

    /**
     * 文档-获取列表
     *
     * @throws Exception
     * @author ensong.liu@vhall.com
     * @date   2019-05-21 14:49:42
     */
    public function listAction()
    {
        $keyword  = $this->getParam('keyword', '');
        $page     = $this->getParam('page', 1);
        $pageSize = $this->getParam('page_size', 10);

        //1.1、组织数据结构
        $params = [
            'file_name'  => $keyword,
            'app_id'     => vss_service()->getTokenService()->getAppId(),
            'account_id' => $this->accountInfo['account_id'],
            'page_size'  => $pageSize,
            //            'is_back'    => 1,
            'curr_page'  => $page
        ];

        $documentList                 = vss_service()->getDocumentService()->lists($params);
        $documentList['current_page'] = $documentList['curr_page'];
        $documentList['per_page']     = $pageSize;
        $documentList['data']         = $documentList['detail'];
        unset($documentList['curr_page'], $documentList['total_page'], $documentList['detail']);

        if (!empty($documentList['data'])) {
            foreach ($documentList['data'] as $k => $v) {
                if ($v['account_id']) {
                    $where['account_id'] = $v['account_id'];
                    $userInfo            = vss_service()->getAccountsService()->getOne($where);

                    $documentList['data'][$k]['account']['nickname'] = $userInfo['nickname'];
                }
                switch ($v['trans_status']) {
                    case 1:
                        $documentList['data'][$k]['trans_name'] = '待转码';
                        break;
                    case 2:
                        $documentList['data'][$k]['trans_name'] = '转码中';
                        break;
                    case 3:
                        $documentList['data'][$k]['trans_name'] = '转码成功';
                        break;
                    case 4:
                        $documentList['data'][$k]['trans_name'] = '转码失败';
                        break;
                    default:
                        $documentList['data'][$k]['trans_name'] = '';
                }
            }
        }
        $this->success($documentList);
    }

    /**
     * 开启/关闭文档
     *
     * @return void
     * @author ensong.liu@vhall.com
     * @date   2019-05-09 15:59:52
     */
    public function switchAction()
    {
        $ilId   = $this->getParam('il_id', 0);
        $action = $this->getParam('action', 'open');
        if (in_array($action, ['open', 'close']) === false) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }

        //房间信息
        $interactiveLiveInfo = vss_model()->getRoomsModel()->getInfoByIlIdAndAccountId(
            $ilId,
            $this->accountInfo['account_id']
        );
        if (!$interactiveLiveInfo) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }

        //修改文档开关状态
        vss_model()->getRoomsModel()->updateIsOpenDocument(
            $this->accountInfo['account_id'],
            $ilId,
            $action == 'close' ? 0 : 1
        );

        //发送消息

        vss_service()->getPaasChannelService()->sendMessageByChannel(
            $interactiveLiveInfo['channel_id'],
            ['module' => 'Document', 'action' => $action],
            null,
            'service_custom'
        );

        $this->success();
    }

    /**
     * 文档加载上报
     * 加载文档完成上报接口
     *
     * @return void
     * @author ensong.liu@vhall.com
     * @date   2019-05-09 16:02:38
     */
    public function loadedAction()
    {
        $ilId       = $this->getParam('il_id', 0);
        $documentId = $this->getParam('document_id', 0);

        if (!vss_model()->getRoomDocumentsModel()->getListByAccountIdAndDocumentIds(
            $this->accountInfo['account_id'],
            [$documentId]
        )) {
            $this->fail(ResponseCode::EMPTY_DOCUMENT);
        }

        $interactiveLiveInfo = vss_model()->getRoomsModel()->getInfoByIlIdAndAccountId(
            $ilId,
            $this->accountInfo['account_id']
        );
        if (!$interactiveLiveInfo) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }
        vss_model()->getDocumentStatusModel()->insert($this->accountInfo['account_id'], $ilId, $documentId);

        $this->success();
    }

    /**
     * 查询回放文档是否存在
     *
     * @return void
     * @author ensong.liu@vhall.com
     * @date   2019-05-09 16:08:15
     */
    public function getStatusAction()
    {
        $ilId      = $this->getParam('il_id', 0);
        $record_id = $this->getParam('record_id', 0);

        $interactiveLiveInfo = vss_model()->getRoomsModel()->getInfoByIlIdAndAccountId(
            $ilId,
            $this->accountInfo['account_id']
        );
        if (!$interactiveLiveInfo) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }
        $status = vss_model()->getDocumentStatusModel()->findExistsByRecordId($record_id, $ilId);

        $data['exists'] = $status;
        $this->success($data);
    }
}
