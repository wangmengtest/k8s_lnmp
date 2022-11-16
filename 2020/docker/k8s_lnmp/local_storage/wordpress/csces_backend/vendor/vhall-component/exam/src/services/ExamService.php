<?php
/**
 * Created by PhpStorm.
 * Date: 2020/3/23
 * Time: 17:49
 */

namespace vhallComponent\exam\services;

use App\Constants\ResponseCode;
use Illuminate\Support\Arr;
use vhallComponent\exam\constants\ExamConstant;
use vhallComponent\exam\jobs\ExamAuthFinishJob;
use vhallComponent\exam\jobs\SubmitExamAnswerJob;
use vhallComponent\room\constants\CachePrefixConstant;
use Vss\Common\Services\WebBaseService;
use vhallComponent\exam\models\ExamsModel;

class ExamService extends WebBaseService
{
    /**
     * 试卷-创建
     *
     * @param $params
     *
     * @return array
     *
     */
    public function paperCreate($params)
    {
        vss_validator($params, [
            'title'        => 'required',
            'desc'         => '',
            'extend'       => '',
            'exam_id'      => 'required',
            'account_id'   => 'required',
            'is_public'    => '',
            'source_id'    => '',
            'score'        => 'required',
            'question_num' => 'required',
            'limit_time'   => ['required_if:type,1', 'min:0'],
            'type'         => '',
        ]);

        //TODO 核实计算分数题目数

        $data                 = [];
        $data['title']        = $params['title'];
        $data['desc']         = $params['desc'] ?? '';
        $data['extend']       = $params['extend'] ?? '';
        $data['exam_id']      = $params['exam_id'];
        $data['account_id']   = $params['account_id'];
        $data['score']        = $params['score'];
        $data['question_num'] = $params['question_num'];
        $data['limit_time']   = $params['limit_time'] ?? 0;
        //试卷类型确定后 不可修改
        $exam = vss_model()->getExamsModel()->findByExamId($data['exam_id']);
        if (!empty($exam)) {
            $data['type'] = $exam['type'];
        } else {
            $data['type'] = $params['type'] ?? 0;
            //类型为试卷时 不限时间
            !$data['type'] && $data['limit_time'] = 0;
        }
        $data['is_public'] = $params['is_public'] ? $params['is_public'] : 0;
        $data['source_id'] = $params['source_id'] ?? '';

        $examInfo = vss_model()->getExamsModel()->saveByExamId($data['exam_id'], $data);
        if (empty($examInfo)) {
            $this->fail(ResponseCode::BUSINESS_CREATE_FAILED);
        }
        return $examInfo;
    }

    /**
     * 试卷-列表
     *
     * @param $params
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     */
    public function paperList($params)
    {
        $validator               = vss_validator($params, [
            'account_id' => 'required',
            'keyword'    => '',
            'page'       => '',
            'pagesize'   => '',
            'begin_time' => '',
            'end_time'   => '',
        ]);
        $modelExams              = vss_model()->getExamsModel();
        $condition               = [];
        $condition['keyword']    = trim($params['keyword']);
        $condition['account_id'] = $params['account_id'];
        $condition['type']       = $modelExams::TYPE_PAPER;
        $params['begin_time'] && $condition['begin_time'] = $params['begin_time'];
        $params['end_time'] && $condition['end_time'] = $params['end_time'];
        $page     = $params['page'] ? $params['page'] : 1;
        $pagesize = $params['pagesize'] ? $params['pagesize'] : 10;

        $list = $modelExams->setPerPage($pagesize)->getList($condition, [], $page);
        return $list;
    }

    /**
     * 考试-考试创建、修改
     *
     * @param $params
     *
     * @return mixed
     *
     */
    public function create($params)
    {
        $rule = [
            'room_id'      => 'required',
            'title'        => 'required',
            'desc'         => '',
            'extend'       => '',
            'exam_id'      => 'required',
            'account_id'   => 'required',
            'is_public'    => '',
            'source_id'    => '',
            'score'        => 'required',
            'question_num' => 'required',
            'limit_time'   => 'required',
        ];
        $data = vss_validator($params, $rule);

        //试卷存在情况
        $modelExams = vss_model()->getExamsModel();
        $exam       = $modelExams->findByExamId($params['exam_id']);
        if (!empty($exam)) {
            if ($exam['type'] != $modelExams::TYPE_EXAM) {
                $this->fail(ResponseCode::TYPE_EXAM_ERROR);
            }
            $lk = vss_model()->getRoomExamLkModel()->findByRoomIdAndExamId($params['room_id'], $params['exam_id']);
            ($lk->publish == 1) && $this->fail(ResponseCode::COMP_EXAM_NOT_EDIT);
        }

        //创建、修改试卷
        unset($data['room_id']);
        $examInfo = $this->paperCreate($data + ['type' => $modelExams::TYPE_EXAM]);
        //更换绑定房间
        if ($lk['room_id'] != $params['room_id']) {
            $this->bindRoom([
                'exam_id'    => $params['exam_id'],
                'room_id'    => $params['room_id'],
                'account_id' => $params['account_id'],
            ]);
        }
        //新建考试 复制试卷
        if (empty($exam)) {
            $this->copy($data);
        }

        return $examInfo;
    }

    /**
     * 考试修改
     *
     * @param $params
     *
     * @return array
     *
     */
    public function update($params)
    {
        $rule = [
            'old_exam_id'  => 'required',
            'room_id'      => 'required',
            'title'        => 'required',
            'desc'         => '',
            'extend'       => '',
            'exam_id'      => 'required',
            'account_id'   => 'required',
            'is_public'    => '',
            'source_id'    => '',
            'score'        => 'required',
            'question_num' => 'required',
            'limit_time'   => 'required',
        ];
        $data = vss_validator($params, $rule);

        $lk = vss_model()->getRoomExamLkModel()->where('exam_id', $params['old_exam_id'])->first();
        !$lk && $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        ($lk->publish == 1) && $this->fail(ResponseCode::COMP_EXAM_NOT_EDIT);
        //试卷存在情况
        $modelExams = vss_model()->getExamsModel();
        $exam       = $modelExams->findByExamId($params['exam_id']);
        if (!empty($exam) && $exam['type'] != $modelExams::TYPE_EXAM) {
            $this->fail(ResponseCode::TYPE_EXAM_ERROR);
        }

        $exist = vss_model()->getRoomExamLkModel()->getExamCount('exam_id', $params['exam_id']);
        $exist && $this->fail(ResponseCode::COMP_EXAM_INVALID);
        //创建、修改试卷
        unset($data['old_exam_id'], $data['room_id']);
        $examInfo = $this->paperCreate($data + ['type' => $modelExams::TYPE_EXAM]);
        if ($lk['room_id'] != $params['room_id'] || $lk['exam_id'] != $params['exam_id']) {
            $lk->room_id = $params['room_id'];
            $lk->exam_id = $params['exam_id'];
            $lk->update();
        }
        return $examInfo;
    }

