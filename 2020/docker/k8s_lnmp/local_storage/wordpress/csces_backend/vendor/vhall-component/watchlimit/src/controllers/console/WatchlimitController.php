<?php

namespace vhallComponent\watchlimit\controllers\console;

use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;
use vhallComponent\common\services\UploadFile;
use vhallComponent\watchlimit\constants\WatchlimitConstant;

/**
 * WatchlimitController extends BaseController
 */
class WatchlimitController extends BaseController
{
    /**
     * 观看限制
     * @return
     * @author
     * @date
     */
    public function limitAction()
    {
        $params      = $this->getParam();
        $validator   = vss_validator($params, [
            'il_id'       => 'required',
            'question_id' => '',
            'limit_type'  => 'required',
        ]);
        $ilId        = $params['il_id'];
        $limit_type  = $params['limit_type'];
        $question_id = $params['question_id'];
        //更新限制类型
        $params   = [
            'limit_type' => $limit_type,
            'il_id'      => $ilId,
        ];
        $liveList = vss_service()->getWatchlimitService()->update($params);
        if ($liveList == false) {
            $this->fail(ResponseCode::BUSINESS_EDIT_FAILED);
        }

        //默认
        if ($liveList['limit_type'] == WatchlimitConstant::ACCOUNT_TYPE_APPROVE) {
            $login = vss_service()->getAccountsService()->visitor($ilId);
            $this->success(['limit_type' => 2]);
        }

        //登录模式
        if ($liveList['limit_type'] == WatchlimitConstant::ACCOUNT_TYPE_LOGIN) {
            $this->success(['limit_type' => 0]);
        }

        //报名的
        if ($liveList['limit_type'] == WatchlimitConstant::ACCOUNT_TYPE_APPEAR) {
            if (!$question_id) {
                $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
            }
            $attributes = ['il_id' => $ilId, 'limit_type' => $limit_type, 'source_id' => $question_id];
            vss_service()->getWatchlimitService()->applyCreate($attributes);
            $this->success(['limit_type' => 1]);
        }

        //白名单
        if ($liveList['limit_type'] == WatchlimitConstant::ACCOUNT_TYPE_WHITE) {
            $this->success(['limit_type' => 3]);
        }
    }

