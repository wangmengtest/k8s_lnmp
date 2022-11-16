<?php

namespace vhallComponent\question\services;

use App\Constants\ResponseCode;
use Illuminate\Support\Arr;
use vhallComponent\question\constants\QuestionConstants;
use vhallComponent\question\models\QuestionsModel;
use Vss\Common\Services\WebBaseService;
use vhallComponent\question\jobs\SubmitQuestionJob;

class QuestionService extends WebBaseService
{
    /**
     * 未登录用户提交问卷
     * @auther yaming.feng@vhall.com
     * @date 2021/1/20
     *
     * @param $params
     *
     * @return bool
     *
     */
    public function answerNotLogin($params)
    {
        vss_validator($params, [
            'room_id'     => 'required',
            'device_id'   => 'required',
            'extend'      => '',
            'token'       => '',
            'question_id' => 'required',
            'answer_id'   => 'required',
        ]);

        $roomId     = $params['room_id'];
        $questionId = $params['question_id'];
        $deviceId   = $params['device_id'];

        $redisKey = implode(':', [QuestionConstants::ROOM_QUESTION_ANSWERED_USER_ID, $roomId, $questionId, $deviceId]);
        if (vss_redis()->lock($redisKey, 86400)) {
            $this->fail(ResponseCode::BUSINESS_REPEAT_SUBMIT);
        }

        // 问卷及房间验证
        $question = $this->getQuestion($questionId, $roomId);
        !$question && $this->fail(ResponseCode::EMPTY_QUESTION);

        $params['join_id']    = 0;
        $params['account_id'] = 0;
        $params['extend']     = '{}';

        // 如果用户登录， 则校验用户信息
        vss_service()->getTokenService()->checkToken($params['token']);
        $accountId = vss_service()->getTokenService()->getAccountId();
        if ($accountId) {
            $redisKey = implode(':',
                [QuestionConstants::ROOM_QUESTION_ANSWERED_USER_ID, $roomId, $questionId, $accountId]);
            if (vss_redis()->lock($redisKey, 86400)) {
                $this->fail(ResponseCode::BUSINESS_REPEAT_SUBMIT);
            }

            $joinUser = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId(
                $accountId,
                $roomId
            );

            if ($joinUser) {
                $params['join_id']    = $joinUser->join_id;
                $params['account_id'] = $accountId;
            }
        }

        // 记录房间 ID, 用于队列消费
        vss_queue()->push(new SubmitQuestionJob($params));
        return true;
    }

    /**
     * 提交答案
     *
     * @param $params
     *
     *
     */
    public function answer($params)
    {
        vss_validator($params, [
            'room_id'     => 'required',
            'extend'      => '',
            'question_id' => 'required',
            'answer_id'   => 'required',
        ]);

        // 判断是否提交过
        $roomId     = $params['room_id'];
        $questionId = $params['question_id'];
        $accountId  = vss_service()->getTokenService()->getAccountId();
        $redisKey   = implode(':',
            [QuestionConstants::ROOM_QUESTION_ANSWERED_USER_ID, $roomId, $questionId, $accountId]);
        if (vss_redis()->lock($redisKey, 86400)) {
            $this->fail(ResponseCode::BUSINESS_REPEAT_SUBMIT);
        }

        // 房间用户 fixme 为什么不用account_id
        $joinUser = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId(
            $accountId,
            $roomId
        );
        empty($joinUser) && $this->fail(ResponseCode::AUTH_NOT_PERMISSION);

        $params['join_id']    = $joinUser->join_id;
        $params['account_id'] = $accountId;
        vss_redis()->sadd(
            QuestionConstants::ROOM_QUESTION_ANSWERED_USER_ID . $roomId . ':' . $questionId,
            $accountId,
        );

        vss_queue()->push(new SubmitQuestionJob($params));
        return true;
    }

    /**
     * 队列消费提交
     *
     * @param $params
     *
     * @author  jin.yang@vhall.com
     * @date    2020-11-05
     */
    public function queueAnswer($params)
    {
        $rule = [
            'room_id'     => 'required',
            'extend'      => 'required',
            'question_id' => 'required',
            'answer_id'   => 'required',
            'join_id'     => 'required',
            'account_id'  => '',
        ];
        $data = vss_validator($params, $rule);

        vss_model()->getQuestionAnswersModel()->create($data);

        // 未登录用户 join_id 为 0
        $joinId = $params['join_id'];
        $joinId && vss_model()->getRoomJoinsModel()->updateRow($joinId, ['is_answered_questionnaire' => 1]);

        return true;
    }

    /**
     *
     * @param $params
     *
     * @return bool
     *
     */
    public function checkSurvey($params)
    {
        vss_validator($params, [
            'room_id'     => 'required',
            'question_id' => 'required',
        ]);
        $join_user = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId(
            vss_service()->getTokenService()->getAccountId(),
            $params['room_id']
        );
        $data      = vss_model()->getQuestionAnswersModel()->where([
            'question_id' => $params['question_id'],
            'room_id'     => $params['room_id'],
            'join_id'     => $join_user->join_id,
        ])->first();
        if (empty($data)) {
            return false;
        }
        return true;
    }

