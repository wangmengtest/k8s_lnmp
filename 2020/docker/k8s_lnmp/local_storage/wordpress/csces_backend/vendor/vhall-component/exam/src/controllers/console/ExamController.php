<?php

namespace vhallComponent\exam\controllers\console;

use App\Constants\ResponseCode;
use Illuminate\Support\Arr;
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
        $params   = $this->getParam();
        $examInfo = vss_service()->getExamService()->paperCreate($params);
        $this->success($examInfo);
    }

    /**
     * 试卷-列表
     */
    public function paperListAction()
    {
        $list = vss_service()->getExamService()->paperList($this->getParam());
        $this->success($list);
    }

    /**
     * 试卷-预览
     *
     */
    public function paperInfoAction()
    {
        $info = vss_service()->getExamService()->paperInfo($this->getParam());
        $this->success($info);
    }

    /**
     * 考试详情
     *
     */
    public function infoAction()
    {
        $info = vss_service()->getExamService()->roomExamInfo($this->getParam());
        $this->success($info);
    }

    /**
     * 试卷-复制
     *
     */
    public function paperCopyAction()
    {
        $params = Arr::except($this->getParam(), 'room_id');
        $exam   = vss_service()->getExamService()->copy($params);
        $this->success($exam);
    }

    /**
     * 试卷-绑定
     *
     */
    public function bindPaperAction()
    {
        $params = $this->getParam();
        validator($params, [
            'room_id' => 'required'
        ]);
        $exam = vss_service()->getExamService()->copy($params);
        $this->success($exam);
    }

    /**
     * 考试-创建记录
     *
     * @author ensong.liu@vhall.com
     * @date   2019-05-21 17:04:42
     */
    public function createAction()
    {
        //1、接收参数信息
        $params   = $this->getParam();
        $examInfo = vss_service()->getExamService()->create($params);
        $this->success($examInfo);
    }

    /**
     * 考试-修改
     *
     * @author ensong.liu@vhall.com
     * @date   2019-05-21 17:04:42
     */
    public function updateAction()
    {
        //1、接收参数信息
        $params   = $this->getParam();
        $examInfo = vss_service()->getExamService()->update($params);
        $this->success($examInfo);
    }

    /**
     * 试卷-删除记录
     *
     * @author ensong.liu@vhall.com
     * @date   2019-05-21 17:04:42
     */
    public function deleteAction()
    {

        //1、获取参数信息
        $params = $this->getParam();
        validator($params, [
            'exam_ids' => 'required'
        ]);

        $examIds = $params['exam_ids'];

        //2、获取用户列表
        $deleteRes = [];    //删除房间信息
        $examIdArr = explode(',', $examIds);
        if (!empty($examIdArr)) {//2.1、删除多条记录
            $count = vss_model()->getRoomExamLkModel()
                ->whereIn('exam_id', $examIdArr)
                ->where('publish', '>', 0)
                ->count();
            $count && $this->fail(ResponseCode::COMP_EXAM_NOT_EDIT);
            foreach ($examIdArr as $examId) {
                $data['exam_id']    = $examId;
                $data['account_id'] = $params['account_id'];
                $result             = vss_service()->getExamService()->delete($data);
                if ($result) {
                    $deleteRes[] = $examId;
                }
            }
        }

        //删除成功提示信息
        if ($deleteRes) {
            $this->success($deleteRes);
        }
        //删除失败提示信息
        $this->fail(ResponseCode::BUSINESS_DELETE_FAILED);
    }

    /**
     * 试卷-获取列表
     */
    public function listAction()
    {
        $params = $this->getParam();
        //1、参数列表

        if ($params['room_id']) {
            unset($params['account_id']);
        }

        //2、获取考试数据
        $examList = vss_service()->getExamService()->list($params);
        $this->success($examList);
    }

    /**
     * 批阅列表
     * @return mixed
     *
     */
    public function gradeListAction()
    {
        //不需account_id
        $params = $this->getParam();
        unset($params['account_id']);
        if (!isset($params['is_graded'])) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        $this->success(vss_service()->getExamService()->answeredList($params));
    }

    /**
     * 批阅
     * @return mixed
     *
     */
    public function gradeAction()
    {
        $params                        = $this->getParam();
        $params['operator_account_id'] = $this->accountInfo['account_id'];
        $params['operator_nickname']   = $this->accountInfo['nickname'];
        $this->success(vss_service()->getExamService()->gradedMark($params));
    }

    /**
     * 批阅信息
     * @return mixed
     *
     */
    public function gradeInfoAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getExamService()->gradedMarkInfo($params));
    }

    /**
     * 考试概况
     * @return mixed
     */
    public function statAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getExamService()->getStat($params));
    }

    /**
     * 回答列表
     * @return mixed
     *
     */
    public function answeredListAction()
    {
        //不需account_id
        $params = $this->getParam();
        unset($params['account_id']);
        $this->success(vss_service()->getExamService()->answeredList($params));
    }

    /**
     * 回答导出
     * @return mixed
     */
    public function exportAnswerAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getExamService()->exportAnswer($params));
    }
}
