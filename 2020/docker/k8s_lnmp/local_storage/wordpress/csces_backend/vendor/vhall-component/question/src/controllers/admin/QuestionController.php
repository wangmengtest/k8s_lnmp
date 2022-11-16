<?php
namespace vhallComponent\question\controllers\admin;

use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;

class QuestionController extends BaseController
{
    /**
     * 问卷-信息
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-02-17 00:03:16
     * @method GET
     * @request int question_id 问卷ID
     * @return void
     */
    public function getAction()
    {
        //参数列表
        $questionId = $this->getParam('question_id');

        //问卷信息
        $condition = [
            'question_id' => $questionId,
        ];
        $with = ['account'];
        $questionInfo = vss_model()->getQuestionsModel()->getRow($condition, $with);
        if (empty($questionInfo)) {
            $this->fail(ResponseCode::EMPTY_QUESTION);
        }

        //返回数据
        $data = $questionInfo;
        $this->success($data);
    }

    /**
     * 问卷-列表
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 15:22:10
     * @method GET
     * @request string  keyword             关键字
     * @request string  begin_time          开始日期
     * @request string  end_time            结束日期
     * @request string  is_finish           结束状态 0|1，默认0
     * @request string  begin_finish_time   结束开始日期
     * @request string  end_finish_time     结束结束日期
     * @request int     page                页码
     * @return void
     */
    public function listAction()
    {
        //参数列表
        $keyword = $this->getParam('keyword');
        $beginTime = $this->getParam('begin_time');
        $endTime = $this->getParam('end_time');
        $page = $this->getParam('page');

        //问卷列表
        $data = [
            'keyword'    => $keyword,
            'begin_time' => $beginTime,
            'end_time' => $endTime,
            'page'    => $page,
        ];

        $result =  vss_service()->getQuestionService()->list($data);
        if ($result['data']!==null) {
            foreach ($result['data'] as $key => $datum) {
                $result['data'][$key]['q_id'] = $datum['question_id'];
            }
            $questionList = $result;
        } else {
            $questionList = [];
        }
        $this->success($questionList);
    }

    /**
     * 问卷-删除
     *
     * @author ensong.liu@vhall.com
     * @date   2019-01-29 15:22:10
     * @method GET
     * @return void
     */
    public function deleteAction()
    {
        //参数列表
        $questionIds = $this->getParam('question_ids');
        $data = [
            'question_id' => $questionIds,
            'account_id' => $this->accountInfo['account_id'],
        ];
        // 请求vss
        $result = vss_service()->getQuestionService()->delete($data);

        if (!$result) {
            $this->fail(ResponseCode::COMP_QUESTION_NOT_DELETE);
        }

        //返回数据
        $this->success($result);
    }
}
