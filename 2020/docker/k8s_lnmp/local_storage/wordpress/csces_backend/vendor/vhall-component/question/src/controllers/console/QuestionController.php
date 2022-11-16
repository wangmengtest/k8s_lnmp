<?php

namespace vhallComponent\question\controllers\console;

use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;

class QuestionController extends BaseController
{
    /**
     * 获取accessToken
     * 三种用户权限,对应三种权限的token
     * data_collect_manage 在使用数据收集服务SDK 允许管理问卷 时传入此参数，参数值目前只支持传入all
     * data_collect_submit 在使用数据收集服务SDK 允许提交问卷答卷 时传入此参数，参数值目前只支持传入all
     * data_collect_view 在使用数据收集服务SDK 允许浏览问卷信息 时传入此参数，参数值目前只支持传入all
     *
     * @author ensong.liu@vhall.com
     * @date 2019-05-10 13:26:25
     */
    public function getAccessTokenAction()
    {
        $permissions = [
            ['data_collect_manage' => 'all'],
            ['data_collect_submit' => 'all'],
            ['data_collect_view' => 'all'],

        ];

        $permission                        = $permissions[0];
        $thirdPartUserId                   = $this->accountInfo['account_id'];
        $permission['third_party_user_id'] = $thirdPartUserId;
        $accessToken                       = vss_service()->getPaasService()->baseCreateAccessToken($permission);

        $this->success(['accessToken' => $accessToken ?? '']);
    }