    /**
     * @param $params
     *
     * @return mixed
     *
     */
    public function list($params)
    {
        vss_validator($params, [
            'room_id'      => ['required_without:account_id', 'required_if:type,2'],
            'account_id'   => ['required_without:room_id'],
            'keyword'      => '',
            'publish'      => '',
            'is_public'    => '',
            'from_room_id' => '',
            'page'         => '',
            'pagesize'     => '',
            'begin_time'   => '',
            'end_time'     => '',
            'is_finish'    => '',
            'type'         => '',
        ]);
        $page     = $params['page'] ?? 1;
        $pagesize = $params['pagesize'] ?? 20;
        $query    = vss_model()->getQuestionsModel()->newQuery()
            ->leftJoin('room_question_lk', 'questions.question_id', 'room_question_lk.question_id');
        if ($params['room_id']) {
            if ($params['type'] == 2) { //排除某个房间绑定
                $question_ids = vss_model()->getRoomQuestionLkModel()->where([
                    'room_id' => $params['room_id'],
                    'bind'    => 1,
                ])->pluck('question_id');
                $query->whereNotIn('questions.question_id', $question_ids);
            } elseif (!empty($params['publish'])) { //获取发布中的问卷
                $query->where('room_question_lk.room_id', $params['room_id'])
                    ->where('room_question_lk.publish', $params['publish']);
//                    ->where('room_question_lk.finish_time', '>=', date('Y-m-d H:i:s'));
            } else {
                $query->where('room_question_lk.room_id', $params['room_id'])
                    ->where('bind', 1);
            }
        }
        // 时间筛选
        if ($params['begin_time']) {
            $begin_time = $params['begin_time'];
            $end_time   = !empty($params['end_time']) ? $params['end_time'] : date('Y-m-d H:i:s');
            $query->whereBetween('questions.created_at', [$begin_time, $end_time]);
        }
        if ($params['account_id']) {
            $query->where('questions.account_id', $params['account_id']);
        }
        if (!is_null($params['is_public'])) {
            $query->where('questions.is_public', $params['is_public']);
        }

        $keyword = $params['keyword'];
        if (!empty($keyword)) {
            $query->where(function ($query) use ($keyword) {
                $val = "%{$keyword}%";
                $query->where('questions.title', 'like', $val);
                $query->orWhere('questions.question_id', 'like', $val);
            });
        }

        $query->leftJoin('rooms', 'rooms.room_id', 'room_question_lk.room_id');
        $list = $query->selectRaw('questions.*,room_question_lk.updated_at as lk_update_time,room_question_lk.publish,rooms.subject as room_subject,room_question_lk.room_id')
            ->groupBy('questions.question_id')
            ->orderBy('questions.updated_at', 'desc')
            ->paginate($pagesize, ['questions.*'], 'page', $page);
        $list = json_decode(json_encode($list, JSON_UNESCAPED_UNICODE), true);

        if ($params['from_room_id']) {
            if (!empty($list['data'])) {
                $lk_list = vss_model()->getRoomQuestionLkModel()->where(
                    'room_id',
                    $params['from_room_id']
                )->whereIn(
                    'question_id',
                    array_column($list['data'], 'question_id')
                )->get()->toArray();

                $map = [];
                foreach ($lk_list as $lk) {
                    $map[$lk['question_id']] = $lk;
                }
                $list['data'] = array_map(function ($v) use ($map, $params) {
                    $redisKey     = implode(':', [
                        QuestionConstants::ROOM_QUESTION_ANSWERED_USER_ID,
                        $params['room_id'],
                        $v['question_id'],
                        vss_service()->getTokenService()->getAccountId(),
                    ]);
                    $v['publish'] = empty($map[$v['question_id']]) ? 0 : $map[$v['question_id']]['publish'];
                    $v['bind']    = empty($map[$v['question_id']]) ? 0 : $map[$v['question_id']]['bind'];
                    $v['answer']  = empty($answerMap[$v['question_id']]) && !(vss_redis()->exists($redisKey)) ? 0 : 1;
                    return $v;
                }, $list['data']);
            }
        }

        // 查询问卷的填写人数
        if ($list['data']) {
            $questionIds    = array_column($list['data'], 'question_id');
            $roomId         = $params['room_id'];
            $answerCountMap = vss_model()->getQuestionAnswersModel()->getAnswerCountByQuestionIds($questionIds,
                $roomId);
            $accountId      = vss_service()->getTokenService()->getAccountId();

            // 查询当前用户是否填写问卷
            $joinQuestionIds = vss_model()->getQuestionAnswersModel()
                ->where('account_id', $accountId)
                ->whereIn('question_id', $questionIds)
                ->pluck('answer_id', 'question_id')
                ->toArray();

            foreach ($list['data'] as &$item) {
                $item['answerer_count'] = Arr::get($answerCountMap, $item['question_id'], 0);
                $item['is_fill']        = isset($joinQuestionIds[$item['question_id']]) ? 1 : 0;
                $item['answer_id']      = $joinQuestionIds[$item['question_id']] ?? 0;
            }
        }

        return $list;
    }

