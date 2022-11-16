<?php

namespace vhallComponent\vote\controllers\console;

use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;
use Illuminate\Support\Arr;
use vhallComponent\vote\constants\VoteConstant;

/**
 * Created by PhpStorm.
 * User: liuxiangliang
 * Date: 2020/10/27
 * Time: 15:12
 */
class VoteController extends BaseController
{
    /**
     * 投票-创建记录
     */
    public function createAction()
    {

        //1、接收参数信息
        $voteId = $this->getParam('vote_id', 0);
        $ilId   = $this->getParam('il_id', 0);
        if ($voteId == 0) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        if ($ilId) {
            $live = vss_model()->getRoomsModel()->getInfoByIlId($ilId);
        }
        $roomId = $live['room_id'] ?? '';

        //2、创建
        $params            = $this->getParam();
        $params['room_id'] = $roomId;
        $params['app_id']  = vss_service()->getTokenService()->getAppId();
        $voteInfo          = vss_service()->getVoteService()->create($params);

        //3、返回数据
        $this->success($voteInfo);
    }

    /**
     * 投票-删除记录
     */
    public function deleteAction()
    {

        //1、获取参数信息
        $voteIds = $this->getParam('vote_ids', 0);

        if (empty($voteIds)) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        //2、获取用户列表
        $deleteRes = [];    //删除房间信息
        $voteIdArr = explode(',', $voteIds);

        //查询删除投票中是否有已发布未结束投票
        $condition              = [];
        $condition['vote_ids']  = $voteIdArr;
        $condition['publish']   = VoteConstant::PUBLISH_YES;
        $condition['is_finish'] = VoteConstant::FINISH_NO;
        $count                  = vss_model()->getRoomVoteLkModel()->getCount($condition);
        if ($count) {
            $this->fail(ResponseCode::COMP_VOTE_TAKING_NOT_DEL);
        }
        if (!empty($voteIdArr)) {//2.1、删除多条记录
            foreach ($voteIdArr as $voteId) {
                $params = [
                    'vote_id'    => $voteId,
                    'account_id' => $this->accountInfo['account_id'],
                ];

                $result = vss_service()->getVoteService()->delete($params);
                if ($result) {
                    $deleteRes[] = $voteId;
                }
            }
        }

        //删除成功提示信息
        if (!empty($deleteRes)) {
            $this->success($deleteRes);
        }
        //删除失败提示信息
        $this->fail(ResponseCode::BUSINESS_DELETE_FAILED);
    }

    /**
     * 投票-修改记录
     */
    public function updateAction()
    {
        //1、接收参数信息
        $voteId = $this->getParam('vote_id', 0);
        $ilId   = $this->getParam('il_id', 0);

        if ($voteId == 0) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        $condition       = [
            'il_id'      => $ilId,
            'account_id' => $this->accountInfo['account_id'],
        ];
        $interactiveInfo = vss_model()->getRoomsModel()->getRow($condition);
        $room_id         = $interactiveInfo['room_id'] ?? '';

        //3、组织投票信息
        $params            = $this->getParam();
        $params['room_id'] = $room_id;
        $data              = vss_service()->getVoteService()->update($params);
        $data['vote_id']   = $voteId;
        $this->success($data);
    }

    /**
     * 投票-获取列表
     */
    public function listAction()
    {
        //1、参数列表
        $keyword  = $this->getParam('keyword');
        $page     = $this->getParam('page');
        $pageSize = $this->getParam('pagesize', 10);
        $ilId     = $this->getParam('il_id', 0);
        //1.1、组织结构信息
        $data = [
            'keyword'    => $keyword,
            'page'       => $page,
            'account_id' => $this->accountInfo['account_id'],
        ];
        //分页显示条数
        if ($pageSize > 0 && $pageSize <= 1000) {
            $data['pagesize'] = $pageSize;
        }
        //1.2、判断是否有对应的活动信息
        if (!empty($ilId)) {
            $condition       = [
                'il_id'      => $ilId,
                'account_id' => $this->accountInfo['account_id'],
            ];
            $interactiveInfo = vss_model()->getRoomsModel()->getRow($condition);
            if (empty($interactiveInfo)) {
                $this->fail(ResponseCode::EMPTY_ROOM);
            }
            $data['room_id'] = $interactiveInfo['room_id'];
            unset($data['account_id']);
        }

        //2、获取投票数据
        $result   = vss_service()->getVoteService()->list($data);
        $voteList = $result;
        $this->success($voteList);
    }

    /**
     * 投票-获取房间下投票信息
     */
    public function infoAction()
    {
        $voteId = $this->getParam('vote_id');
        $roomId = $this->getParam('room_id');
        if (!$roomId) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }

        $live = vss_model()->getRoomsModel()->findByRoomId($roomId);
        if (empty($live)) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }
        $roomId = $live['room_id'] ?? '';
        //2、组织数据
        $data = [
            'vote_id' => $voteId,
            'room_id' => $roomId
        ];
        //2.1、查看投票信息
        $data = vss_service()->getVoteService()->info($data);
        $this->success($data);
    }

    /**
     * 投票参与人数统计
     * @auther yaming.feng@vhall.com
     * @date 2021/1/5
     */
    public function statAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'room_id' => 'required',
        ]);

        $roomId   = $this->getParam('room_id');
        $page     = $this->getParam('page', 1);
        $pageSize = $this->getParam('page_size', 10);

        $result = vss_service()->getVoteService()->statByRoomId($roomId, $page, $pageSize);
        $this->success($result);
    }

    /**
     * 投票导出
     * @auther yaming.feng@vhall.com
     * @date 2021/1/5
     *
     * @param int $id room_vote_lk 表的 ID
     */
    public function exportAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'id' => 'required',
        ]);

        $voteInfo = vss_model()->getRoomVoteLkModel()->getRoomVoteLkInfo([
            'id' => $this->getParam('id')
        ], ['id', 'vote_id', 'room_id']);

        if (!$voteInfo) {
            $this->fail(ResponseCode::COMP_VOTE_INVALID);
        }

        $voteId    = $voteInfo['vote_id'];
        $accountId = $this->accountInfo['account_id'];

        vss_service()->getVoteService()->createExport($voteInfo['room_id'], $accountId, $voteId);
        $this->success();
    }

    /**
     * 获取投票统计详情
     * @auther yaming.feng@vhall.com
     * @date 2021/2/23
     *
     */
    public function voteDetailAction()
    {
        $params = $this->getParam();
        Arr::forget($params, 'account_id');
        $result = vss_service()->getVoteService()->voteDetail($params);
        $this->success($result);
    }
}
