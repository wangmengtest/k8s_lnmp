<?php

namespace vhallComponent\photosignin\controllers\v2;

use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;
use vhallComponent\photosignin\constants\PhotoSignConstant;
use vhallComponent\photosignin\jobs\PhotoSignReportJob;

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
     * 主持人发起签到.
     *
     *
     * @throws \Throwable
     */
    public function addAction()
    {
        $data = $this->getParam();
        vss_validator($data, [
            'room_id' => 'required'
        ]);

        $data['source'] = $this->getParam('source', 'pc');

        $account_id = $this->getParam('third_party_user_id'); //该信息就是account_id

        //该方式获取到的用户信息没有third_party_user_id
        $data['user_id'] = $account_id;

        $res = vss_service()->getPhotoSignService()->add($data);
        if ($res['sign_id']) {
            $this->success(['sign_id' => $res['sign_id'], 'show_time' => PhotoSignConstant::SIGN_SHOW_TIME]);
        }

        $this->fail(ResponseCode::COMP_SIGN_INITIATE_FAILED);
    }

    /**
     * 观看端用户发起签到前的上报接口.
     *
     *
     * @throws \Throwable
     */
    public function reportAction()
    {
        $data = $this->getParam();
        vss_validator($data, [
            'sign_id' => 'required',
            'room_id' => 'required',
            'source'  => 'required',
        ]);

        $account_id = $this->getParam('third_party_user_id'); //该信息就是account_id
        $userInfo   = vss_service()->getPhotoSignService()->getUserInfoById($account_id);
        if (empty($userInfo)) {
            $this->fail(ResponseCode::EMPTY_USER);
        }

        //根据签到id验一下签到任务是否超时 条件sign_id和room_id在任务表是否存在且时间是否到了
        vss_service()->getPhotoSignService()->checkTask($data['sign_id']);

        $pushArr = [
            'sign_id'       => $data['sign_id'],
            'room_id'       => $data['room_id'],
            'user_id'       => $userInfo['account_id'],
            'nickname'      => $userInfo['nickname'],
            'phone'         => $userInfo['phone'],
            'third_user_id' => $userInfo['account_id'],
        ];

        vss_queue()->push(new PhotoSignReportJob($pushArr));

        $this->success([]);
    }

    /**
     * 观看端用户发起签到.
     *
     *
     * @throws \Throwable
     */
    public function signAction()
    {
        $data = $this->getParam();
        vss_validator($data, [
            'sign_id' => 'required',
            'room_id' => 'required',
            'source'  => 'required',
        ]);

        $account_id = $this->getParam('third_party_user_id'); //该信息就是account_id
        $userInfo   = vss_service()->getPhotoSignService()->getUserInfoById($account_id);

        $signTime = vss_service()->getPhotoSignService()->sign($data, $userInfo);

        if (in_array($data['source'], ['android', 'ios'])) {
            $signTime = date('Y/m/d H:i:s', strtotime($signTime));
        }
        $this->success(['sign_time' => $signTime]);
    }

    /**
     * 用户签到检测是否超时以及照片是否达到上线
     *
     *
     * @throws \Throwable
     */
    public function checkAction()
    {
        $data = vss_validator($this->getParam(), [
            'sign_id' => 'required',
        ]);

        $account_id = $this->getParam('third_party_user_id');

        $res = vss_service()->getPhotoSignService()->check($account_id, $data['sign_id']);

        $this->success($res);
    }

    /**
     * 用户签到图片列表接口.
     *
     *
     * @throws \Throwable
     */
    public function imgListAction()
    {
        $data = vss_validator($this->getParam(), [
            'sign_id' => 'required',
        ]);

        $account_id = $this->getParam('third_party_user_id');

        $res = vss_service()->getPhotoSignService()->imgList($account_id, $data['sign_id']);

        $this->success($res);
    }

    /**
     * 签到任务列表.
     *
     *
     * @throws \Throwable
     */
    public function taskListAction()
    {
        $data = $this->getParam();
        vss_validator($data, [
            'room_id' => 'required',
            'source'  => 'required',
        ]);

        $data['page']      = $this->getParam('page', 1);
        $data['page_size'] = $this->getParam('page_size', 10);

        $account_id = $this->getParam('third_party_user_id'); //该信息就是account_id
        $userInfo   = vss_service()->getPhotoSignService()->getUserInfoById($account_id);

        if (1 != $userInfo['account_type']) {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }

        //如果查询时不加user_id,值根据room_id会出现b管理员工(主持人) 可以 查看 a管理员工(主持人)下的签到任务列表
        $res = vss_service()->getPhotoSignService()->taskList(
            $userInfo['account_id'],
            $data['room_id'],
            $data['page'],
            $data['page_size'],
            $data['source']
        );

        $this->success($res);
    }

    /**
     * 某次签到任务详情接口.
     *
     *
     * @throws \Throwable
     */
    public function taskDetailAction()
    {
        $data = vss_validator($this->getParam(), [
            'sign_id'   => 'required',
            'source'    => 'required',
            'status'    => '',
            'nickname'  => '',
            'page'      => '',
            'page_size' => '',
        ]);

        $data['status']    = $this->getParam('status', 0); //0默认未签到，1已签到
        $data['nickname']  = $this->getParam('nickname');
        $data['page']      = $this->getParam('page', 1);
        $data['page_size'] = $this->getParam('page_size', 10);

        $account_id = $this->getParam('third_party_user_id'); //该信息就是account_id
        $userInfo   = vss_service()->getPhotoSignService()->getUserInfoById($account_id);

        if (1 != $userInfo['account_type']) {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }

        $res = vss_service()->getPhotoSignService()->taskDetail(
            $data['status'],
            $data['sign_id'],
            $data['nickname'],
            $data['page'],
            $data['page_size'],
            $data['source']
        );

        $this->success($res);
    }
}