    /**
     * @param $params
     *
     * @return mixed
     *
     */
    public function create($params)
    {
        $rule = [
            'room_id'     => '',
            'title'       => 'required',
            'description' => '',
            'extend'      => '',
            'question_id' => 'required',
            'account_id'  => 'required',
            'is_public'   => '',
            'source_id'   => '',
            'app_id'      => 'required',
        ];
        $data = vss_validator($params, $rule);

        if ($params['room_id']) {
            $room = vss_model()->getRoomsModel()->findByRoomId($params['room_id']);
            ($params['account_id'] != $room->account_id) && $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        unset($data['room_id']);
        $question = vss_model()->getQuestionsModel()->updateOrCreate(['question_id' => $params['question_id']], $data);
        $params['room_id'] && $this->bindRoom([
            'question_id' => $question->question_id,
            'room_id'     => $params['room_id'],
        ]);
        return $question;
    }

    /**
     * @param $params
     *
     *
     */
    public function update($params)
    {
        $rule = [
            'title'       => '',
            'description' => '',
            'extend'      => '',
            'question_id' => 'required',
            'account_id'  => 'required',
            'room_id'     => '',
        ];
        $data = vss_validator($params, $rule);

        $roomId = $params['room_id'];

        $question = $this->getQuestion(
            $params['question_id'],
            $roomId,
            $params['account_id']
        );
        if (!$question) {
            $this->fail(ResponseCode::EMPTY_QUESTION);
        }

        // 已发布的问卷不能编辑
        $isPublish = vss_model()->getRoomQuestionLkModel()->questionIsPublish($question->question_id);
        if ($isPublish) {
            $this->fail(ResponseCode::COMP_QUESTION_NOT_EDIT);
        }
        unset($data['question_id'], $data['room_id']);
        $question->update($data + ['updated_at' => date('Y-m-d H:i:s')]);
        $roomId && $this->updateBindRoom([
            'question_id' => $question->question_id,
            'room_id'     => $roomId,
        ]);
    }

    /**
     * @param $params
     *
     *
     */
    public function delete($params)
    {
        vss_validator($params, [
            'question_id' => 'required',
            'account_id'  => '', //required
        ]);
        $question = $this->getQuestion($params['question_id'], null, $params['account_id']);
        if ($question) {
            // 检查问卷是否发布，已发布的问卷不能删除
            $isPublish = vss_model()->getRoomQuestionLkModel()->questionIsPublish($question->question_id);

            if (!$isPublish) {
                return $question->delete();
            }
        }

        return false;
    }

    /**
     * @param $params
     *
     *
     */
    public function bindRoom($params)
    {
        $rule     = [
            'question_id' => 'required',
            'room_id'     => 'required',
            'finish_time' => '',
        ];
        $data     = vss_validator($params, $rule);
        $question = $this->getQuestion($params['question_id']);
        $room     = vss_model()->getRoomsModel()->findByRoomId($params['room_id']);
        $room->account_id != $question->account_id && $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        $lk = vss_model()->getRoomQuestionLkModel()->findByRoomIdAndQuestionId(
            $params['room_id'],
            $params['question_id']
        );
        if (empty($lk)) {
            vss_model()->getRoomQuestionLkModel()->create($data + ['bind' => 1]);
        } else {
            $lk->update(['bind' => 1]);
        }
    }

    /**
     * @param $params
     *
     *
     */
    public function updateBindRoom($params)
    {
        vss_validator($params, [
            'question_id' => 'required',
            'room_id'     => 'required',
            'updated_at'  => '',
        ]);
        $question = $this->getQuestion($params['question_id']);
        $room     = vss_model()->getRoomsModel()->findByRoomId($params['room_id']);
        $room->account_id != $question->account_id && $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        $oldRoomId = vss_model()->getRoomQuestionLkModel()->where(
            'question_id',
            $params['question_id']
        )->value('room_id');
        $lk        = vss_model()->getRoomQuestionLkModel()->findByRoomIdAndQuestionId(
            $oldRoomId,
            $params['question_id']
        );
        if (empty($lk)) {
            $this->bindRoom($params);
            return;
        }
        $lk->update(['room_id' => $params['room_id'], 'bind' => 1]);
        $lk->deleteCache('InfoByRoomIdAndQuestionId', $oldRoomId . 'and' . $params['question_id']);
    }

    /**
     * @param $params
     *
     *
     */
    public function unbindRoom($params)
    {
        vss_validator($params, [
            'question_id' => 'required',
            'room_id'     => 'required',
        ]);
        $this->getQuestion($params['question_id'], $params['room_id']);
        $lk = vss_model()->getRoomQuestionLkModel()->findByRoomIdAndQuestionId(
            $params['room_id'],
            $params['question_id']
        );

        if (!$lk) {
            $this->fail(ResponseCode::EMPTY_QUESTION);
        }

        // 已发布的问卷不能解绑
        if ($lk->publish == 1) {
            $this->fail(ResponseCode::COMP_QUESTION_NOT_DELETE);
        }

        $lk->forceDelete();
    }

    /**
     * 批量解除问卷和房间的绑定关系
     * @auther yaming.feng@vhall.com
     * @date 2021/3/5
     *
     * @param array $questionIds
     * @param int   $accountId
     *
     */
    public function batchUnbindRoom($questionIds, $accountId)
    {
        $questionIds = (array)$questionIds;
        if (!$questionIds) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }

        // 查询问卷 ID 是否是属于当前用户
        $res = vss_model()->getQuestionsModel()->whereIn('question_id', $questionIds)
            ->where('account_id', '!=', $accountId)
            ->count();
        if ($res) {
            $this->fail(ResponseCode::EMPTY_QUESTION);
        }

        // 删除问卷和当前房间的绑定关系, 只操作已经未发布的问卷
        return vss_model()->getRoomQuestionLkModel()->newQuery()
            ->whereIn('question_id', $questionIds)
            ->where('publish', 0)
            ->forceDelete();
    }

    /**
     * 关联房间查询
     * @auther yaming.feng@vhall.com
     * @date 2021/1/22
     *
     * @param $params
     *
     * @return mixed
     *
     */
    public function linkRoomList($params)
    {
        vss_validator($params, [
            'keyword'     => '',
            'account_id'  => 'required',
            'question_id' => 'integer|min:1',
            'page'        => 'integer|min:1',
            'pagesize'    => 'integer|min:1',
        ]);

        $page       = $params['page'] ?? 1;
        $pageSize   = $params['pagesize'] ?? 10;
        $questionId = $params['question_id'] ?? 0;
        $accountId  = $params['account_id'];
        $keyword    = $params['keyword'];

        $list = vss_model()->getRoomQuestionLkModel()->getLinkRoomList(
            $page, $pageSize, $accountId, $questionId, $keyword
        );

        // 存在 question_id 的即为关联房间
        array_walk($list['data'], function (&$item) {
            $item['is_link_room'] = $item['question_id'] ? 1 : 0;
        });

        return $list;
    }

