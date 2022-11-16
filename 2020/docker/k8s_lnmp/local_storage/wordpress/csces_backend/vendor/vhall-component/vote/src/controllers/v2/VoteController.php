<?php
namespace vhallComponent\vote\controllers\v2;

use vhallComponent\decouple\controllers\BaseController;

/**
 * 投票组件
 * Created by PhpStorm.
 * User: liuxiangliang
 * Date: 2020/3/27
 * Time: 16:15
 */
class VoteController extends BaseController
{
    /**
     * 创建投票
     */
    public function createAction()
    {
        $params = $this->getParam();
        $params['app_id'] = vss_service()->getTokenService()->getAppId();
        unset($params['source_id']);
        $this->success(vss_service()->getVoteService()->create($params));
    }

    /**
     * 投票列表
     */
    public function listAction()
    {
        $this->success(vss_service()->getVoteService()->list($this->getParam()));
    }

    /**
     * 删除投票
     */
    public function deleteAction()
    {
        vss_service()->getVoteService()->delete($this->getParam());
        $this->success();
    }

    /**
     * 修改投票
     */
    public function updateAction()
    {
        vss_service()->getVoteService()->update($this->getParam());
        $this->success();
    }

    /**
     * 投票详情
     */
    public function infoAction()
    {
        $this->success(vss_service()->getVoteService()->info($this->getParam()));
    }

    /**
     * 绑定投票
     */
    public function bindRoomAction()
    {
        vss_service()->getVoteService()->bindRoom($this->getParam());
        $this->success();
    }

    /**
     * 解绑投票
     */
    public function unbindRoomAction()
    {
        vss_service()->getVoteService()->unbindRoom($this->getParam());
        $this->success();
    }

    /**
     * 复制投票
     */
    public function copyAction()
    {
        $params = $this->getParam();
        $params['app_id'] = vss_service()->getTokenService()->getAppId();
        $this->success(vss_service()->getVoteService()->copy($params));
    }

    /**
     * 提交答案
     */
    public function answerAction()
    {
        vss_service()->getVoteService()->answer($this->getParam());
        $this->success();
    }

    /**
     * 取消发布投票
     */
    public function cancelPublishAction()
    {
        vss_service()->getVoteService()->cancelPublish($this->getParam());
        $this->success();
    }

    /**
     * 发布投票 主播端
     */
    public function publishAction()
    {
        vss_service()->getVoteService()->publish($this->getParam());
        $this->success();
    }

    /**
     * 观看端投票列表
     */
    public function watchListAction()
    {
        $params = $this->getParam();
        $params['publish'] = 1;
        $this->success(vss_service()->getVoteService()->list($params));
    }

    /**
     * 是否提交过投票
     */
    public function checkSurveyAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getVoteService()->checkSurvey($params));
    }

    /**
     * 投票结束
     */
    public function voteFinishAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getVoteService()->voteFinish($params));
    }

    /**
     * 投票统计推送
     */
    public function pushStatisAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getVoteService()->pushStatis($params));
    }

    /**
     * 投票结果发布
     */
    public function votePushAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getVoteService()->votePush($params));
    }

    /**
     * 投票计数详情
     */
    public function voteDetailAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getVoteService()->voteDetail($params));
    }

    /**
     * 投票数量信息
     */
    public function getNumAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getVoteService()->getNum($params));
    }
}
