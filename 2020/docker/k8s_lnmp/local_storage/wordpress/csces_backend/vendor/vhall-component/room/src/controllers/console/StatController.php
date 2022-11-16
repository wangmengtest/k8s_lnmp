<?php

namespace vhallComponent\room\controllers\console;

use vhallComponent\decouple\controllers\BaseController;
use vhallComponent\room\constants\RoomConstant;

/**
 * StatController
 *
 * @author   jin.yangjin@vhall.com
 * @uses     yangjin
 * @date     2020-08-19
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class StatController extends BaseController
{
    /**
     * 统计-房间
     *
     * @return void
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
        $accountId = $this->accountInfo['account_id'];

        $result = vss_service()->getStatService()->live($accountId, $ilId, $beginTime, $endTime, $status);

        $this->success($result);
    }

    /**
     * 统计-并发趋势列表
     *
     * @author ensong.liu@vhall.com
     * @date   2019-05-22 19:53:34
     */
    public function liveConcurrentAction()
    {
        //参数列表
        $ilId      = $this->getParam('il_id');
        $beginTime = $this->getParam('begin_time');
        $endTime   = $this->getParam('end_time');
        $accountId = $this->accountInfo['account_id'];

        $data = vss_service()->getStatService()->liveConcurrent($accountId, $ilId, $beginTime, $endTime);
        $this->success($data);
    }

    /**
     * 统计-观看趋势列表
     *
     * @author ensong.liu@vhall.com
     * @date   2019-05-22 19:53:32
     */
    public function liveAttendAction()
    {
        //参数列表
        $ilId      = $this->getParam('il_id');
        $beginTime = $this->getParam('begin_time');
        $endTime   = $this->getParam('end_time');
        $status    = $this->getParam('status');

        $st = [];
        if ($status == RoomConstant::LIVE_PLAY_ALL) {
            $data = vss_service()->getStatService()->allState($this->accountInfo['account_id'], $ilId, $beginTime,
                $endTime);
        } elseif ($status == RoomConstant::LIVE_STATUS_START) {
            $data = vss_service()->getStatService()->roomState($this->accountInfo['account_id'], $ilId, $beginTime,
                $endTime);
        } elseif ($status == RoomConstant::LIVE_PLAY_BACK) {
            $data = vss_service()->getStatService()->recordState($this->accountInfo['account_id'], $ilId, $beginTime,
                $endTime);
        }
        foreach ($data ?: [] as $key => $value) {
            $st[] = [
                'datetime'            => date('Y-m-d H:i:s', strtotime($value['created_time']) + 3600),
                'duration_count'      => round($value['duration'] / 60),
                'watch_count'         => $value['pv_num'],
                'watch_account_count' => $value['uv_num'] + 1, // 加上主持人
            ];
        }
        $this->success($st);
    }

    /**
     * 统计-终端使用列表
     *
     * @author ensong.liu@vhall.com
     * @date   2019-05-22 19:56:23
     */
    public function liveTerminalAction()
    {
        //参数列表
        $ilId      = $this->getParam('il_id');
        $beginTime = $this->getParam('begin_time');
        $endTime   = $this->getParam('end_time');
        $status    = $this->getParam('status');

        $data = [['name' => '移动端', 'value' => 0], ['name' => 'PC端', 'value' => 0]];
        if ($status == RoomConstant::LIVE_PLAY_ALL) {
            $data = vss_service()->getStatService()->allTerminal($this->accountInfo['account_id'], $ilId, $beginTime,
                $endTime);
        } elseif ($status == RoomConstant::LIVE_STATUS_START) {
            $data = vss_service()->getStatService()->liveTerminal($this->accountInfo['account_id'], $ilId, $beginTime,
                $endTime);
        } elseif ($status == RoomConstant::LIVE_PLAY_BACK) {
            $data = vss_service()->getStatService()->recordTerminal($this->accountInfo['account_id'], $ilId, $beginTime,
                $endTime);
        }
        $this->success($data);
    }

    /**
     * 统计-地域分布列表
     *
     * @author ensong.liu@vhall.com
     * @date   2019-05-22 19:57:37
     */
    public function liveRegionAction()
    {
        $params = $this->getParam();
        $data   = vss_service()->getStatService()->liveRegion($params);
        $this->success($data);
    }

    /**
     * 异步导出列表
     *
     * @author  jin.yang@vhall.com
     * @date    2020-10-23
     */
    public function exportListAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'export'     => 'required',
            'status'     => '',
            'begin_time' => '',
            'end_time'   => '',
        ]);
        //参数列表
        $ilId      = $this->getParam('il_id');
        $accountId = $this->accountInfo['account_id'];
        $sourceId  = $this->getParam('source_id');
        $export    = $this->getParam('export', '');
        $beginTime = $this->getParam('begin_time', '');
        $endTime   = $this->getParam('end_time', '');
        $page      = $this->getParam('page', 1);
        $pageSize  = $this->getParam('page_size', 10);
        $status    = $this->getParam('status') ? $this->getParam('status') : '';

        $list = vss_service()->getExportService()->getList($ilId, $accountId, $sourceId, $export, $status, $beginTime,
            $endTime, $page, $pageSize);
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
        //参数列表
        $ilId      = $this->getParam('il_id');
        $beginTime = $this->getParam('begin_time', '2020-01-01');
        $endTime   = $this->getParam('end_time', date('Y-m-d'));
        $accountId = $this->accountInfo['account_id'];
        $status    = $this->getParam('status', 1);

        vss_service()->getStatService()->exportPV($ilId, $accountId, $beginTime, $endTime, $status);
        $this->success();
    }

    /**
     * 创建 uv 异步导出
     * @auther yaming.feng@vhall.com
     * @date 2021/2/26
     */
    public function exportUvAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'il_id'      => 'required',
            'begin_time' => 'required',
            'end_time'   => 'required',
            'status'     => '',
        ]);

        $ilId      = $this->getParam('il_id');
        $beginTime = $this->getParam('begin_time');
        $endTime   = $this->getParam('end_time');
        $status    = $this->getParam('status', 1);
        $accountId = $this->accountInfo['account_id'];

        vss_service()->getStatService()->createExportUv($ilId, $accountId, $beginTime, $endTime, $status);
        $this->success();
    }
}