    /**
     * 考试-获取试卷列表
     *
     * @param $params
     *
     * @return mixed
     *
     */
    public function list($params)
    {
        $validator               = vss_validator($params, [
            'room_id'           => ['required_without:account_id'],
            'account_id'        => ['required_without:room_id'],
            'keyword'           => '',
            'from_room_id'      => '',
            'answer_account_id' => '',
            'page'              => '',
            'pagesize'          => '',
            'begin_time'        => '',
            'end_time'          => '',
            'publish'           => '',
            'is_finish'         => '',
            'is_grade'          => '',
            'is_push_grade'     => '',
            'status'            => '',
        ]);
        $page                    = $params['page'] ?? 1;
        $pagesize                = $params['pagesize'] ?? 10;
        $modelExams              = vss_model()->getExamsModel();
        $condition               = [];
        $condition['exams.type'] = $modelExams::TYPE_EXAM;
        !empty($params['room_id']) && $condition['room_exam_lk.room_id'] = $params['room_id'];
        !empty($params['account_id']) && $condition['room_exam_lk.account_id'] = $params['account_id'];
        !empty(trim($params['keyword'])) && $condition['keyword'] = $params['keyword'];
        !empty($params['begin_time']) && $condition['begin_time'] = $params['begin_time'];
        !empty($params['end_time']) && $condition['end_time'] = $params['end_time'];
        isset($params['publish']) && $condition['room_exam_lk.publish'] = $params['publish'];
        isset($params['is_finish']) && $condition['room_exam_lk.is_finish'] = $params['is_finish'];
        isset($params['is_grade']) && $condition['room_exam_lk.is_grade'] = $params['is_grade'];
        isset($params['is_push_grade']) && $condition['room_exam_lk.publish'] = $params['is_push_grade'];
        if (!empty($params['status'])) {
            $this->getConditionByStatus($condition, $params['status']);
        }
        $columns = [
            'exams.exam_id',
            'exams.title',
            'exams.score',
            'exams.question_num',
            'exams.limit_time',
            'exams.type',
            'room_exam_lk.account_id',
            'room_exam_lk.room_id',
            'room_exam_lk.publish',
            'room_exam_lk.finish_time',
            'room_exam_lk.is_finish',
            'room_exam_lk.is_grade',
            'room_exam_lk.is_push_grade',
            'room_exam_lk.id',
            'room_exam_lk.created_at',
            'room_exam_lk.updated_at'
        ];
        if ($params['room_id']) {
            $list = vss_model()->getRoomExamLkModel()->joinExamsList($condition, $columns, $page, $pagesize);
        } else {
            $columns[] = 'rooms.il_id';
            $columns[] = 'rooms.subject';
            $list      = vss_model()->getRoomExamLkModel()->joinExamsAndRoomsList($condition, $columns, $page,
                $pagesize);
        }

        //查询是否回答过问卷
        if (!empty($params['answer_account_id'])) {
            $data        = $list->items();
            $answer_list = vss_model()->getExamAnswersModel()
                ->where(['room_id' => $params['room_id'], 'account_id' => $params['answer_account_id']])
                ->whereIn('exam_id', array_column($data, 'exam_id'))->get()->toArray();
            $answerMap   = [];
            foreach ($answer_list as $alk) {
                $answerMap[$alk['exam_id']] = $alk;
            }
        }

        foreach ($list as $k => &$item) {
            $data           = $item->toArray();
            $item['status'] = $this->examStatus($data);
            $item['answer'] = 0;
            if (!empty($answerMap)) {
                $item['answer']    = empty($answerMap[$item['exam_id']]) ? 0 : 1;
                $item['answer_id'] = (int)$answerMap[$item['exam_id']]['answer_id'];
            }
            //观众提交答案至队列处理有时间差 因此使用缓存判断是否提交
            if ($params['answer_account_id'] && $item['status'] == ExamConstant::LK_STATUS_PUBLISH) {
                $key            = ExamConstant::EXAM_ANSWER . 'lock:' . $item['exam_id'] . '-' . $item['room_id'] . '-' . $params['answer_account_id'];
                $answer         = vss_redis()->get($key);
                $item['answer'] = $answer ? (int)$answer : $item['answer'];
            }

            //观众获取考试中列表 如果已作答则不展示 如无需此逻辑可删除
            if ($item['answer'] && $params['answer_account_id'] && $params['status'] == ExamConstant::LK_STATUS_PUBLISH) {
                unset($list[$k]);
            }
        }
        return $list;
    }

    /**
     * 获取考试状态
     *
     * @param $params
     *
     * @return int
     */
    public function examStatus($params)
    {
        vss_validator($params, [
            'publish'       => 'required',
            'is_finish'     => 'required',
            'is_grade'      => 'required',
            'is_push_grade' => 'required',
        ]);
        $status = 0;
        switch ($params) {
            case (int)$params['publish'] === 0:
                $status = ExamConstant::LK_STATUS_NOT_PUBLISH;
                break;
            case (int)$params['publish'] === 1 && (int)$params['is_finish'] === 0:
                $status = ExamConstant::LK_STATUS_PUBLISH;
                break;
            case (int)$params['is_finish'] === 1 && (int)$params['is_grade'] === 0:
                $status = ExamConstant::LK_STATUS_FINISH;
                break;
            case (int)$params['is_grade'] === 1 && (int)$params['is_push_grade'] === 0:
                $status = ExamConstant::LK_STATUS_GRADED;
                break;
            case (int)$params['is_push_grade'] === 1:
                $status = ExamConstant::LK_STATUS_PUSH;
                break;
        }
        return $status;
    }

    /**
     * 根据status获取对应条件
     *
     * @param array $condition
     * @param       $status
     *
     * @return array
     */
    public function getConditionByStatus(array &$condition, $status)
    {
        if (!in_array($status, [
            ExamConstant::LK_STATUS_NOT_PUBLISH,
            ExamConstant::LK_STATUS_PUBLISH,
            ExamConstant::LK_STATUS_FINISH,
            ExamConstant::LK_STATUS_GRADED,
            ExamConstant::LK_STATUS_PUSH
        ])) {
            return $condition;
        }
        switch ($status) {
            case ExamConstant::LK_STATUS_NOT_PUBLISH:
                $condition['room_exam_lk.publish'] = 0;
                break;
            case ExamConstant::LK_STATUS_PUBLISH:
                $condition['room_exam_lk.publish']   = 1;
                $condition['room_exam_lk.is_finish'] = 0;
                break;
            case ExamConstant::LK_STATUS_FINISH:
                $condition['room_exam_lk.is_finish'] = 1;
                $condition['room_exam_lk.is_grade']  = 0;
                break;
            case ExamConstant::LK_STATUS_GRADED:
                $condition['room_exam_lk.is_grade']      = 1;
                $condition['room_exam_lk.is_push_grade'] = 0;
                break;
            case ExamConstant::LK_STATUS_PUSH:
                $condition['room_exam_lk.is_push_grade'] = 1;
                break;
        }
        return $condition;
    }

