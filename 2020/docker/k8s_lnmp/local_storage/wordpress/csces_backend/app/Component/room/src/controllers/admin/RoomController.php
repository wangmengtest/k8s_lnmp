<?php

namespace App\Component\room\src\controllers\admin;

use vhallComponent\decouple\controllers\BaseController;

/**
 * RoomController extends BaseController
 *
 * @uses     yangjin
 * @date     2020-09-09
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class RoomController extends BaseController
{
    /**
     * 房间-信息
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-02-13 14:35:15
     * @method  GET
     * @request int $il_id  房间ID
     */
    public function getAction()
    {
        $params = $this->getParam();
        //参数列表
        vss_validator($params, [
            'il_id' => 'required',
        ]);

        $roomInfo = vss_service()->getRoomService()->getRow(
            ['il_id' => $params['il_id']],
            ['account']
        );

        //返回数据
        $this->success($roomInfo);
    }

    /**
     * 房间-列表
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:11:04
     * @method  GET
     * @request string  keyword     关键字搜索
     * @request string  begin_time  开始时间
     * @request string  end_time    结束时间
     * @request int     option_status  操作状态    nullable|in:-1, 0, 1, 2
     * @request int     status      直播状态
     * @request int     page        当前页码
     */
    public function listAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'keyword'       => '',
            'begin_time'    => '',
            'end_time'      => '',
            'option_status' => '',
            'page'          => '',
        ]);

        //参数列表
        $keyword   = $this->getParam('keyword');
        $beginTime = $this->getParam('begin_time');
        $endTime   = $this->getParam('end_time');
        $status    = $this->getParam('option_status', '');
        $page      = $this->getParam('page');

        //列表数据
        $condition = [
            'keyword'          => $keyword,
            'created_at_begin' => $beginTime,
            'created_at_end'   => $endTime,
            'status'           => $status,
        ];
        $with      = ['account:account_id,nickname'];

        $roomList = vss_model()->getRoomsModel()->getList($condition, $with, $page, ['rooms.*']);

        $this->success($roomList);
    }

    /**
     * 房间-导出
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:11:23
     * @method  GET
     * @request string  keyword     关键字搜索
     * @request string  begin_time  开始时间
     * @request string  end_time    结束时间
     * @request int     status      直播状态  nullable|in:"0", "1", "2", 0, 1, 2
     */
    public function exportListAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'keyword'    => '',
            'begin_time' => '',
            'end_time'   => '',
            'status'     => '',
        ]);

        //参数列表
        $keyword   = $this->getParam('keyword');
        $beginTime = $this->getParam('begin_time');
        $endTime   = $this->getParam('end_time');
        $status    = $this->getParam('status');

        vss_service()->getRoomService()->exportList($keyword, $beginTime, $endTime, $status);
    }

    /**
     * 房间-删除
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:11:57
     * @method  GET
     * @request int|string il_id   房间ID，多个用逗号隔开
     */
    public function deleteAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'il_ids' => 'required',
        ]);

        $data = vss_service()->getRoomService()->deleteByIds($params['il_ids']);
        //返回数据
        $this->success($data);
    }


    public function searchStatusAction()
    {
        $this->success();
    }

    /**
     * 审核操作：2--审核通过；3--审核驳回
     *
     * @method POST
     * @request int  il_id          房间id
     * @request int  audit_status   审核状态
     *
     * @return void
     *
     */
    public function auditAction()
    {
        //1、参数列表
        $ilId        = $this->getParam('il_id', 0);
        $auditStatus = $this->getParam('audit_status', 0);
        $data        = vss_service()->getRoomService()->audit($ilId, $auditStatus);

        $this->success($data);
    }

    /**
     * 获取需审核数据
     *
     */
    public function getAuditInfoAction()
    {
        $data = vss_service()->getRoomService()->getAuditInfo();

        $this->success($data);
    }
}