    /**
     * 切换哪个模式
     * @return
     * @author
     * @date
     */
    public function getAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'il_id' => 'required',
        ]);
        $ilId     = $params['il_id'];
        $params   = ['il_id' => $ilId];
        $liveList = vss_service()->getRoomService()->getRow($params);
        //获取报表信息户信息
        $applyInfo = vss_service()->getWatchlimitService()->getApplyorderby($ilId);
        if ($liveList['limit_type'] == WatchlimitConstant::ACCOUNT_TYPE_APPEAR) {
            $info = [
                'limit_type' => $liveList['limit_type'],
                'apply_id'   => $applyInfo['source_id'],
            ];
        } else {
            $info = [
                'limit_type' => $liveList['limit_type'],
            ];
        }
        $this->success($info);
    }

    /**
     * 导出白名单
     * @return
     * @author
     * @date
     */
    public function whiteleadingderiveAction()
    {
        vss_service()->getWatchlimitService()->exportList();
    }

    /**
     * 白名单列表
     * @return
     * @author
     * @date
     */
    public function whitelistAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'il_id' => 'required',
        ]);
        $ilId = $params['il_id'];

        $pageSize = $this->getParam('pagesize', 25);
        $page     = $this->getParam('page', 1);
        $login    = vss_service()->getWatchlimitService()->whiteaccountslist($ilId, $pageSize, $page);
        $this->success($login);
    }

    /**
     * 白名单批量删除
     * @return void
     *
     * @author ensong.liu@vhall.com
     * @date   2019-01-29 16:54:37
     */
    public function whitedelAction()
    {
        $ilId   = $this->getParam('wi_ids');
        $Ids    = explode(',', $ilId);
        $result = vss_service()->getWatchlimitService()->deleteByIds($Ids);
        $this->success($result);
    }

    /**
     * 白名单搜索框
     * @return
     * @author
     * @date
     */
    public function whitesearchlistAction()
    {
        $params     = $this->getParam();
        $validator  = vss_validator($params, [
            'whitename' => 'required',
            'il_id'     => 'required',
        ]);
        $phone      = $params['whitename'];
        $ilId       = $params['il_id'];
        $searchlist = vss_service()->getWatchlimitService()->whiteaccountssearch($phone, $ilId);
        if (empty($searchlist['0']['whitename'])) {
            $this->fail(ResponseCode::EMPTY_USER);
        }
        $this->success($searchlist);
    }

    /**
     * 白名单登录
     * @return
     * @author
     * @date
     */
    public function whiteloginAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'whitename' => 'required',
            'whitepaas' => 'required',
            'il_id'     => 'required',
        ]);
        $phone    = $params['whitename'];
        $password = $params['whitepaas'];
        $il_id    = $params['il_id'];

        $params            = [
            'phone'    => $phone,
            'nickname' => $phone,
            'password' => $password,
            'il_id'    => $il_id,
            'third_user_id' => $il_id . "_" . $phone . '_' . $password
        ];
        $this->accountInfo = vss_service()->getAccountsService()->login($params, 0);
        $this->success($this->accountInfo);
    }

    /**
     * 参与报名表单提交
     * @return
     * @author
     * @date
     */
    public function regapplyAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'phone'       => 'required',
            'question_id' => 'required',
            'answer_id'   => 'required',
            'il_id'       => 'required',
            'code'        => '',
        ]);

        $phone      = $params['phone'];//谁答题的id
        $questionId = $params['question_id'];
        $answerId   = $params['answer_id'];
        $ilId       = $params['il_id'];
        $code       = $params['code'];
        $limitType  = WatchlimitConstant::ACCOUNT_TYPE_APPEAR;

        $info = vss_model()->getApplyModel()->getApplyInfoByIlId($ilId);
        if (empty($info['source_id']) || $info['source_id'] != $questionId) {
            $this->fail(ResponseCode::EMPTY_SIGN_TABLE);
        }

        //需优化为判断是否验证由数据表 code_verify 字段判断
        if (!empty($code) && vss_service()->getCodeService()->checkCode($phone, $code) == false) {
            $this->fail(ResponseCode::AUTH_VERIFICATION_CODE_ERROR);
        }

        // 提交报名信息
        $applyUserInfo = [
            'phone'      => $phone,
            'il_id'      => $ilId,
            'apply_id'   => $questionId,
            'answer_id'  => $answerId,
            'limit_type' => $limitType,
        ];
        vss_service()->getWatchlimitService()->applyUsersCreate($applyUserInfo);

        $this->success(['limit_type' => $applyUserInfo]);
    }

    /**
     *  导入白名单  上传
     *
     * @author ensong.liu@vhall.com
     * @date   2019-05-21 14:50:54
     */
    public function uploadAction()
    {
        $ilId = $this->getParam('il_id');
        if (empty($ilId)) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        $file      = new UploadFile('document');
        $extension = strtolower($file->getClientOriginalExtension());
        if ($file::checkType('exel', strtolower($extension)) === false) {
            $this->fail(ResponseCode::TYPE_INVALID_UPLOAD);
        }
        try {
            $filename = $file->sourceFile;
            // 创建读取对象并加载Excel文件--应该支持多种版本
            $objReader = vss_service()->getUploadService()->getExcelReader($extension);
            $objExcel  = $objReader->load($filename);
            // 默认使用第一个工作簿，并获取行数
            $sheet = $objExcel->getSheet(0);
            $rows  = $sheet->getHighestRow();

            $count = vss_model()->getWhiteAccountsModel()->where('il_id', $ilId)->count();
            if ($count >= 50000) {
                $this->fail(ResponseCode::BUSINESS_WHITE_COUNT_OVERFLOW);
            }
            $allowCount = min(WatchlimitConstant::WHITE_ACCOUNT_MYRIAD - $count, 5000);

            // 遍历，并读取单元格内容
            $errodata = 0;
            for ($i = 1; $i < $rows; $i++) {
                $k        = $i + 1;
                $phone    = $sheet->getCell('A' . $k)->getValue();
                $password = $sheet->getCell('B' . $k)->getValue();
                if (empty($phone) && empty($password)) {
                    continue;
                }
                if (preg_match('/^[\w@.]{1,30}$/', $phone) && preg_match('/^[A-Za-z0-9@_.]{1,30}$/', $password)) {
                    $data[$phone] = [
                        'whitename'  => $phone,
                        'whitepaas'  => $password,
                        'limit_type' => 3,
                        'il_id'      => $ilId
                    ];
                    $phoneArr[]   = $phone;
                } else {
                    $errodata++;
                }
            }
            // 关闭
            $objExcel->disconnectWorksheets();
            unset($objExcel);   //释放资源

            $correcttotaldata = count($data);
            if ($correcttotaldata > $allowCount) {
                $this->fail(ResponseCode::BUSINESS_WHITE_COUNT_OVERFLOW);
            }
            if (empty($correcttotaldata)) {
                $this->fail(ResponseCode::EMPTY_INFO);
            }

            $account_list = array_chunk($data, 1000);
            $updataIds    = [];
            foreach ($account_list as $item) {
                $tempArr = array_column($item, 'whitename');
                $lists   = vss_model()->getWhiteAccountsModel()->where('il_id', '=', $ilId)->whereIn('whitename',
                    $tempArr)->get(['whitename', 'whitepaas', 'limit_type', 'il_id', 'id'])->toArray();
                if (empty($lists)) {
                    $lists = [];
                }
                $updateData = array_column($lists, 'id');
                $updataIds  = array_merge($updataIds, $updateData);
            }

            vss_model()->getWhiteAccountsModel()->getConnection()->beginTransaction();
            $repeat = count($updataIds);
            vss_service()->getWatchlimitService()->deleteByIds($updataIds);

            foreach ($account_list as $item) {
                vss_model()->getWhiteAccountsModel()->insert($item);
            }

            vss_model()->getWhiteAccountsModel()->getConnection()->commit();

            $dataarray = [
                'correctdata' => $correcttotaldata,
                'errordata'   => $errodata,
                'repeat'      => $repeat,
            ];
        } catch (\Exception $e) {
            vss_model()->getWhiteAccountsModel()->getConnection()->rollBack();
            throw $e;
        }

        $this->success($dataarray);
    }

    public function get_array_diff_list($arr1, $arr2, $pk = 'whitename')
    {
        try {
            $insertData = [];
            foreach ($arr2 as $item) {
                $tmpArr[$item[$pk]] = $item;
            }
            foreach ($arr1 as $v) {
                if (!isset($tmpArr[$v[$pk]])) {
                    $insertData[] = $v;
                }
            }
            return $insertData;
        } catch (\Exception $e) {
            return $arr1;
        }
    }
}