    /**
     * 考试-试卷删除
     *
     * @param $params
     *
     *
     */
    public function delete($params)
    {
        vss_validator($params, [
            'exam_id'    => 'required',
            'account_id' => 'required',
        ]);
        $exam = $this->getExam($params['exam_id']);
        if ($exam->account_id != $params['account_id']) {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }

        $list = vss_model()->getRoomExamLkModel()->getList(['exam_id' => $params['exam_id']], [], null, ['publish']);
        foreach ($list as $info) {
            if ($info['publish']) {
                $this->fail(ResponseCode::COMP_EXAM_NOT_EDIT);
            }
        }
        //exam 删除 type为1时连带删除room_exam_lk 关联数据
        return $exam->delete();
    }

    /**
     * 试卷详情
     *
     * @param $params
     *
     * @return ExamsModel|null
     *
     */
    public function paperInfo($params)
    {
        vss_validator($params, [
            'exam_id'    => 'required',
            'account_id' => 'required',
        ]);

        $exam = vss_model()->getExamsModel()->findByExamId($params['exam_id']);
        !$exam && $this->fail(ResponseCode::COMP_EXAM_INVALID);
        //只能创建用户查看
        $exam->account_id != $params['account_id'] && $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        return $exam;
    }

    /**
     * 考试详情
     *
     * @param $params
     *
     * @return array
     *
     */
    public function roomExamInfo($params)
    {
        $validator                  = vss_validator($params, [
            'exam_id'    => 'required',
            'account_id' => 'required',
        ]);
        $condition                  = [];
        $condition['exams.exam_id'] = $params['exam_id'];
        $columns                    = ['rooms.room_id', 'rooms.subject', 'exams.*'];
        $roomExamInfo               = vss_model()->getRoomExamLkModel()->joinExamsAndRoomsInfo($condition, $columns);

        empty($roomExamInfo) && $this->fail(ResponseCode::COMP_EXAM_INVALID);
        return $roomExamInfo;
    }

    /**
     * 房间试卷信息
     *
     * @param $params
     *
     * @return array
     *
     */
    public function publishInfo($params)
    {
        vss_validator($params, [
            'exam_id' => 'required',
            'room_id' => 'required',
        ]);
        $exam = $this->getExam($params['exam_id']);
        $exam = $exam->toArray();
        $lk   = vss_model()->getRoomExamLkModel()->findByRoomIdAndExamId(
            $params['room_id'],
            $params['exam_id']
        );
        !$lk && $this->fail(ResponseCode::COMP_EXAM_INVALID);
        $lk      = $lk->toArray();
        $dowTime = 0;
        //剩余答题时间 发布时间 + 考试时长 - 当前时间
        if ($exam['limit_time'] && $lk['publish_time']) {
            $dowTime = time() < $lk['publish_time'] + $exam['limit_time'] ? time() - $lk['publish_time'] + $exam['limit_time'] : 0;
        }
        $exam['down_time'] = $dowTime;
        $data              = array_merge($exam, $lk);
        return $data;
    }

    /**
     * 考试-试卷绑定
     *
     * @param $params
     *
     *
     */
    public function bindRoom($params)
    {
        $rule = [
            'exam_id'    => 'required',
            'room_id'    => 'required',
            'account_id' => 'required',
        ];
        $data = vss_validator($params, $rule);
        //验证试卷信息
        $this->getExam($params['exam_id'], $params['room_id']);
        //绑定
        $lk = vss_model()->getRoomExamLkModel()->findByRoomIdAndExamId(
            $params['room_id'],
            $params['exam_id']
        );
        if (empty($lk)) {
            vss_model()->getRoomExamLkModel()->updateOrCreate(['exam_id' => $params['exam_id']], $data + ['bind' => 1]);
        } elseif ($lk->bind != 1) {
            $lk->update(['bind' => 1]);
        }
    }

    /**
     * 考试-试卷复制
     *
     * @param $params
     *
     * @return array
     *
     */
    public function copy($params)
    {
        vss_validator($params, [
            'exam_id'    => 'required',
            'account_id' => 'required',
            'room_id'    => '',
        ]);
        if ($params['room_id']) {
            $room = vss_model()->getRoomsModel()->findByRoomId($params['room_id']);
            ($params['account_id'] != $room->account_id) && $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        //获取试卷信息
        $examId = $params['exam_id'];
        $exam   = $this->getExam($examId);
        $exam   = $exam->toArray();
        if (empty($exam)) {
            $this->fail(ResponseCode::COMP_EXAM_INVALID);
        }
        //从微吼云获取问卷详细信息
        $info = vss_service()->getPaasService()->getFormInfo($examId);
        if (empty($info)) {
            $this->fail(ResponseCode::COMP_EXAM_INVALID);
        }
        $createArr = [
            'title'    => $info['title'],
            'publish'  => 'Y', //PaaS没有发布问卷功能,默认写死
            'detail'   => $this->formatDetail($info['detail']),
            'owner_id' => $params['account_id']
        ];
        $createArr = $this->unsetEmptyArr($createArr);
        //在微吼创建问卷
        $newExam = vss_service()->getPaasService()->createForm($createArr);
        if (empty($newExam['id'])) {
            $this->fail(ResponseCode::COMP_EXAM_INVALID);
        }

        //复制试卷
        $modelExam             = vss_model()->getExamsModel();
        $createExam            = $exam;
        $createExam['exam_id'] = $newExam['id'];
        $createExam['type']    = $modelExam::TYPE_PAPER;
        //绑定房间的试卷类型为考试
        if ($params['room_id']) {
            $createExam['type']       = $modelExam::TYPE_EXAM;
            $createExam['limit_time'] = $params['limit_time'] ?? 0;
        }

        //试卷创建
        $exam = $this->paperCreate($createExam);
        //绑定房间
        if ($params['room_id']) {
            $this->bindRoom([
                'exam_id'    => $newExam['id'],
                'room_id'    => $params['room_id'],
                'account_id' => $params['account_id'],
            ]);
        }

        return $exam;
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
            'room_id'   => 'required',
            'extend'    => 'required',
            'exam_id'   => 'required',
            'answer_id' => 'required',
            //'start_time'=> 'required',
        ]);

        $params['account_id'] = vss_service()->getTokenService()->getAccountId();

        $key = ExamConstant::EXAM_ANSWER . 'lock:' . $params['exam_id'] . '-' . $params['room_id'] . '-' . $params['account_id'];
        if (vss_redis()->lock($key, 90)) {
            $this->fail(ResponseCode::BUSINESS_REPEAT_SUBMIT);
        }