    /**
     * 问卷-发布问卷(主播)
     *
     * @param       $params
     * @param array $adminInfo 管理员信息，从控制台发布时，传入
     *
     */
    public function publish($params, $adminInfo = [])
    {
        vss_validator($params, [
            'question_id' => 'required',
            'room_id'     => 'required',
        ]);

        $questionId = $params['question_id'];
        $roomId     = $params['room_id'];
        $question   = $this->getQuestion($questionId, $roomId);
        empty($question) && $this->fail(ResponseCode::EMPTY_QUESTION);

        if ($adminInfo) {
            $adminInfo['role_name'] = 1;
            $join_user              = json_decode(json_encode($adminInfo));
        } else {
            $join_user = vss_service()->getTokenService()->getCurrentJoinUser($roomId);
            $join_user->role_name == 2 && $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }

        $lk = vss_model()->getRoomQuestionLkModel()->findByRoomIdAndQuestionId($roomId, $questionId);
        if (!$lk) {
            $this->fail(ResponseCode::COMP_QUESTION_ROOM_ALREADY_UNBIND);
        }

        // 绑定account_id
        $res = vss_model()->getRoomsModel()->findByRoomId($roomId);
        if (empty($res)) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }
        // 发布问卷，问卷信息本地静态化
        $ossUrl = vss_service()->getFormService()->writeInfoLocal($question->question_id, 'question');

        $lk->update(['publish' => 1, 'account_id' => $res['account_id']]);

        vss_service()->getPaasChannelService()->sendMessage($roomId, [
            'type'             => 'questionnaire_push',
            'questionnaire_id' => $questionId,
            'nick_name'        => $join_user->nickname,
            'account_id'       => $join_user->account_id,
            'room_role'        => $join_user->role_name,
            'info_url'         => $ossUrl,
        ]);

        //发问卷发送公告信息
        vss_service()->getPaasChannelService()->sendNotice(
            $roomId,
            $questionId,
            $join_user->account_id,
            'questionnaire'
        );
        //调查问卷上报(向单个用户推送)
        vss_service()->getBigDataService()->requestQuestionPushParams($params);