    /**
     * 问卷-创建记录
     *
     * @author ensong.liu@vhall.com
     * @date 2019-05-21 17:04:42
     */
    public function createAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'q_id'  => 'required',
            'il_id' => 'required',
            'title' => 'required',
        ]);

        //1、接收参数信息
        $title       = $this->getParam('title');
        $description = $this->getParam('description');
        $imgUrl      = $this->getParam('img_url');
        $qId         = $this->getParam('q_id');
        $ilId        = $this->getParam('il_id');

        $live   = vss_model()->getRoomsModel()->getInfoByIlId($ilId);
        if (!$live) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }
        //1.1、同步至vss
        $postArr = [
            'question_id' => $qId,
            'title'       => $title,
            'description' => $description,
            'extend'      => '',
            'account_id'  => $this->accountInfo['account_id'],
            'cover'       => $imgUrl,
            'app_id'      => vss_service()->getTokenService()->getAppId(),
            'room_id'     => $live['room_id'],
        ];

        $questionInfo = vss_service()->getQuestionService()->create($postArr);
        if (!$questionInfo) {
            $this->fail(ResponseCode::BUSINESS_CREATE_FAILED);
        }

        //2、组织数据
        $data = [
            'question_id' => $questionInfo['question_id'],
            'account_id'  => $this->accountInfo['account_id'],
        ];
        //2.1、查看问卷信息
        $result = vss_service()->getQuestionService()->info($data);

        //3、返回数据
        if (!empty($result)) {
            $result['q_id'] = $result['question_id'];
            $this->success($result);
        } else {
            $this->fail(ResponseCode::BUSINESS_CREATE_FAILED);
        }
    }

    /**
     * 问卷-删除记录
     *
     * @author ensong.liu@vhall.com
     * @date 2019-05-21 17:04:42
     */
    public function deleteAction()
    {

        //1、获取参数信息
        $questionId = $this->getParam('question_ids', 0);
        if ($questionId == 0) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        //2、获取用户列表
        $deleteRes            = [];    //删除房间信息
        $questionIdArr        = explode(',', $questionId);
        $params               = [];
        $params['account_id'] = $this->accountInfo['account_id'];
        foreach ($questionIdArr as $quesId) {
            $params['question_id'] = $quesId;
            $ok                    = vss_service()->getQuestionService()->delete($params);
            if ($ok) {
                $deleteRes[] = $quesId;
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
     * 问卷-修改记录
     *
     * @author ensong.liu@vhall.com
     * @date 2019-05-21 17:04:42
     */
    public function updateAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'question_id' => 'required',
            'il_id'       => 'required',
            'title'       => 'required',
        ]);

        //1、接收参数信息
        $questionId  = $this->getParam('question_id');
        $description = $this->getParam('description', '');
        $title       = $this->getParam('title');
        $cover       = $this->getParam('cover', '');
        $extend      = $this->getParam('extend', '');
        $ilId        = $this->getParam('il_id');

        $room = vss_model()->getRoomsModel()->getInfoByIlId($ilId);
        $room->account_id != $this->accountInfo['account_id'] && $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);

        //2、组织参数信息
        $accountId = $this->accountInfo['account_id'];
        $data      = [
            'question_id' => $questionId,
            'account_id'  => $accountId
        ];

        //2.1、查看问卷信息
        $questionInfo = vss_service()->getQuestionService()->info($data);
        //2.1、判断问卷是否存在
        if (empty($questionInfo)) {
            $this->fail(ResponseCode::EMPTY_QUESTION);
        }

        $params = [
            'question_id' => $questionId,
            'title'       => $title,
            'description' => $description,
            'extend'      => $extend,
            'account_id'  => $accountId,
            'cover'       => $cover,
            'app_id'      => vss_service()->getTokenService()->getAppId(),
            'room_id'     => $room->room_id,
        ];
        //3、组织问卷信息
        vss_service()->getQuestionService()->update($params);
        $result['q_id'] = $questionId;
        $this->success($result);
    }

    /**
     * 问卷-获取记录
     *
     * @author ensong.liu@vhall.com
     * @date 2019-05-21 17:04:42
     */
    public function getAction()
    {
        //参数列表
        $questionId = $this->getParam('question_id');

        //问卷信息
        $condition    = [
            'question_id' => $questionId,
            'account_id'  => $this->accountInfo['account_id'],
        ];
        $with         = [];
        $questionInfo = vss_model()->getQuestionsModel()->getRow($condition, $with);
        if (empty($questionInfo)) {
            $this->fail(ResponseCode::EMPTY_QUESTION);
        }

        //返回数据
        $this->success($questionInfo);
    }

    /**
     * 问卷-获取列表
     *
     * @author ensong.liu@vhall.com
     * @date 2019-05-21 17:04:42
     */
    public function listAction()
    {

        //1、参数列表
        $keyword  = $this->getParam('keyword');
        $page     = $this->getParam('page');
        $pageSize = $this->getParam('pagesize', 0);
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

        //2、获取问卷数据
        $list = vss_service()->getQuestionService()->list($data);
        if (!empty($list['data'])) {
            foreach ($list['data'] as $key => &$datum) {
                $list['data'][$key]['q_id'] = $datum['question_id'];
                $condition                  = ['room_id' => $datum['room_id'], 'account_id' => $this->accountInfo['account_id']];
                $liveList                   = $interactiveInfo = vss_model()->getRoomsModel()->getRow($condition);
                $datum['il_id']             = $liveList ? $liveList['il_id'] : '';
            }
            $questionList = $list;
        } else {
            $questionList = [];
        }
        $this->success($questionList);
    }

    /**
     * 统计-问卷使用记录
     */
    public function getQuestionLogAction()
    {
        //1、获取参数信息
        $ilId      = $this->getParam('il_id');
        $beginTime = $this->getParam('begin_time');
        $endTime   = $this->getParam('end_time');
        $page      = $this->getParam('page', 1);
        $perPage   = $this->getParam('per_page', 10);
        $accountId = $this->accountInfo['account_id'];

        $return = vss_service()->getQuestionService()->getQuestionLog($accountId, $ilId, $beginTime, $endTime, $page, $perPage);

        if ($return['data']) {
            // 查询问卷是否发布
            $questionIds = array_column($return['data'], 'question_id');
            $publishStatusMap = vss_model()->getRoomQuestionLkModel()->getQuestionPublishStatus($questionIds);

            array_walk($return['data'], function (&$item) use ($publishStatusMap) {
                $item['publish'] = $publishStatusMap[$item['question_id']] ?? 0;
            });
        }

        $this->success($return);
    }

    /**
     * 统计-问卷使用记录
     *
     * @author ensong.liu@vhall.com
     * @date   2019-05-22 19:26:24
     */
    public function questionLogAction()
    {
        //参数列表
        $ilId      = $this->getParam('il_id');
        $beginTime = $this->getParam('begin_time');
        $endTime   = $this->getParam('end_time');

        $data = vss_service()->getQuestionService()->questionLog($ilId, $beginTime, $endTime);
        $this->success($data);
    }

    /**
     * 导出单个问卷及答案列表
     */
    public function exportQuestionAnswerAction()
    {
        $params    = $this->getParam();
        vss_validator($params, [
            'il_id'       => 'required',
            'question_id' => 'required',
        ]);
        //参数列表
        $ilId       = $params['il_id'];
        $questionId = $params['question_id'];
        $accountId  = $this->accountInfo['account_id'];
        $username   = $this->accountInfo['username'];
        $fileName   = "问卷_{$questionId}_{$username}";

        // 房间数据下的导出，文件名需要追加时间
        $beginTime = $this->getParam('begin_time');
        $endTime   = $this->getParam('end_time');
        if ($beginTime && $endTime) {
            $beginTime = str_replace('-', '', $beginTime);
            $endTime   = str_replace('-', '', $endTime);
            $fileName  .= "_{$beginTime}_{$endTime}";
        }

        vss_service()->getQuestionService()->exportQuestionAnswer($ilId, $accountId, $questionId, $fileName);
        $this->success();
    }

    /**
     * 未登录用户提交答案
     * @auther yaming.feng@vhall.com
     * @date 2021/1/20
     *
     */
    public function submitAnswerAction()
    {
        vss_service()->getQuestionService()->answerNotLogin($this->getParam());
        $this->success();
    }

    /**
     * 问卷关联房间列表
     * @auther yaming.feng@vhall.com
     * @date 2021/1/22
     */
    public function linkRoomListAction()
    {
        $params               = $this->getParam();
        $params['account_id'] = $this->accountInfo['account_id'];

        $list = vss_service()->getQuestionService()->linkRoomList($params);
        $this->success($list);
    }

    /**
     * 发布问卷 主播端
     */
    public function publishAction()
    {
        vss_service()->getQuestionService()->publish($this->getParam(), $this->accountInfo);
        $this->success();
    }

    /**
     * 批量解除问卷和房间的绑定关系
     * @auther yaming.feng@vhall.com
     * @date 2021/3/5
     */
    public function batchUnbindRoomAction()
    {
        $questionIds = $this->getParam('question_ids');
        $questionIds = explode(',', trim($questionIds));
        $accountId = $this->accountInfo['account_id'];

        $delCount = vss_service()->getQuestionService()->batchUnbindRoom($questionIds, $accountId);
        if (!$delCount) {
            $this->fail(ResponseCode::BUSINESS_DELETE_FAILED);
        }
        $this->success();
    }
}