        $exam = $this->getExam($params['exam_id'], $params['room_id']);

        //是否超出提交时间
        $lk = vss_model()->getRoomExamLkModel()->findByRoomIdAndExamId($params['room_id'], $params['exam_id']);
        //超出提交10秒以上不允许提交   给自动提交预留时间
        if ($lk->is_finish && (time() - $lk->finish_time > ExamConstant::SUBMIT_REMAIN_TIME)) {
            $this->fail(ResponseCode::BUSINESS_SUBMIT_FAILED);
        }
        //限时试卷判断是否到结束时间 当前时间大于等于结束时间
        //结束时间 考试发布时间 + 限时时长
        if (!$lk->is_finish && $lk->publish_time && $exam->limit_time) {
            $finishTime = $lk['publish_time'] + $exam['limit_time'];
            if (time() >= $finishTime) {
                $this->examFinish(['room_id' => $params['room_id'], 'exam_id' => $params['exam_id']]);
            }
        }

        //开始结束时间
        if ($params['start_time'] < $lk->publish_time) {
            $params['start_time'] = $lk->publish_time;
        }
        $params['end_time'] = time();
        //加入队列用户加入集合
        $answererSetKey = ExamConstant::EXAM_ANSWER_ACCOUNTIDS . $params['exam_id'];
        vss_redis()->sAdd($answererSetKey, $params['account_id']);
        vss_redis()->expire($answererSetKey, 60 * 30); // 设置过期时间

        vss_queue()->push(new SubmitExamAnswerJob($params));

