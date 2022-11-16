<?php
namespace vhallComponent\question\controllers\v2;

use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;

class QuestionController extends BaseController
{
    /**
     * Notes: 根据room_id，question_id获取问卷提交信息
     * User: michael
     * Date: 2019/8/21
     * Time: 19:18
     */
    public function SubmitInfoAction()
    {
        $res = vss_service()->getQuestionService()->getSubmitInfo($this->getParam());
        $this->success($res);
    }

    /**
     * 提交答案
     */
    public function answerAction()
    {
        vss_service()->getQuestionService()->answer($this->getParam());
        $this->success();
    }

    /**
     * 问卷详情
     */
    public function infoAction()
    {
        $this->success(vss_service()->getQuestionService()->info($this->getParam()));
    }

    /**
     * Notes: 根据room_id后去问卷和答卷数量
     * User: michael
     * Date: 2019/8/21
     * Time: 19:18
     */
    public function getNumAction()
    {
        $params['publish'] = $this->getPost('publish', 1);
        $res = vss_service()->getQuestionService()->getQuestionNum($params);
        $this->success($res);
    }

    /**
     * 解绑问卷
     */
    public function unbindRoomAction()
    {
        vss_service()->getQuestionService()->unbindRoom($this->getParam());
        $this->success();
    }

    /**
     * 统计列表
     */
    public function statisticsListAction()
    {
        $this->success(vss_service()->getQuestionService()->statisticsList($this->getParam()));
    }

    /**
     * 删除问卷
     */
    public function deleteAction()
    {
        $ok = vss_service()->getQuestionService()->delete($this->getParam());
        if (!$ok) {
            return $this->fail(ResponseCode::BUSINESS_DELETE_FAILED);
        }
        $this->success();
    }

    /**
     * 问卷列表
     */
    public function listAction()
    {
        $this->success(vss_service()->getQuestionService()->list($this->getParam()));
    }

    /**
     * 取消发布问卷
     */
    public function cancelPublishAction()
    {
        vss_service()->getQuestionService()->cancelPublish($this->getParam());
        $this->success();
    }

    /**
     * 创建问卷
     */
    public function createAction()
    {
        $params = $this->getParam();
        $params['app_id'] = vss_service()->getTokenService()->getAppId();
        unset($params['source_id']);
        $this->success(vss_service()->getQuestionService()->create($params));
    }

    /**
     * 更新问卷
     */
    public function updateAction()
    {
        vss_service()->getQuestionService()->update($this->getParam());
        $this->success();
    }

    /**
     * 发布问卷 主播端
     */
    public function publishAction()
    {
        vss_service()->getQuestionService()->publish($this->getParam());
        $this->success();
    }

    /**
     * 观看端问卷列表
     */
    public function watchListAction()
    {
        $params = $this->getParam();
        $params['publish'] = 1;
        $this->success(vss_service()->getQuestionService()->list($params));
    }

    /**
     * 统计问卷
     */
    public function statAction()
    {
        $this->success(vss_service()->getQuestionService()->getStat($this->getParam()));
    }

    /**
     * 是否提交过问卷
     */
    public function checkSurveyAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getQuestionService()->checkSurvey($params));
    }

    /**
     * 统计列表
     */
    public function accountStatisticsListAction()
    {
        $this->success(vss_service()->getQuestionService()->accountStatisticsList($this->getParam()));
    }

    /**
     * 复制问卷
     */
    public function copyAction()
    {
        $params = $this->getParam();
        $params['app_id'] = vss_service()->getTokenService()->getAppId();
        $this->success(vss_service()->getQuestionService()->copy($params));
    }

    /**
     * 问卷推屏
     */
    public function repushAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getQuestionService()->repush($params));
    }
}
