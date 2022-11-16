<?php

namespace vhallComponent\photosignin\controllers\console;

use App\Constants\ResponseCode;
use Exception;
use vhallComponent\decouple\controllers\BaseController;
use vhallComponent\photosignin\constants\PhotoSignConstant;

/**
 * PhotosignController extends BaseController.
 *
 * @uses    wangguangli
 * @date    2021-06-18
 *
 * @author  wangguangli <guangli.wang@vhall.com>
 * @license PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class PhotosignController extends BaseController
{

    /**
     * Notes:签到任务列表.
     *
     *
     * @throws Exception
     */
    public function taskListAction()
    {
        vss_validator($this->getParam(), [
            'room_id' => 'required',
            'source'  => 'required',
        ]);

        $data              = $this->getParam();
        $data['page']      = $this->getParam('page', 1);
        $data['page_size'] = $this->getParam('page_size', 10);

        $userInfo = $this->accountInfo;
        if (1 != $userInfo['account_type']) {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }

        //如果查询时不加user_id,值根据room_id会出现b管理员工(主持人) 可以 查看 a管理员工(主持人)下的签到任务列表
        $res = vss_service()->getPhotoSignService()->taskList(
            $userInfo['account_id'],
            $data['room_id'],
            $data['page'],
            $data['page_size']
        );

        $this->success($res);
    }

    /**
     * Notes:某个签到任务导出任务列表.
     *
     *
     * @throws Exception
     */
    public function exportListAction()
    {
        $data = $this->getParam();
        vss_validator($data, [
            'sign_id' => 'required',
            'source'  => 'required',
        ]);

        $data['page']      = $this->getParam('page', 1);
        $data['page_size'] = $this->getParam('page_size', 10);

        $res = vss_service()->getPhotoSignService()->exportList(
            $this->accountInfo['account_id'],
            PhotoSignConstant::EXPORT_TYPE,
            $data['page'],
            $data['page_size']
        );

        $this->success($res);
    }

    /**
     * Notes:创建导出任务
     *
     *
     * @throws Exception
     */
    public function exportCreateAction()
    {
        $data = $this->getParam();
        vss_validator($data, [
            'sign_id' => 'required',
            'source'  => 'required',
        ]);

        $fileName = '照片签到' . date('YmdHis');

        $res = vss_service()->getPhotoSignService()->exportCreate($fileName, $this->accountInfo['account_id'], $data);

        $this->success($res);
    }
}
