<?php

namespace vhallComponent\exam\controllers\v2;

use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;

/**
 * 试卷组件
 * Created by PhpStorm.
 * User: liuxiangliang
 * Date: 2020/3/23
 * Time: 17:41
 */
class ExamController extends BaseController
{
    /**
     * 试卷-创建、编辑
     */
    public function paperCreateAction()
    {
        $params               = $this->getParam();
        $params['account_id'] = $params['third_party_user_id'];
        $examInfo             = vss_service()->getExamService()->paperCreate($params);
        $this->success($examInfo);
    }

    /**
     * 试卷-列表
     */
    public function paperListAction()
    {
        $params               = $this->getParam();
        $params['account_id'] = $params['third_party_user_id'];
        $list                 = vss_service()->getExamService()->paperList($params);
        $this->success($list);
    }

    /**
     * 试卷-绑定
     *
     */
    public function bindPaperAction()
    {
        $params = $this->getParam();
        if (empty($params['room_id'])) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        $params['account_id'] = $params['third_party_user_id'];
        $exam                 = vss_service()->getExamService()->copy($params);
        $this->success($exam);
    }

    /**
     * 创建试卷
     */
    public function createAction()
    {
        $params               = $this->getParam();
        $params['app_id']     = vss_service()->getTokenService()->getAppId();
        $params['account_id'] = $params['third_party_user_id'];
        $this->success(vss_service()->getExamService()->create($params));
    }

    /**
     * 考试修改
     *
     */
    public function updateAction()
    {
        $params               = $this->getParam();
        $params['app_id']     = vss_service()->getTokenService()->getAppId();
        $params['account_id'] = $params['third_party_user_id'];
        $this->success(vss_service()->getExamService()->update($params));
    }

    /**
     * 试卷列表
     */
    public function listAction()
    {
        $this->success(vss_service()->getExamService()->list($this->getParam()));
    }

    /**
     * 删除试卷
     */
    public function deleteAction()
    {
        vss_service()->getExamService()->delete($this->getParam());
        $this->success();
    }

    /**
     * 试卷详情
     */
    public function paperInfoAction()
    {
        $params               = $this->getParam();
        $params['account_id'] = $params['third_party_user_id'];
        $this->success(vss_service()->getExamService()->paperInfo($params));
    }

    /**
     * 试卷详情
     */
    public function infoAction()
    {
        $params               = $this->getParam();
        $params['account_id'] = $params['third_party_user_id'];
        $this->success(vss_service()->getExamService()->roomExamInfo($params));
    }

    /**
     * 房间试卷详情
     */
    public function publishInfoAction()
    {
        $this->success(vss_service()->getExamService()->publishInfo($this->getParam()));
    }

    /**
     * 复制试卷
     */
    public function copyAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getExamService()->copy($params));
    }

    /**
     * 提交答案
     */
    public function answerAction()
    {
        vss_service()->getExamService()->answer($this->getParam());
        $this->success();
    }

    /**
     * 根据room_id获取试卷数量
     */
    public function getNumAction()
    {
        $res = vss_service()->getExamService()->getExamNum($this->getParam());
        $this->success($res);
    }

    /**
     * 取消发布试卷
     */
    public function cancelPublishAction()
    {
        vss_service()->getExamService()->cancelPublish($this->getParam());
        $this->success();
    }

    /**
     * 发布试卷 主播端
     */
    public function publishAction()
    {
        vss_service()->getExamService()->publish($this->getParam());
        $this->success();
    }

    /**
     * 观看端试卷列表
     */
    public function watchListAction()
    {
        $params = $this->getParam();
        if (!isset($params['answer_account_id'])) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        $params['publish'] = 1;
        $this->success(vss_service()->getExamService()->list($params));
    }

    /**
     * 是否提交过试卷
     */
    public function checkSurveyAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getExamService()->checkSurvey($params));
    }

    /**
     * 答卷列表
     */
    public function answeredListAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getExamService()->answeredList($params));
    }

    /**
     * 观众已作答列表
     *
     */
    public function answeredExamAction()
    {
        $params               = $this->getParam();
        $params['account_id'] = $params['third_party_user_id'];
        $this->success(vss_service()->getExamService()->answeredExamList($params));
    }

    /**
     * 批阅列表
     *
     */
    public function gradeListAction()
    {
        $params = $this->getParam();
        if (!isset($params['is_graded'])) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        $this->success(vss_service()->getExamService()->answeredList($params));
    }

    /**
     * 考试结束
     */
    public function examFinishAction()
    {
        $params = $this->getParam();
        vss_service()->getExamService()->examFinish($params);
        $this->success();
    }

    /**
     * 考试判卷
     */
    public function setGradeAction()
    {
        $params = $this->getParam();
        vss_service()->getExamService()->setGrade($params);
        $this->success();
    }

    /**
     * 考试成绩发布
     */
    public function gradePushAction()
    {
        $params = $this->getParam();
        vss_service()->getExamService()->gradePush($params);
        $this->success();
    }

    /**
     * 批阅
     *
     */
    public function gradeAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getExamService()->gradedMark($params));
    }

    /**
     * 批阅信息
     *
     */
    public function gradeInfoAction()
    {
        $params               = $this->getParam();
        $params['account_id'] = $params['third_party_user_id'];
        $this->success(vss_service()->getExamService()->gradedMarkInfo($params));
    }

    /**
     * 是否全部批阅检测
     */
    public function gradeCheckAction()
    {
        $params               = $this->getParam();
        $params['account_id'] = $params['third_party_user_id'];
        $this->success(vss_service()->getExamService()->gradeCheck($params));
    }

    /**
     * 考试概况
     */
    public function statAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getExamService()->getStat($params));
    }

    /**
     * 回答导出
     */
    public function exportAnswerAction()
    {
        $params               = $this->getParam();
        $params['account_id'] = $params['third_party_user_id'];
        $this->success(vss_service()->getExamService()->exportAnswer($params));
    }
}
