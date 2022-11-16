<?php

namespace App\Component\room\src\controllers\admin;

use vhallComponent\decouple\controllers\BaseController;

/**
 * StatController extends BaseController
 *
 * @uses     yangjin
 * @date     2020-09-15
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class StatController extends BaseController
{
    /**
     * 统计-概览
     *
     * @return void
     * @author ensong.liu@vhall.com
     * @date   2019-01-29 15:07:54
     * @method  GET
     */
    public function indexAction()
    {
        //筛选条件
        $conditionDay   = [
            'begin_time' => date('Y-m-d 00:00:00'),
        ];
        $conditionWeek  = [
            'begin_time' => date('Y-m-d 00:00:00', strtotime('-' . (date('w') - 1) . ' days')),
        ];
        $conditionMonth = [
            'begin_time' => date('Y-m-d 00:00:00', strtotime('-' . (date('j') - 1) . ' days')),
        ];
        $conditionYear  = [
            'begin_time' => date('Y-m-d 00:00:00', strtotime('-' . (date('z')) . ' days')),
        ];

        $data = [];

        //概览-房间
        $liveStat          = [
            'total' => vss_model()->getRoomsModel()->getCount(),
            'day'   => vss_model()->getRoomsModel()->getCount(['created_at_begin' => $conditionDay['begin_time']]),
            'week'  => vss_model()->getRoomsModel()->getCount(['created_at_begin' => $conditionWeek['begin_time']]),
            'month' => vss_model()->getRoomsModel()->getCount(['created_at_begin' => $conditionMonth['begin_time']]),
            'year'  => vss_model()->getRoomsModel()->getCount(['created_at_begin' => $conditionYear['begin_time']]),
        ];
        $data['live_stat'] = $liveStat;

        //概览-用户
        $accountStat          = [
            'total' => vss_model()->getAccountsModel()->getCount(),
            'day'   => vss_model()->getAccountsModel()->getCount($conditionDay),
            'week'  => vss_model()->getAccountsModel()->getCount($conditionWeek),
            'month' => vss_model()->getAccountsModel()->getCount($conditionMonth),
            'year'  => vss_model()->getAccountsModel()->getCount($conditionYear),
        ];
        $data['account_stat'] = $accountStat;

        //概览-管理员
        $adminStat          = [
            'total' => vss_model()->getAdminsModel()->getCount(),
            'day'   => vss_model()->getAdminsModel()->getCount($conditionDay),
            'week'  => vss_model()->getAdminsModel()->getCount($conditionWeek),
            'month' => vss_model()->getAdminsModel()->getCount($conditionMonth),
            'year'  => vss_model()->getAdminsModel()->getCount($conditionYear),
        ];
        $data['admin_stat'] = $adminStat;

        # vhallEOF-question-adminStat-index-1-start
        
        //概览-问卷
        $questionsModel = vss_model()->getQuestionsModel();
        $questionStat = [
            "total" => $questionsModel->getCount(),
            "day"   => $questionsModel->getCount($conditionDay),
            "week"  => $questionsModel->getCount($conditionWeek),
            "month" => $questionsModel->getCount($conditionMonth),
            "year"  => $questionsModel->getCount($conditionYear),
        ];
        $data["question_stat"] = $questionStat;

        # vhallEOF-question-adminStat-index-1-end

        //概览-文档
        # vhallEOF-document-adminStat-index-1-start
        
        $documentModel = vss_model()->getRoomDocumentsModel();
        $documentStat = [
            "total" => $documentModel->getCount(),
            "day"   => $documentModel->getCount($conditionDay),
            "week"  => $documentModel->getCount($conditionWeek),
            "month" => $documentModel->getCount($conditionMonth),
            "year"  => $documentModel->getCount($conditionYear),
        ];
        $data["document_stat"] = $documentStat;

        # vhallEOF-document-adminStat-index-1-end

        $this->success($data);
    }

    /**
     * 统计-房间
     *
     * @return void
     *
     * @throws Exception
     * @author  ensong.liu@vhall.com
     * @date    2019-02-13 14:56:56
     * @method  GET
     * @request int     il_id       房间ID
     * @request string  begin_time  开始时间
     * @request string  end_time    结束时间
     */
    public function liveAction()
    {
        //参数列表
        $ilId      = $this->getParam('il_id');
        $beginTime = $this->getParam('begin_time');
        $endTime   = $this->getParam('end_time');
        $status    = $this->getParam('status', 1);
        $accountId = '';

        $result = vss_service()->getStatService()->live($accountId, $ilId, $beginTime, $endTime, $status);

        $this->success($result);
    }


    ##### 新版异步导出

    /**
     * 异步导出列表
     *
     * @author  jin.yang@vhall.com
     * @date    2020-10-23
     */
    public function exportListAction()
    {
        $params    = $this->getParam();
        $validator = vss_validator($params, [
            'il_id'      => 'required',
            'export'     => 'required',
            'status'     => '',
            'begin_time' => '',
            'end_time'   => '',
        ]);
        //参数列表
        $ilId      = $this->getParam('il_id');
        $sourceId  = $this->getParam('source_id');
        $export    = $this->getParam('export', '');
        $beginTime = $this->getParam('begin_time', '');
        $endTime   = $this->getParam('end_time', '');
        $page      = $this->getParam('page', 1);
        $pageSize  = $this->getParam('page_size', 10);
        $status    = $this->getParam('status') ? $this->getParam('status') : '';

        $list = vss_service()->getExportService()->getList(
            $ilId,
            0,
            $sourceId,
            $export,
            $status,
            $beginTime,
            $endTime,
            $page,
            $pageSize
        );
        $this->success($list);
    }


    /**
     * 创建pv异步导出
     *
     * @author  jin.yang@vhall.com
     * @date    2020-10-27
     */
    public function exportPvAction()
    {
        $params    = $this->getParam();
        $validator = vss_validator($params, [
            'il_id'      => 'required',
            'begin_time' => 'required',
            'end_time'   => 'required',
        ]);
        //参数列表
        $ilId      = $this->getParam('il_id');
        $beginTime = $this->getParam('begin_time', '2020-01-01');
        $endTime   = $this->getParam('end_time', date('Y-m-d'));
        $status    = $this->getParam('status', 1);

        vss_service()->getStatService()->exportPV($ilId, 0, $beginTime, $endTime, $status);
        $this->success();
    }
}