        return true;
    }

    /**
     * @param $params
     *
     *
     * @author  jin.yang@vhall.com
     * @date    2020-11-20
     */
    public function queueAnswer($params)
    {
        $rule = [
            'room_id'    => 'required',
            'extend'     => '',
            'exam_id'    => 'required',
            'answer_id'  => 'required',
            'start_time' => '',
            'end_time'   => 'required',
            'account_id' => 'required',
        ];
        $data = vss_validator($params, $rule);

        $join_user = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId(
            $params['account_id'],
            $params['room_id']
        );
        empty($join_user) && $this->fail(ResponseCode::AUTH_NOT_PERMISSION);

        if (empty($params['start_time'])) {
            $lk                 = vss_model()->getRoomExamLkModel()->findByRoomIdAndExamId($params['room_id'],
                $params['exam_id']);
            $data['start_time'] = $lk['publish_time'];
        }
        vss_logger()->info('queueAnswerParams', [$params]);
        //处理extend信息
        if (!empty($params['extend'])) {
            $extend         = $this->dealExtend($params['extend']);
            $data['extend'] = $extend;
            $extend_arr     = json_decode($extend, true);
            if (!empty($extend_arr['answer_result']['elect_score'])) {
                $data['elect_score']    = (int)$extend_arr['answer_result']['elect_score'];
                $data['answerer_score'] = $data['elect_score'];
            }
            $exam = $this->getExam($params['exam_id']);
            //判断是否为系统自动批阅
            if ($extend_arr['answer_result']['total_num'] == $exam['question_num']) {
                $data['is_graded']         = 1;
                $data['operator_nickname'] = '自动批阅';
            }
        }
        $createData = [
            'join_id'  => $join_user->join_id,
            'nickname' => $join_user->nickname,
            'avatar'   => $join_user->avatar,
        ];

        $answer = vss_model()->getExamAnswersModel()->create($data + $createData);
        if (!$join_user->is_answered_exam) {
            $join_user->update(['is_answered_exam' => 1]);
        }
        vss_logger()->info('queueAnswer', [$answer]);

        return true;
    }

    /**
     * 批阅试卷
     *
     * @param $params
     *
     *
     */
    public function gradedMark($params)
    {
        vss_validator($params, [
            'answer_id'           => 'required',
            'graded_mark'         => 'required',
            'operator_account_id' => 'required',
            'operator_nickname'   => '',
        ]);

        $answerId = $params['answer_id'];

        $key = ExamConstant::EXAM_GRADED_MARK . 'lock:' . $answerId;
        if (vss_redis()->lock($key, 90)) {
            $this->fail(ResponseCode::BUSINESS_REPEAT_SUBMIT);
        }

        //是否超出提交时间
        $lk = vss_model()->getRoomExamLkModel()->findByRoomIdAndExamId($params['room_id'], $params['exam_id']);
        //批阅完毕后不可以再批阅
        if ($lk['is_grade']) {
            $this->fail(ResponseCode::COMP_EXAM_FINISH_ALREADY);
        }

        $examAnswer = vss_model()->getExamAnswersModel()->find($answerId);

        $mark = $this->dealGradedMark($params['graded_mark']);
        vss_logger()->info('exam_mark', ['mark' => $mark, $mark['grade_score'], $examAnswer->elect_score]);
        $examAnswer->answerer_score      = $mark['grade_score'] + $examAnswer->elect_score;
        $examAnswer->is_graded           = 1;
        $examAnswer->graded_mark         = $params['graded_mark'];
        $examAnswer->operator_account_id = $params['operator_account_id'];
        $examAnswer->operator_nickname   = $params['operator_nickname'] ?? '';
        return $examAnswer->save();
    }

    /**
     * 处理批阅内容
     *
     * @param $mark
     *
     * @return array
     */
    public function dealGradedMark($mark)
    {
        $mark       = json_decode($mark, true);
        $score      = 0;
        $gradeScore = 0;
        foreach ($mark as $v) {
            $score      += $v['score'];
            $gradeScore += $v['gradeScore'];
        }
        $data                = [];
        $data['score']       = $score;
        $data['grade_score'] = $gradeScore;
        return $data;
    }

    /**
     * 批阅信息查看
     *
     * @param $params
     *
     * @return array
     *
     */
    public function gradedMarkInfo($params)
    {
        $validator  = vss_validator($params, [
            'answer_id'  => 'required',
            'exam_id'    => 'required',
            'account_id' => 'required'
        ]);
        $examInfo   = $this->getExam($params['exam_id']);
        $answerInfo = vss_model()->getExamAnswersModel()->findByAnswerId($params['answer_id']);
        //只能创建考试用户及回答者有查询权限
        if ($params['account_id'] != $examInfo['account_id'] && $params['account_id'] != $answerInfo['account_id']) {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }

        $extend                    = json_decode($answerInfo['extend'], true);
        $answer_result             = $extend['answer_result'];
        $answerInfo['right_num']   = $answer_result['right_num'];
        $answerInfo['err_num']     = $answer_result['err_num'];
        $answerInfo['empty_num']   = $answer_result['empty_num'];
        $answerInfo['accuracy']    = $answer_result['accuracy'];
        $answerInfo['answer_time'] = $answerInfo['end_time'] - $answerInfo['start_time'];
        $data                      = [];
        $data['exam']              = $examInfo;
        $data['answer']            = $answerInfo;

        return $data;
    }

    /**
     * 获取试卷及答卷数量
     *
     * @param $params
     *
     * @return mixed
     */
    public function getExamNum($params)
    {
        $params = array_filter(array_map('trim', $params));
        $where  = [];
        if (!empty($params['room_id'])) {
            $validator        = vss_validator($params, [
                'room_id' => '',
            ]);
            $where['room_id'] = $params['room_id'];
        }
        if (!empty($params['account_id'])) {
            $validator           = vss_validator($params, [
                'account_id' => '',
            ]);
            $where['account_id'] = $params['account_id'];
        }
        if (!empty($params['begin_date'])) {
            $where['begin_date'] = $params['begin_date'];
        }
        if (!empty($params['end_date'])) {
            $where['end_date'] = $params['end_date'];
        }

        $res['by_room_num'] = vss_model()->getRoomExamLkModel()->getExamCount($where);

        $res['by_account_num'] = vss_model()->getExamAnswersModel()->getAnswerCount($where);

        return $res;
    }

    /**
     * 取消试卷发布
     *
     * @param $params
     *
     *
     */
    public function cancelPublish($params)
    {
        vss_validator($params, [
            'exam_id' => 'required',
            'room_id' => 'required'
        ]);
        $exam = $this->getExam($params['exam_id'], $params['room_id']);
        empty($exam) && $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $join_user->role_name == 2 && $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        $lk = vss_model()->getRoomExamLkModel()->findByRoomIdAndExamId(
            $params['room_id'],
            $params['exam_id']
        );
        $lk && $lk->update(['publish' => 0]);
        //删除考试中状态
        $this->delGlobalStatus($params['room_id']);
    }

    /**
     * 发布试卷(主播)
     *
     * @param $params
     *
     *
     */
    public function publish($params)
    {
        vss_validator($params, [
            'exam_id' => 'required',
            'room_id' => 'required',
        ]);
        $inExamId = $this->getGlobalStatus($params['room_id']);
        if ($inExamId) {
            $this->fail(ResponseCode::COMP_EXAM_TAKING);
        }
        $exam = $this->getExam($params['exam_id']);
        empty($exam) && $this->fail(ResponseCode::COMP_EXAM_INVALID);
        $res = vss_model()->getRoomsModel()->findByRoomId($params['room_id']);
        empty($res) && $this->fail(ResponseCode::EMPTY_ROOM);

        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $join_user->role_name == 2 && $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        $lk = vss_model()->getRoomExamLkModel()->findByRoomIdAndExamId(
            $params['room_id'],
            $params['exam_id']
        );
        if (empty($lk)) {
            $this->fail(ResponseCode::COMP_EXAM_INVALID);
        }

        // 发布考试，考试信息本地静态化
        $publishTime            = time();
        $extend                 = [];
        $extend['score']        = $exam['score'];
        $extend['question_num'] = $exam['question_num'];
        $extend['limit_time']   = $exam['limit_time'];
        $extend['exam_title']   = $exam['title'];
        $extend['publish_time'] = $publishTime;
        $jsonPath               = vss_service()->getFormService()->writeInfoLocal($exam->exam_id, 'exam', $extend);

        //发布信息修改
        $lk->publish      = 1;
        $lk->publish_time = $publishTime;
        $lk->account_id   = $res['account_id'];
        $lk->update();
        //设置考试中状态
        $this->setGlobalStatus($params['room_id'], $params['exam_id']);

        //记录考试结束时间 用于定时结束
        if ($extend['limit_time'] > 0) {
            vss_queue()->push(new ExamAuthFinishJob($params), $extend['limit_time']);
        }

        //广播信息
        vss_service()->getPaasChannelService()->sendMessage($params['room_id'], [
            'type'         => 'exam_push',
            'exam_id'      => $params['exam_id'],
            'nick_name'    => $join_user->nickname,
            'room_join_id' => $join_user->account_id,
            'room_role'    => $join_user->role_name,
            'info_url'     => $jsonPath
        ]);

        //发问卷发送公告信息
        vss_service()->getPaasChannelService()->sendNotice(
            $params['room_id'],
            $params['exam_id'],
            $join_user->account_id,
            'exam_push'
        );
        //调查问卷上报(向单个用户推送)
        vss_service()->getBigDataService()->requestExamPushParams($params);
    }

    /**
     * 是否提交过试卷
     *
     * @param $params
     *
     * @return bool
     *
     */
    public function checkSurvey($params)
    {
        vss_validator($params, [
            'room_id' => 'required',
            'exam_id' => 'required',
        ]);
        $join_user = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId(
            vss_service()->getTokenService()->getAccountId(),
            $params['room_id']
        );

        $answer = vss_model()->getExamAnswersModel()->where([
            'exam_id' => $params['exam_id'],
            'room_id' => $params['room_id'],
            'join_id' => $join_user->join_id,
        ])->first();

        $lk = vss_model()->getRoomExamLkModel()->findByRoomIdAndExamId(
            $params['room_id'],
            $params['exam_id']
        );
        //用户是否回答
        $data['is_answer'] = empty($answer) ? false : true;
        //考试是否结束
        $data['is_finish'] = $lk['is_finish'] ? true : false;

        return $data;
    }

    /**
     * 回答列表
     *
     * @param $params
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     */
    public function answeredList($params)
    {
        vss_validator($params, [
            'room_id'    => 'required',
            'exam_id'    => 'required',
            'account_id' => '',
            'type'       => '',
            'is_graded'  => '',
            'keyword'    => '',
            'page'       => '',
            'pagesize'   => '',
        ]);
        $page     = $params['page'] ?? 1;
        $pagesize = $params['pagesize'] ?? 10;
        //获取并验证信息
        $examInfo = $this->getExam($params['exam_id'], $params['room_id']);

        $condition                         = [];
        $condition['exam_answers.room_id'] = $params['room_id'];
        $condition['exam_answers.exam_id'] = $params['exam_id'];
        !empty(trim($params['keyword'])) && $condition['keyword'] = $params['keyword'];
        !empty($params['account_id']) && $condition['exam_answers.account_id'] = $params['account_id'];
        isset($params['is_graded']) && $condition['exam_answers.is_graded'] = $params['is_graded'] ? $params['is_graded'] : 0;
        $order = 'exam_answers.created_at';
        //根据分数排序
        if ($params['type'] == 1) {
            $order = 'exam_answers.answerer_score';
        }

        $columns = [
            'exam_answers.answer_id',
            'exam_answers.exam_id',
            'exam_answers.join_id',
            'exam_answers.room_id',
            'exam_answers.created_at',
            'exam_answers.updated_at',
            'exam_answers.account_id',
            'exam_answers.elect_score',
            'exam_answers.answerer_score',
            'exam_answers.is_graded',
            'exam_answers.start_time',
            'exam_answers.end_time',
            'exam_answers.extend',
            'exam_answers.operator_account_id',
            'exam_answers.operator_nickname',
            'room_joins.nickname',
            'room_joins.role_name',
            'room_joins.is_answered_exam'
        ];
        $list    = vss_model()->getExamAnswersModel()->joinRoomJoinsList($condition, $columns, $page, $pagesize,
            'inner', $order);

        $number = ($page - 1) * $pagesize + 1;
        foreach ($list as $k => $v) {
            $extend           = json_decode($v['extend'], true);
            $answer_result    = $extend['answer_result'];
            $v['number']      = $number;
            $v['total_score'] = (int)$examInfo['score'];
            $v['total_num']   = (int)$examInfo['question_num'];
            $v['right_num']   = $answer_result['right_num'];
            $v['err_num']     = $answer_result['err_num'];
            $v['empty_num']   = $answer_result['empty_num'];
            $v['accuracy']    = $answer_result['accuracy'];
            $v['answer_time'] = $v['end_time'] - $v['start_time'];
            unset($v['extend']);
            $number++;
        }

        return $list;
    }

    /**
     * 观众已作答列表
     *
     * @param $params
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     */
    public function answeredExamList($params)
    {
        vss_validator($params, [
            'account_id' => 'required',
            'room_id'    => '',
            'keyword'    => '',
            'page'       => '',
            'pagesize'   => '',
        ]);
        $page     = $params['page'] ?? 1;
        $pagesize = $params['pagesize'] ?? 10;

        $condition                            = [];
        $condition['exam_answers.room_id']    = $params['room_id'];
        $condition['exam_answers.account_id'] = $params['account_id'];
        !empty(trim($params['keyword'])) && $condition['keyword'] = $params['keyword'];
        $columns = [
            'exams.exam_id',
            'exams.title',
            'exams.score',
            'exams.question_num',
            'exams.limit_time',
            'exam_answers.answer_id',
            'exam_answers.room_id',
            'exam_answers.start_time',
            'exam_answers.end_time',
            'exam_answers.elect_score',
            'exam_answers.answerer_score',
            'exam_answers.is_graded',
            'exam_answers.operator_account_id',
            'exam_answers.operator_nickname',
        ];
        $list    = vss_model()->getExamAnswersModel()->joinExamsList($condition, $columns, $page, $pagesize);
        return $list;
    }

    /**
     * 考试结束
     *
     * @param $params
     *
     *
     */
    public function examFinish($params)
    {
        vss_validator($params, [
            'exam_id' => 'required',
            'room_id' => 'required',
        ]);
        $lock = vss_redis()->lock(ExamConstant::LOCK_EXAM_FINISH_SUBMIT . $params['exam_id'], 60);
        if ($lock) {
            return true;
        }
        $exam = $this->getExam($params['exam_id'], $params['room_id']);
        empty($exam) && $this->fail(ResponseCode::COMP_EXAM_INVALID);

        $lk = vss_model()->getRoomExamLkModel()->findByRoomIdAndExamId(
            $params['room_id'],
            $params['exam_id']
        );
        empty($lk) && $this->fail(ResponseCode::COMP_EXAM_INVALID);
        $lk['publish'] != 1 && $this->fail(ResponseCode::COMP_EXAM_NOT_PUBLISH);
        $lk->is_finish   = 1;
        $lk->finish_time = time();
        $lk->update();
        //删除考试中状态
        $this->delGlobalStatus($params['room_id']);

        vss_service()->getPaasChannelService()->sendMessage($params['room_id'], [
            'type'    => 'exam_finish',
            'exam_id' => $params['exam_id'],
        ]);
        //发问卷发送公告信息
        vss_service()->getPaasChannelService()->sendNotice(
            $params['room_id'],
            $params['exam_id'],
            $lk->account_id,
            'exam_finish'
        );
        //调查问卷上报(向单个用户推送)
        vss_service()->getBigDataService()->requestExamPushParams($params);
        return true;
    }

    /**
     * 是否全部批阅检测
     *
     * @param $params
     *
     * @return bool
     *
     */
    public function gradeCheck($params)
    {
        vss_validator($params, [
            'exam_id'    => 'required',
            'room_id'    => 'required',
            'account_id' => 'required',
        ]);

        //1.条件验证
        $lk = vss_model()->getRoomExamLkModel()->findByRoomIdAndExamId(
            $params['room_id'],
            $params['exam_id']
        );
        //1.1操作用户验证
        if ($lk['account_id'] != $params['account_id']) {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }
        //1.2流程验证
        if ($lk['is_finish'] != 1) {
            $this->fail(ResponseCode::COMP_EXAM_TAKING);
        }
        if (time() - $lk['finish_time'] < ExamConstant::SUBMIT_REMAIN_TIME) {
            $this->fail(ResponseCode::COMP_EXAM_COLLECTING);
        }
        //2.判断队列中是否有未处理数据
        $answererSetKey = ExamConstant::EXAM_ANSWER_ACCOUNTIDS . $params['exam_id'];
        //2.1提交数量
        $submitCount = vss_redis()->sCard($answererSetKey);
        //2.2判断是否有未批阅数据
        $where              = [];
        $where['exam_id']   = $params['exam_id'];
        $where['is_graded'] = 1;
        $answerCount        = vss_model()->getExamAnswersModel()->getAnswerCount($where);
        if ($answerCount < $submitCount) {
            return false;
        }
        //返回结果
        return true;
    }

    /**
     * 考试判卷
     *
     * @param $params
     *
     *
     */
    public function setGrade($params)
    {
        vss_validator($params, [
            'exam_id' => 'required',
            'room_id' => 'required',
        ]);
        $exam = $this->getExam($params['exam_id'], $params['room_id']);
        empty($exam) && $this->fail(ResponseCode::COMP_EXAM_INVALID);

        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $join_user->role_name == 2 && $this->fail(ResponseCode::AUTH_NOT_PERMISSION);

        $lk = vss_model()->getRoomExamLkModel()->findByRoomIdAndExamId(
            $params['room_id'],
            $params['exam_id']
        );
        empty($lk) && $this->fail(ResponseCode::COMP_EXAM_INVALID);

        if ($lk['is_finish'] != 1 || $lk['publish'] != 1) {
            $this->fail(ResponseCode::COMP_EXAM_NOT_REVIEW);
        }
        //结束考试和判卷之间有一定的自动提交试卷时间
        if (time() - $lk['finish_time'] < ExamConstant::SUBMIT_REMAIN_TIME) {
            $this->fail(ResponseCode::COMP_EXAM_COLLECTING);
        }

        $lk->update(['is_grade' => 1]);
    }

    /**
     * 考试公布结果
     *
     * @param $params
     *
     *
     */
    public function gradePush($params)
    {
        vss_validator($params, [
            'exam_id' => 'required',
            'room_id' => 'required',
        ]);
        $exam = $this->getExam($params['exam_id'], $params['room_id']);
        empty($exam) && $this->fail(ResponseCode::COMP_EXAM_INVALID);

        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $join_user->role_name == 2 && $this->fail(ResponseCode::AUTH_NOT_PERMISSION);

        $lk = vss_model()->getRoomExamLkModel()->findByRoomIdAndExamId(
            $params['room_id'],
            $params['exam_id']
        );
        empty($lk) && $this->fail(ResponseCode::COMP_EXAM_INVALID);
        if ($lk['publish'] != 1 || $lk['is_finish'] != 1 || $lk['is_grade'] != 1) {
            $this->fail(ResponseCode::COMP_EXAM_NOT_PUBLISH_RESULT);
        }
        $lk->update(['is_push_grade' => 1]);

        vss_service()->getPaasChannelService()->sendMessage($params['room_id'], [
            'type'         => 'exam_push_grade',
            'exam_id'      => $params['exam_id'],
            'nick_name'    => $join_user->nickname,
            'room_join_id' => $join_user->account_id,
            'room_role'    => $join_user->role_name
        ]);
        //发问卷发送公告信息
        vss_service()->getPaasChannelService()->sendNotice(
            $params['room_id'],
            $params['exam_id'],
            $join_user->account_id,
            'exam_push_grade'
        );
        //调查问卷上报(向单个用户推送)
        vss_service()->getBigDataService()->requestExamPushParams($params);
    }

    /**
     * 考试概况
     *
     * @param $params
     *
     * @return array
     *
     */
    public function getStat($params)
    {
        vss_validator($params, [
            'exam_id' => 'required',
            'room_id' => 'required',
        ]);
        $examInfo = $this->getExam($params['exam_id']);
        $roomInfo = vss_model()->getRoomsModel()->findByRoomId($params['room_id']);
        $lk       = vss_model()->getRoomExamLkModel()->findByRoomIdAndExamId($params['room_id'], $params['exam_id']);
        if (empty($lk)) {
            $this->fail(ResponseCode::COMP_EXAM_INVALID);
        }

        //提交数量
        $answererSetKey = ExamConstant::EXAM_ANSWER_ACCOUNTIDS . $params['exam_id'];
        //2.1提交数量
        $submitCount = vss_redis()->sCard($answererSetKey);
        if (empty($submitCount)) {
            $submitCount = vss_model()->getExamAnswersModel()->getAnswerCount(['exam_id' => $params['exam_id']]);
        }
        //未批改 已批改
        $notGradedCount = vss_model()->getExamAnswersModel()->getAnswerCount([
            'exam_id'   => $params['exam_id'],
            'is_graded' => 0
        ]);
        $gradedCount    = vss_model()->getExamAnswersModel()->getAnswerCount([
            'exam_id'   => $params['exam_id'],
            'is_graded' => 1
        ]);
        //平均分 最高分 最低分
        $field     = 'max(answerer_score) as max_score, min(answerer_score) as min_score, avg(answerer_score) as avg_score';
        $scoreInfo = vss_model()->getExamAnswersModel()->selectRaw($field)->where(['exam_id' => $params['exam_id']])->first();
        //数据输出
        $data = [];
        //试卷总分
        $data['exam_id']        = $params['exam_id'];
        $data['title']          = $examInfo['title'];
        $data['room_id']        = $params['room_id'];
        $data['il_id']          = $roomInfo['il_id'];
        $data['subject']        = $roomInfo['subject'];
        $data['score']          = $examInfo['score'];
        $data['avg_score']      = round($scoreInfo['avg_score'], 2);
        $data['max_score']      = $scoreInfo['max_score'];
        $data['min_score']      = $scoreInfo['min_score'];
        $data['limit_time']     = $examInfo['limit_time'];
        $data['submit_count']   = $submitCount;
        $data['graded_count']   = $gradedCount;
        $data['ungraded_count'] = $notGradedCount;
        return $data;
    }

    /**
     * 考试中状态设置
     *
     * @param $room_id
     * @param $examId
     *
     * @return int
     */
    public function setGlobalStatus($room_id, $examId)
    {
        vss_redis()->hset(
            CachePrefixConstant::INTERACT_TOOL_RECORDS . $room_id,
            ExamConstant::HAS_EXAM,
            1
        );
        return (int)vss_redis()->hset(
            CachePrefixConstant::INTERACT_TOOL . $room_id,
            ExamConstant::IN_EXAMING,
            $examId
        );
    }

    /**
     * 获取考试中 试卷id
     *
     * @param $room_id
     *
     * @return int
     */
    public function getGlobalStatus($room_id)
    {
        return (int)vss_redis()->hget(
            CachePrefixConstant::INTERACT_TOOL . $room_id,
            ExamConstant::IN_EXAMING
        );
    }

    public function delGlobalStatus($room_id)
    {
        return (int)vss_redis()->hdel(
            CachePrefixConstant::INTERACT_TOOL . $room_id,
            ExamConstant::IN_EXAMING
        );
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
    public function exportAnswer($params)
    {
        vss_validator($params, [
            'il_id'      => 'required',
            'exam_id'    => 'required',
            'account_id' => 'required',
            'answer_id'  => '',
        ]);
        $ilId      = $params['il_id'];
        $accountId = $params['account_id'];
        $examId    = $params['exam_id'];
        $answerId  = $params['answer_id'];

        $liveInfo = vss_service()->getRoomService()->getInfoByIlId($ilId);
        if (empty($liveInfo)) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }
        $account  = vss_model()->getAccountsModel()->find($accountId);
        $fileName = $account['username'] . '_' . $examId . '_' . 'exam_answer_list' . '_' . date('Ymd_His');
        $params   = [
            'exam_id' => $examId,
            'il_id'   => $ilId,
            'room_id' => $liveInfo['room_id'],
        ];
        $answerId && $params['answer_id'] = $answerId;

        $insert = [
            'export'     => ExamConstant::EXPORT_EXAM_ANSWER,
            'il_id'      => $ilId,
            'account_id' => $accountId,
            'source_id'  => $examId,
            'file_name'  => $fileName,
            'title'      => ['房间ID', '房间名称', '考卷名称', '用户昵称', '用户账号'],
            'params'     => json_encode($params),
            'callback'   => 'exam:getExamAnswerExportData',
        ];
        return vss_model()->getExportModel()->create($insert);
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
    public function getExamAnswerExportData($export, $filePath)
    {
        $params = json_decode($export['params'], true);
        $header = json_decode($export['title'], true);
        $ilId   = $params['il_id'];
        $examId = $params['exam_id'];

        $ext  = 'csv';
        $file = $filePath . $export['file_name'];

        //step1:问卷详细信息
        $question_info = vss_service()->getPaasService()->getFormInfo($examId);
        if (empty($question_info)) {
            throw new \Exception('获取问卷详细信息失败');
        }
        //判断房间ID是否存在
        $liveInfo = vss_model()->getRoomsModel()->getInfoByIlId($ilId);

        //step2:排序-对应问题答案顺序
        $question_ids            = array_column($question_info['detail'], 'id');   //返回ID值
        $question_info['detail'] = array_combine($question_ids, $question_info['detail']);  //将键值指定为ID值
        ksort($question_info['detail']);//按照键名排序
        $this->exportHeader($question_info['detail'], $header);

        //step4:构建问卷答案列表信息--此部分来填充用户的回答信息
        $condition                         = [];
        $condition['exam_answers.exam_id'] = $params['exam_id'];
        $condition['exam_answers.room_id'] = $liveInfo['room_id'];
        $params['answer_id'] && $condition['exam_answers.answer_id'] = $params['answer_id'];

        $columns = [
            'exam_answers.answer_id',
            'exam_answers.exam_id',
            'exam_answers.account_id',
            'exam_answers.nickname',
            'room_joins.username'
        ];
        $rowData = [
            $ilId,
            $liveInfo['subject'],
            $question_info['title'],
        ];
        $page    = 1;
        //写入文件
        $exportProxyService = vss_service()->getExportProxyService()->init($file)->putRow($header);
        while (true) {
            //获取答卷列表
            $answer_list = vss_model()->getExamAnswersModel()->joinRoomJoinsList($condition, $columns, $page, 100,
                'inner', 'exam_answers.answer_id');
            //获取答卷信息
            $export_data = $this->exportData($answer_list, $rowData, $question_info['detail']);
            foreach ($export_data as $val) {
                $exportProxyService->putRow($val);
            }
            if ($answer_list->lastPage() <= $page) {
                break;
            }
            $page++;
        }
        $exportProxyService->close();

        return true;
    }

    /**
     * 导出头
     *
     * @param array $questionDetail
     * @param array $header
     *
     * @return array
     */
    public function exportHeader(array $questionDetail, array &$header = [])
    {
        //step3:补充问题标题头信息--此部分来拼标题
        foreach ($questionDetail as $detail_key => $detail_value) {
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
        $header[] = '提交时间';
        return $header;
    }

    /**
     * @param $answer_list
     * @param $rowData
     * @param $questionDetail
     *
     * @return array
     * @throws \Exception
     */
    public function exportData($answer_list, $rowData, $questionDetail)
    {
        $export_data = [];
        foreach ($answer_list as $answer_value) {
            $row_data   = $rowData;
            $row_data[] = $answer_value['nickname'];    //用户昵称
            $row_data[] = $answer_value['username'];    //用户账号
            //获取单个答卷
            $answer_info = vss_service()->getPaasService()->getAnswerDetail($answer_value['exam_id'],
                $answer_value['answer_id']);
            if (empty($answer_info)) {
                throw new \Exception('获取单个答卷详情失败');
            }

            foreach ($questionDetail as $detail_value) {
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
                                );//不要用","分隔
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
        return $export_data;
    }

    /**
     * 获取试卷及房间详情
     *
     * @param      $exam_id
     * @param null $room_id
     *
     * @return ExamsModel
     *
     */
    public function getExam($exam_id, $room_id = null)
    {
        $exam = vss_model()->getExamsModel()->findByExamId($exam_id);
        empty($exam) && $this->fail(ResponseCode::COMP_EXAM_INVALID);
        if ($room_id) {
            $room = vss_model()->getRoomsModel()->findByRoomId($room_id);
            empty($room) && $this->fail(ResponseCode::EMPTY_ROOM);
            ($room->account_id != $exam->account_id) && $this->fail(ResponseCode::COMP_EXAM_INVALID);
        }
        return $exam;
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

    /**
     * 处理考试回答内容 得出结果
     *
     * @param $extend
     *
     * @return string
     */
    public function dealExtend($extend)
    {
        $answer_content = json_decode($extend, true);
        if (empty($answer_content)) {
            return $extend;
        }
        //准确率
        $accuracy = 0;
        //总分数
        $total_score = 0;
        //得分数
        $elect_score = 0;
        //总题目数
        $total_num = 0;
        //答对问题数
        $right_num = 0;
        //答错问题数
        $err_num = 0;
        //未作答问题数
        $empty_num = 0;

        foreach ($answer_content as $answer) {
            //超出字母表的 不计入准确率和总分数
            if (is_array($answer['correctIndex']) && (min($answer['correctIndex']) < 0 || max($answer['correctIndex']) > 25)) {
                continue;
            }    //多选 违规输入
            if (!is_array($answer['correctIndex']) && ($answer['correctIndex'] < -1 || $answer['correctIndex'] > 25)) {
                continue;
            }    //单选    违规输入

            //计入总数总分  题目
            if (in_array($answer['type'], ['radio', 'checkbox'])) {
                $total_num   += 1;
                $total_score += $answer['score'];
            }
            //数字转字母 匿名函数
            $func = function ($num) {
                return chr($num + 65);
            };
            //回答内容为空
            if (empty($answer['replys'])) {
                $empty_num += 1;
                continue;
            }

            //统计正确答案
            switch ($answer['type']) {
                case 'radio':
                    //判断用户答案正确  没有预设答案 只要回复就计算分数
                    if ($answer['correctIndex'] == -1 || $func($answer['correctIndex']) == $answer['replys']) {
                        $right_num   += 1;
                        $elect_score += $answer['score'];
                    } else {
                        $err_num += 1;
                    }
                    break;
                case 'checkbox':
                    //数字转为对应选项
                    $right_replys = array_map($func, $answer['correctIndex']);
                    //判断用户答案正确
                    if (empty($answer['correctIndex']) || (empty(array_diff(
                                $right_replys,
                                $answer['replys']
                            )) && empty(array_diff($answer['replys'], $right_replys)))
                    ) {
                        $right_num   += 1;
                        $elect_score += $answer['score'];
                    } else {
                        $err_num += 1;
                    }
                    break;
                default:
                    break;
            }
        }
        //计算准确率
        if ($total_num > 0) {
            $accuracy = round($right_num / $total_num * 100, 3);
        }
        //回答结果统计
        $answer_result                = [];
        $answer_result['total_num']   = $total_num;
        $answer_result['right_num']   = $right_num;
        $answer_result['total_score'] = $total_score;
        $answer_result['elect_score'] = $elect_score;
        $answer_result['err_num']     = $err_num;
        $answer_result['empty_num']   = $empty_num;
        $answer_result['accuracy']    = $accuracy;
        $extend_str                   = json_encode(compact('answer_result', 'answer_content'));
        return $extend_str;
    }
}