        // 记录该房间发布过问卷
        vss_redis()->hset(\vhallComponent\room\constants\CachePrefixConstant::INTERACT_TOOL_RECORDS . $roomId,
            'is_question', 1);
    }

    /**
     * @param $params
     *
     *
     */
    public function cancelPublish($params)
    {
        vss_validator($params, [
            'question_id' => 'required',
            'room_id'     => 'required',
        ]);
        $question = $this->getQuestion($params['question_id'], $params['room_id']);
        empty($question) && $this->fail(ResponseCode::EMPTY_QUESTION);
        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $join_user->role_name == 2 && $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        $lk = vss_model()->getRoomQuestionLkModel()->findByRoomIdAndQuestionId(
            $params['room_id'],
            $params['question_id']
        );
        $lk && $lk->update(['publish' => 0]);
    }

    /**
     * @param      $question_id
     * @param null $room_id
     * @param null $account_id
     *
     * @return QuestionsModel
     *
     */
    public function getQuestion($question_id, $room_id = null, $account_id = null)
    {
        $question = vss_model()->getQuestionsModel()->findByQuestionId($question_id);
        empty($question) && $this->fail(ResponseCode::EMPTY_QUESTION);
        if ($room_id) {
            $room = vss_model()->getRoomsModel()->findByRoomId($room_id);
            empty($room) && $this->fail(ResponseCode::EMPTY_QUESTION);
            ($room->account_id != $question->account_id) && $this->fail(ResponseCode::EMPTY_QUESTION);
        }
        return $question;
    }

    /**
     * @param $params
     *
     * @return QuestionsModel
     *
     */
    public function info($params)
    {
        vss_validator($params, [
            'question_id' => 'required_without:source_id|integer',
            'source_id'   => 'required_without:question_id|integer',
            'account_id'  => 'required',
        ]);
        if ($params['question_id']) {
            $question = vss_model()->getQuestionsModel()->findByQuestionId($params['question_id']);
        } else {
            $question = vss_model()->getQuestionsModel()->findBySourceId($params['source_id']);
        }
        !$question && $this->fail(ResponseCode::EMPTY_QUESTION);
        $question->account_id != $params['account_id'] && $this->fail(ResponseCode::EMPTY_QUESTION);
        return $question;
    }

    /**
     * @param $params
     *
     * @return mixed
     *
     */
    public function statisticsList($params)
    {
        vss_validator($params, [
            'room_id'    => 'required',
            'account_id' => 'required',
            'keyword'    => '',
            'page'       => '',
            'pagesize'   => '',
        ]);
        $page     = $params['page'] ?? 1;
        $pagesize = $params['pagesize'] ?? 20;
        $query    = vss_model()->getQuestionAnswersModel()->newQuery()
            ->leftJoin('questions', 'question_answers.question_id', 'questions.question_id')
            ->where('question_answers.room_id', $params['room_id'])
            ->where('questions.account_id', $params['account_id'])
            ->whereNull('questions.deleted_at');

        if (!is_null($params['keyword'])) {
            $query->where('questions.title', 'like', '%' . $params['keyword'] . '%');
        }
        if (!empty($params['begin_date']) && !empty($params['end_date'])) {
            $query->where('question_answers.created_at', '>=', "{$params['begin_date']}")
                ->where('question_answers.created_at', '<=', "{$params['end_date']} 23:59:59");
        }
        $total_query = clone $query;
        $total       = $total_query->distinct()->count('question_answers.question_id');
        $list        = $query->groupBy(['question_answers.question_id'])
            ->selectRaw('questions.*,count(1) answer_count,question_answers.room_id, question_answers.updated_at')
            ->orderBy('questions.updated_at', 'desc')
            ->skip(($page - 1) * $pagesize)
            ->limit($pagesize)
            ->get()->toArray();
        return compact('page', 'pagesize', 'total', 'list');
    }

    /**
     * 根据关联ID获取问卷数量
     *
     * @param $params
     *
     * @return int|mixed
     *
     */
    public function getQuestionNum($params)
    {
        $params = array_filter(array_map('trim', $params));
        if (!empty($params['room_id'])) {
            vss_validator($params, [
                'room_id' => 'required',
            ]);
        }
        if (!empty($params['account_id'])) {
            vss_validator($params, [
                'account_id' => '',
            ]);
        }
        // $res['by_account_num'] =vss_model()->getRoomQuestionLkModel()->where(['account_id' => $params['account_id'] ,'publish' =>1,['created_at','>=',"{$params['begin_date']}"],['created_at','<=',"{$params['end_date']} 23:59:59"]])->count();
        $res['by_room_num'] = $this->getQuestionNums($params);
        //  $res = vss_model()->getRoomQuestionLkModel()->getQuestionNum($params);
        $res['by_account_num'] = $this->getAnswerNum($params);

        return $res;
    }

    /**
     *
     * @param $params
     *
     * @return int
     *
     */
    public function getAnswerNum($params)
    {
        vss_validator($params, [
            'room_id'    => '',
            'account_id' => '',
        ]);
        $query = vss_model()->getQuestionAnswersModel()->newQuery();

        $query->leftJoin('questions', 'question_answers.question_id', 'questions.question_id');
        if (!empty($params['room_id'])) {
            $query->where('question_answers.room_id', $params['room_id']);
        }
        if (!empty($params['account_id'])) {
            $query->where('questions.account_id', $params['account_id']);
        }
        $query->whereNull('question_answers.deleted_at');
        // $query ->groupBy('question_answers.question_id');

        if (!empty($params['begin_date']) && !empty($params['end_date'])) {
            $query->where('question_answers.created_at', '>=', "{$params['begin_date']}");
            $query->where('question_answers.created_at', '<=', "{$params['end_date']} 23:59:59");
        }

        $total_query = clone $query;
        $total       = $total_query->count('question_answers.question_id');
        return $total;
    }

    public function getQuestionNums($params)
    {
        vss_validator($params, [
            'room_id'    => '',
            'account_id' => '',
        ]);
        $query = vss_model()->getRoomQuestionLkModel()->newQuery();
        $query->leftJoin('questions', 'room_question_lk.question_id', 'questions.question_id');
        if (!empty($params['room_id'])) {
            $query->where('room_question_lk.room_id', $params['room_id']);
        }
        if (!empty($params['account_id'])) {
            $query->where('questions.account_id', $params['account_id']);
        }
        $query->where('room_question_lk.publish', 1);
        $query->whereNull('questions.deleted_at');
        //$query ->groupBy('room_question_lk.question_id');

        if (!empty($params['begin_date']) && !empty($params['end_date'])) {
            $query->where('room_question_lk.created_at', '>=', "{$params['begin_date']}");
            $query->where('room_question_lk.created_at', '<=', "{$params['end_date']} 23:59:59");
        }

        $total_query = clone $query;
        $total       = $total_query->distinct('room_question_lk.question_id')->count('room_question_lk.question_id');
        return $total;
    }

    /**
     * Notes: 获取提交问卷信息
     * User: michael
     * Date: 2019/8/22
     * Time: 16:53
     *
     * @param $params
     *
     * @return array
     *
     */
    public function getSubmitInfo($params)
    {
        $params = array_filter(array_map('trim', $params));
        vss_validator($params, [
            'room_id'     => 'required',
            'question_id' => '',
            'page'        => '',
            'page_size'   => '',
        ]);
        $page     = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 1000; // 之前没有分页， 为了兼容以前的场景
        $data     = vss_model()->getQuestionAnswersModel()->newQuery()
            ->leftJoin('room_joins', 'question_answers.join_id', 'room_joins.join_id')
            // ->leftJoin('room_question_lk','room_question_lk.question_id','question_answers.question_id')
            ->where('question_answers.room_id', $params['room_id'])
            ->where('question_answers.question_id', $params['question_id'])
            ->selectRaw('question_answers.*,room_joins.account_id as third_id')
            ->forPage($page, $pageSize)
            ->get()
            ->toArray();
        return !empty($data) ? $data : [];
    }

    /**
     * 统计信息
     *
     * @param $params
     *
     * @return int|mixed
     */
    public function getStat($params)
    {
        if (isset($params['created_at']) && !empty($params['created_at'])) {
            if (isset($params['end_date']) && !empty($params['end_date'])) {
                $condition = [
                    ['created_at', '>=', "{$params['created_at']}"],
                    ['created_at', '<=', "{$params['end_date']} 23:59:59"],
                ];
            } else {
                $condition = [['created_at', '>=', "{$params['created_at']}"]];
            }
        }
        $condition['app_id'] = $params['app_id'];
        if (isset($params['account_id']) && !empty($params['account_id'])) {
            $condition['account_id'] = $params['account_id'];
        }

        $num = vss_model()->getQuestionsModel()->where($condition)->count();
        return $num > 0 ? $num : 0;
    }

    /**
     * @param $params
     *
     * @return mixed
     *
     */
    public function accountStatisticsList($params)
    {
        vss_validator($params, [
            'account_id' => 'required',
            'page'       => '',
            'pagesize'   => '',
        ]);
        $page     = $params['page'] ?? 1;
        $pagesize = $params['pagesize'] ?? 10;
        $query    = vss_model()->getQuestionAnswersModel()->newQuery()
            ->leftJoin('questions', 'question_answers.question_id', 'questions.question_id')
            ->where('question_answers.account_id', $params['account_id'])
            ->whereNull('questions.deleted_at');

        if (!empty($params['begin_date']) && !empty($params['end_date'])) {
            $query->where('question_answers.created_at', '>=', "{$params['begin_date']}")
                ->where('question_answers.created_at', '<=', "{$params['end_date']} 23:59:59");
        }
        $query->groupBy('question_answers.question_id');
        $query->groupBy('question_answers.room_id');

        $list = $query->selectRaw(
            'questions.question_id, questions.title, questions.description, questions.cover,' .
            'count(1) answer_count,question_answers.room_id, question_answers.updated_at'
        )
            ->groupBy('question_answers.question_id')
            ->groupBy('question_answers.room_id')
            ->orderBy('question_answers.updated_at', 'desc')
            ->paginate($pagesize, ['*'], 'page', $page);

        $list = json_decode(json_encode($list, JSON_UNESCAPED_UNICODE), true);
        return [
            'list'     => $list['data'],
            'total'    => $list['total'],
            'pagesize' => $pagesize,
            'page'     => $list['current_page'],
        ];
    }

    public function copy($params)
    {
        vss_validator($params, [
            'room_id'     => '',
            'question_id' => 'required',
            'account_id'  => 'required',
            'app_id'      => 'required',
        ]);
        if ($params['room_id']) {
            $room = vss_model()->getRoomsModel()->findByRoomId($params['room_id']);
            ($params['account_id'] != $room->account_id) && $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        //获取vss问卷信息
        $questionId = $params['question_id'];
        $question   = vss_model()->getQuestionsModel()->find($questionId);
        if (empty($question)) {
            $this->fail(ResponseCode::EMPTY_QUESTION);
        }
        //从微吼云获取问卷详细信息
        $info = vss_service()->getPaasService()->getFormInfo($questionId);
        if (empty($info)) {
            $this->fail(ResponseCode::EMPTY_QUESTION);
        }
        $createArr = [
            'title'       => $info['title'],
            'description' => $info['description'],
            'imgUrl'      => $info['imgUrl'],
            'publish'     => 'Y', //PaaS没有发布问卷功能,默认写死
            'detail'      => $this->formatDetail($info['detail']),
            'owner_id'    => $params['account_id'],
        ];
        $createArr = $this->unsetEmptyArr($createArr);
        //在微吼创建问卷
        $newQuestion = vss_service()->getPaasService()->createForm($createArr);
        if (empty($newQuestion['id'])) {
            $this->fail(ResponseCode::EMPTY_QUESTION);
        }
        $createQuestion = [
            'question_id' => $newQuestion['id'],
            'title'       => $question->title,
            'description' => $question->description,
            'cover'       => $question->cover,
            'extend'      => $question->extend,
            'account_id'  => $question->account_id,
            'app_id'      => $question->app_id,
            'is_public'   => $question->is_public,
        ];
        //在VSS创建问卷
        $question = vss_model()->getQuestionsModel()->create($createQuestion);
        $params['room_id'] && $this->bindRoom([
            'question_id' => $newQuestion['id'],
            'room_id'     => $params['room_id'],
        ]);
        return $question;
    }

    /**
     * 问卷推屏
     *
     * @param $params
     *
     * @return bool
     *
     */
    public function repush($params)
    {
        vss_validator($params, [
            'room_id'     => '',
            'question_id' => 'required',
            'account_id'  => 'required',
        ]);
        $questionId = $params['question_id'];

        //推屏间隔时间限制
        $key      = QuestionConstants::QUESTION_PUSH . $params['question_id'];
        $ttl_time = vss_redis()->ttl($key);
        if ($ttl_time > 0) {
            $waitTime = ceil($ttl_time / 60);
            $this->fail(ResponseCode::BUSINESS_DONT_FREQUENT_OPERATION, [
                'waitTime' => $waitTime
            ]);
        }

        // 发布问卷，问卷信息本地静态化
        $ossUrl      = vss_service()->getFormService()->writeInfoLocal($questionId, 'question');
        $answeredKey = QuestionConstants::ROOM_QUESTION_ANSWERED_USER_ID . $params['room_id'] . ':' . $params['question_id'];

        //未答卷用户信息分批发送防止消息body体过长
        $flag   = true;
        $offset = 0;
        $limit  = 1000;
        while ($flag) {
            $offset   = $offset * $limit;
            $joinList = vss_model()->getQuestionAnswersModel()->newQuery()
                ->join('room_joins', function ($join) use ($questionId) {
                    $join->on('question_answers.join_id', '=', 'room_joins.join_id')
                        ->where('question_answers.question_id', '=', $questionId);
                }, null, null, 'right')
                ->where('room_joins.room_id', $params['room_id'])
                ->whereNull('question_answers.answer_id')->offset($offset)->limit($limit)->get([
                    'room_joins.account_id',
                    'room_joins.role_name',
                ])->toArray();
            //数据取尽本次执行后终止
            if (count($joinList) < $limit) {
                $flag = false;
            }

            $unAnswerList = [];
            foreach ($joinList as $arr) {
                if (vss_redis()->sismember($answeredKey, $arr['account_id'])) {
                    continue;
                }

                $unAnswerList[] = $arr;
            }

            //分批发送防止消息body体过长
            $joinArr = array_chunk($unAnswerList, 100);
            foreach ($joinArr as $joiner) {
                vss_service()->getPaasChannelService()->sendMessage($params['room_id'], [
                    'type'             => 'questionnaire_repush',
                    'questionnaire_id' => $params['question_id'],
                    'unanswer_joins'   => $joiner,
                    'account_id'       => vss_service()->getTokenService()->getAccountId(),
                    'info_url'         => $ossUrl,
                ]);
            }
        }
        vss_redis()->set($key, 1, QuestionConstants::PUSH_TIME_CONTINUE);
        return true;
    }

    /**
     * 答卷和问卷统计
     *
     * @param array $condition
     * return int
     *
     * @return array
     *
     */
    public function answerQuestionTotal(array $condition)
    {
        $return   = [
            'answer'   => 0,
            'question' => 0,
        ];
        $roomInfo = [];
        if (!empty($condition['il_id'])) {
            $roomInfo = vss_model()->getRoomsModel()->getRow(['il_id' => $condition['il_id']]);
        }

        $params = [
            'begin_date' => $condition['begin_time'],
            'end_date'   => $condition['end_time'],
        ];
        if (!$roomInfo) {
            $params['room_id'] = $roomInfo['room_id'];
        }
        if ($condition['account_id']) {
            $params['account_id'] = $condition['account_id'];
        }

        $result = $this->getQuestionNum($params);

        if ($result) {
            $return['answer']   = $result['by_account_num'] ?? 0;
            $return['question'] = $result['by_room_num'] ?? 0;
        }

        return $return;
    }

    /**
     * 统计-问卷使用记录
     *
     * @param $beginTime
     * @param $endTime
     *
     * @param $ilId
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @author ensong.liu@vhall.com
     * @date   2019-05-22 19:26:24
     */
    public function questionLog($ilId, $beginTime, $endTime)
    {
        $condition = [
            'il_id'      => $ilId,
            'begin_time' => $beginTime,
            'end_time'   => $endTime,
        ];

        return vss_model()->getQuestionLogsModel()->getInstance()->setPerPage(100)->getList($condition, ['question']);
    }

    /**
     * 统计-问卷使用记录
     *
     * @param $accountId
     * @param $ilId
     * @param $beginTime
     * @param $endTime
     * @param $page
     * @param $perPage
     *
     * @return array
     *
     */
    public function getQuestionLog($accountId, $ilId, $beginTime, $endTime, $page, $perPage)
    {
        //1.1、组织数据格式
        $condition = [
            'il_id'      => $ilId,
            'room_id'    => '',
            'account_id' => $accountId,
            'begin_date' => $beginTime,
            'end_date'   => $endTime,
            'page'       => $page,
            'pagesize'   => $perPage,
        ];
        //1.2、默认返回数据格式
        $return = [
            'total'    => 0,
            'page'     => $page,
            'per_page' => $perPage,
            'data'     => [],
        ];

        //2、获取房间信息
        //2.1获取用户问卷管理列表
        if (empty($ilId)) {
            $roomIdArr = []; //存储room_id容器
            $result    = $this->accountStatisticsList($condition);
            if (!empty($result['list'])) {
                foreach ($result['list'] as $value) {
                    $tmpRoomId             = $value['room_id'];
                    $roomIdArr[$tmpRoomId] = $tmpRoomId;
                }
                //如果$roomIdArr不为空，获取对应的互动房间ID值
                if (isset($roomIdArr) && !empty($roomIdArr)) {
                    $ilInfoArr = vss_model()->getRoomsModel()->getInstance()->whereIn('room_id', $roomIdArr)->get([
                        'il_id',
                        'room_id',
                    ]);
                    foreach ($ilInfoArr as $ilInfo) {
                        $tmpIl[$ilInfo['room_id']] = $ilInfo['il_id'];
                    }
                    foreach ($result['list'] as $k => $v) {
                        $result['list'][$k]['il_id'] = empty($tmpIl[$v['room_id']]) ? 0 : $tmpIl[$v['room_id']];
                    }
                }

                $return['total']    = $result['total'];
                $return['page']     = $result['page'];
                $return['per_page'] = $perPage;
                $return['data']     = $result['list'];
            }
        } else {
            //2.1、获取互动房间信息
            $roomInfo = vss_service()->getRoomService()->getRow([
                'il_id'      => $ilId,
                'account_id' => $condition['account_id'],
            ]);

            //获取对应房间的问卷统计列表信息
            $condition['room_id'] = $roomInfo['room_id'];
            $questListArr         = $this->statisticsList($condition);
            if (!empty($questListArr['list'])) {
                $return['total']    = $questListArr['total'];
                $return['page']     = $questListArr['page'];
                $return['per_page'] = $questListArr['pagesize'];

                foreach ($questListArr['list'] as $item) {
                    $return['data'][] = [
                        'question_id'  => $item['question_id'],
                        'room_id'      => $item['room_id'],
                        'il_id'        => $ilId,
                        'title'        => $item['title'],
                        'answer_count' => $item['answer_count'],
                        'updated_at'   => $item['updated_at'],
                    ];
                }
            }
        }

        return $return;
    }

    /**
     * 导出单个问卷及答案列表
     * type: radio=>单选、 checkbox=>多选、 select=>下拉、 text=>问答、 matrix=>单选表格|多选表格
     *
     * @param $export
     * @param $filePath
     *
     * @return bool
     */
    public function getQuestionAnswerExportData($export, $filePath)
    {
        $params     = json_decode($export['params'], true);
        $questionId = $params['question_id'];
        $ilId       = $params['il_id'];
        $file       = $filePath . $export['file_name'];

        //step0:表格信息
        $header = json_decode($export['title'], true);

        //step1:问卷详细信息
        $question_info = vss_service()->getPaasService()->getFormInfo($questionId);
        if (empty($question_info)) {
            throw new \Exception('获取问卷详细信息失败');
        }
        //判断房间ID是否存在
        $liveInfo = vss_model()->getRoomsModel()->getInfoByIlId($ilId);

        //step2:排序-对应问题答案顺序
        $question_ids            = array_column($question_info['detail'], 'id'); //返回ID值
        $question_info['detail'] = array_combine($question_ids, $question_info['detail']); //将键值指定为ID值
        ksort($question_info['detail']); //按照键名排序

        //step3:补充问题标题头信息--此部分来拼标题
        foreach ($question_info['detail'] as $detail_key => $detail_value) {
            switch ($detail_value['type']) {
                case 'radio':
                case 'checkbox':
                case 'select':
                case 'text':
                case 'date':
                case 'area':
                case 'remark':
                    $header[] = $detail_value['title'];
                    break;
                case 'matrix':
                    foreach ($detail_value['detail']['row'] as $row_key => $row_value) {
                        $header[] = sprintf('%s(%s)', $detail_value['title'], $row_value['value']);
                    }
                    break;
                default:
                    //nothing...
            }
        }
        $header[]    = '提交时间';
        $export_data = [];

        $page               = 1;
        $exportProxyService = vss_service()->getExportProxyService()->init($file)->putRow($header);
        while (true) {
            //step4:构建问卷答案列表信息--此部分来填充用户的回答信息
            $answer_list = $this->getSubmitInfo([
                'question_id' => $questionId,
                'room_id'     => $liveInfo['room_id'],
                'page'        => $page++,
                'page_size'   => 1000,
            ]);

            if (!$answer_list) {
                break;
            }

            // 获取问卷的填写人数
            $answererCountMap = vss_model()->getQuestionAnswersModel()->getAnswerCountByQuestionIds([$questionId],
                $liveInfo['room_id']);
            $answererCount    = $answererCountMap[$questionId] ?? 0;
            foreach ($answer_list as $answer_value) {
                //获取单个答卷
                $answer_info = vss_service()->getPaasService()->getAnswerDetail($questionId,
                    $answer_value['answer_id']);
                if (empty($answer_info)) {
                    throw new \Exception('获取单个答卷详情失败');
                }

                //构造行信息--获取用户信息, 游客填写的问卷，不存在 third_id
                $account = null;
                if ($answer_value['third_id']) {
                    $account = vss_model()->getAccountsModel()->getInstance()->find($answer_value['third_id']);
                }

                $row_data = [
                    $liveInfo['il_id'], //活动号
                    $liveInfo['name'], //活动名
                    $question_info['id'], //问卷 ID
                    $question_info['title'], //问卷名称
                    $answererCount, //问卷填写人数
                    $account ? $account->nickname : '-', //答卷人昵称
                    $account ? $account->username : '-', //答卷人账号
                ];
                foreach ($question_info['detail'] as $detail_value) {
                    $answer = $answer_info['answer'][$detail_value['id']] ?? '';
                    switch ($detail_value['type']) {
                        case 'radio':
                        case 'checkbox':
                        case 'select':
                            $tmp_data = [];
                            foreach ($detail_value['detail']['list'] as $list_value) {
                                $tmp_arr = explode(',', $answer);
                                foreach ($tmp_arr as $tmp_value) {
                                    if ($list_value['key'] == $tmp_value) {
                                        $tmp_data[] = $list_value['value'];
                                    }
                                }
                            }
                            $row_data[] = implode('|', $tmp_data); //不要用","分隔
                            break;
                        case 'text':
                        case 'date':
                        case 'area':
                        case 'remark':
                            $row_data[] = $answer ? str_replace("\n", ' ', $answer) : ' ';
                            break;
                        case 'matrix':
                            $tmp_data = [];
                            if (!empty($answer)) {
                                $tmp_arr = explode(',', $answer);
                                //$tmp_data [0=>[1],1=>[0,1]]  1行2列 2行1列 2行2列
                                array_walk($tmp_arr, function ($value) use (&$tmp_data) {
                                    if (strpos($value, '-') !== false) {
                                        list($row, $column) = explode('-', $value);
                                        $tmp_data[$row - 1][] = $column - 1;
                                        ksort($tmp_data);
                                    }
                                });

                                //$tmp_data [0=>[1],1=>[0,1]]  变为 [0=>1行2列,1=>2行1列|2行2列]
                                array_walk($tmp_data, function (&$value) use ($detail_value) {
                                    $colum_list = array_column($detail_value['detail']['column'], 'value');
                                    $value      = implode(
                                        '|',
                                        (array)array_intersect_key($colum_list, array_flip($value))
                                    ); //不要用","分隔
                                });
                            }
                            //按行分配数据
                            foreach ($detail_value['detail']['row'] as $row => $row_value) {
                                $row_data[] = $tmp_data[$row] ? $tmp_data[$row] : ' ';
                            }
                            break;
                        default:
                            //nothing...
                    }
                }
                $row_data[]    = $answer_info['created_at'];
                $export_data[] = array_map(function ($value) {
                    return $value . "\t";
                }, $row_data);
            }

            $exportProxyService->putRows($export_data);
            $export_data = [];
        }
        $exportProxyService->close();

        return true;
    }

    /**
     * 导出消息配置
     *
     * @param $ilId
     * @param $accountId
     * @param $fileName
     * @param $beginTime
     * @param $endTime
     */
    public function exportQuestionAnswer($ilId, $accountId, $questionId, $fileName)
    {
        $liveInfo = vss_service()->getRoomService()->getInfoByIlId($ilId);
        if (empty($liveInfo)) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }
        $params = [
            'question_id' => $questionId,
            'il_id'       => $ilId,
        ];

        $insert = [
            'export'     => QuestionConstants::EXPORT_QUESTION_ANSWER,
            'il_id'      => $ilId,
            'account_id' => $accountId,
            'source_id'  => $questionId,
            'file_name'  => $fileName,
            'title'      => ['房间ID', '房间名称', '问卷ID', '问卷名称', '填写人数', '用户昵称', '用户账号'],
            'params'     => json_encode($params),
            'callback'   => 'question:getQuestionAnswerExportData',
        ];

        return vss_model()->getExportModel()->create($insert);
    }

    public function unsetEmptyArr($arr)
    {
        foreach ($arr as $k => $v) {
            if (empty($v)) {
                unset($arr[$k]);
            }
        }
        return $arr;
    }

    public function formatDetail($detail)
    {
        foreach ($detail as $k => $v) {
            unset($v['id'], $v['third_party_user_id'], $v['created_at'], $v['app_id']);

        }
        return json_encode($detail);
    }
}
