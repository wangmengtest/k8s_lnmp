<?php

namespace vhallComponent\document\controllers\admin;

use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;

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
    const PAGE_SIZE = 10;

    /**
     * 文档-信息
     *
     * @return void
     * @throws Exception
     * @author  ensong.liu@vhall.com
     * @date    2019-02-14 14:53:17
     * @method GET
     * @request int document_id 文档ID
     */
    public function getAction()
    {
        //参数列表
        $documentId = $this->getParam('document_id');

        //文档信息
        $condition    = [
            'document_id' => $documentId,
        ];
        $documentInfo = vss_service()->getDocumentService()->info($condition);
        if (empty($documentInfo)) {
            $this->fail(ResponseCode::EMPTY_DOCUMENT);
        }

        //返回数据
        $data = $documentInfo;
        $this->success($data);
    }

    /**
     * 文档-列表
     *
     * @return void
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:16:08
     * @method  GET
     * @request string  keyword     关键字
     * @request string  begin_time  开始日期
     * @request string  end_time    结束日期
     * @request int     page        页码
     */
    public function listAction()
    {

        //1、接收参数信息
        $keyword           = $this->getParam('keyword', '');
        $beginTime         = $this->getParam('begin_time');
        $endTime           = $this->getParam('end_time');
        $transformSchedule = $this->getParam('transform_schedule');
        $page              = $this->getParam('page', 1);
        //1.1、组织数据结构
        $condition = [
            'file_name' => $keyword,
            'curr_page' => $page,
            'page_size' => self::PAGE_SIZE,
        ];
        if (!empty($keyword)) {
            $condition['file_name'] = $keyword;
        }
        if (isset($beginTime) && !empty($endTime)) {
            $condition['begin_time'] = $beginTime;
            $condition['end_time']   = $endTime;
        }
        //1.7、通过转码状态
        if (isset($transformSchedule) && $transformSchedule != '') {
            $condition['transform_schedule'] = $transformSchedule;
        }
        $condition['is_back']         = 1;
        $documentList                 = vss_service()->getDocumentService()->lists($condition);
        $documentList['current_page'] = $documentList['curr_page'];
        $documentList['per_page']     = self::PAGE_SIZE;
        $documentList['data']         = $documentList['detail'];
        unset($documentList['curr_page'], $documentList['total_page'], $documentList['detail']);

        if (!empty($documentList['data'])) {
            foreach ($documentList['data'] as $k => $v) {
                if ($v['account_id']) {
                    $where['account_id']                             = $v['account_id'];
                    $userInfo                                        = vss_model()->getAccountsModel()->getRow($where);
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
     * 文档-导出列表
     *
     * @return csv
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:16:08
     * @method  GET
     * @request string  keyword     关键字
     * @request string  begin_time  开始日期
     * @request string  end_time    结束日期
     */
    public function exportListAction()
    {
        //参数列表
        $keyword           = $this->getParam('keyword');
        $beginTime         = $this->getParam('begin_time');
        $endTime           = $this->getParam('end_time');
        $transformSchedule = $this->getParam('transform_schedule');
        $documentIds       = $this->getParam('document_id');

        //Excel文件名
        $fileName           = 'DocumentList' . date('YmdHis');
        $header             = ['文档ID', '文档名称', '上传人', '上传时间', '页数', '转码状态'];
        $exportProxyService = vss_service()->getExportProxyService()->init($fileName)->putRow($header);
        //列表数据
        $page     = 1;
        $pageSize = 3000;
        while (true) {
            //当前page下列表数据
            $condition = [
                'file_name'          => $keyword,
                'curr_page'          => $page,
                'page_size'          => $pageSize,
                'keyword'            => $keyword,
                'begin_time'         => $beginTime,
                'end_time'           => $endTime,
                'transform_schedule' => $transformSchedule,
                'document_id'        => $documentIds,
            ];

            $condition['is_back'] = 1;

            $documentList = vss_service()->getDocumentService()->lists($condition);

            $documentList['current_page'] = $documentList['curr_page'];
            $documentList['per_page']     = self::PAGE_SIZE;
            $documentList['data']         = $documentList['detail'];
            unset($documentList['curr_page'], $documentList['detail']);
            /****2019-11-27 方文学  关联上传者字段****/
            if (!empty($documentList['data'])) {
                foreach ($documentList['data'] as $k => $v) {
                    if ($v['account_id']) {
                        $where['account_id']                             = $v['account_id'];
                        $userInfo                                        = vss_model()->getAccountsModel()->getRow($where);
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

            if (!empty($documentList['data'])) {
                foreach ($documentList['data'] as $documentItem) {
                    $row = [
                        $documentItem['id'] ?: '-',
                        $documentItem['file_name'] ?: ' -',
                        $documentItem['account']['nickname'] ?: '-',
                        $documentItem['created_at'] ?: ' -',
                        $documentItem['page'] ?: '-',
                        $documentItem['trans_name'] ?: '-'
                    ];

                    $exportProxyService->putRow($row);
                }
            }

            //跳出while
            if ($page >= $documentList['total_page'] || $page >= 10) { //1页表示1W上限
                break;
            }

            //下一页
            $page++;
        }

        //下载文件
        $exportProxyService->download();
    }

    /**
     * 文档-删除
     *
     * @return void
     * @author ensong.liu@vhall.com
     * @date   2019-01-29 15:16:08
     * @method  GET
     */
    public function deleteAction()
    {
        //参数列表
        $documentIds = $this->getParam('document_ids');

        //删除文档记录
        /*$data = [];
        $paasData = [];
        $documentIdList = explode(',', $documentIds);
        foreach ($documentIdList as $documentId) {
            $condition = [
                'app_id' => $this->getParam('app_id'),
                'document_id' => $documentId,
            ];
            //$documentInfo = Documents::getInstance()->getRow($condition)
            $documentInfo=vss_service()->getDocumentService()->info($condition);
            if ($documentInfo) {
                array_push($data, $documentInfo['id']);
                array_push($paasData, $documentInfo['document_id']);
            }
        }*/
        $data                  = explode(',', $documentIds);
        $params                = [];
        $params['app_id']      = vss_service()->getTokenService()->getAppId();
        $params['document_id'] = $documentIds;
        vss_service()->getDocumentService()->delete($params);
        //返回数据
        $this->success($data);
    }
}
