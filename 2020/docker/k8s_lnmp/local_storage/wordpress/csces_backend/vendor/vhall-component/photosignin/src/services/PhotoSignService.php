<?php

namespace vhallComponent\photosignin\services;

use App\Constants\ResponseCode;
use vhallComponent\photosignin\constants\PhotoSignConstant;
use vhallComponent\photosignin\jobs\PhotoSignAutoFinishJob;
use vhallComponent\photosignin\jobs\PhotoSignJob;
use Vss\Common\Services\WebBaseService;

/**
 * PhotoSignService.
 *
 * @uses    wangguangli
 * @date    2021-06-18
 *
 * @author  wangguangli <guangli.wang@vhall.com>
 * @license PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class PhotoSignService extends WebBaseService
{

    /**
     * 主持人发起签到.
     */
    public function add($data)
    {
        $nowtime = time();
        $arr     = [
            'user_id'    => $data['user_id'],
            'room_id'    => $data['room_id'],
            'source'     => $data['source'],
            'show_time'  => PhotoSignConstant::SIGN_SHOW_TIME,
            'begin_time' => $nowtime,
        ];

        //插入新增 如果使用create方法且不用维护created_at,updated_at
        $signId = vss_model()->getPhotoSignTaskModel()->create($arr)->id;
        if ($signId) { //签到任务生成成功后，开始广播签到消息
            vss_service()->getPaasChannelService()->sendMessage($data['room_id'], [
                'type'            => 'photo_signin_push',
                'room_id'         => $data['room_id'],
                'sign_id'         => $signId,
                'max_photo_count' => PhotoSignConstant::SIGN_MAX_IMG_NUM,
                'sign_show_time'  => PhotoSignConstant::SIGN_SHOW_TIME,
            ]);

            vss_queue()->push(new PhotoSignAutoFinishJob($signId), PhotoSignConstant::SIGN_SHOW_TIME);

            return ['sign_id' => $signId, 'begin_time' => $nowtime];
        }

        return ['sign_id' => 0, 'begin_time' => 0];
    }

    /**
     * 观看端用户进行照片签到.
     */
    public function sign($data, $userInfo)
    {
        $nowtime = time();
        $this->checkTask($data['sign_id'], $userInfo['account_id']);

        $arr = [
            'sign_id'       => $data['sign_id'],
            'user_id'       => $userInfo['account_id'],
            'third_user_id' => $userInfo['third_party_user_id'] ?? $userInfo['account_id'],
            'nickname'      => $userInfo['nickname'],
            'phone'         => $userInfo['phone'],
            'room_id'       => $data['room_id'],
            'source'        => $data['source'],
            'status'        => 1,
            'sign_time'     => $nowtime,
        ];

        vss_queue()->push(new PhotoSignJob($arr));

        $imgUrl = vss_service()->getUploadService()->uploadImg('image', '', $data['ext'] ?? '');

        $imgArr = [
            'user_id' => $userInfo['account_id'],
            'sign_id' => $data['sign_id'],
            'img_url' => $imgUrl ?: '',
        ];

        $ok = vss_model()->getPhotoSignImgModel()->create($imgArr);
        if ($ok) {
            return date('Y-m-d H:i:s', $nowtime);
        }

        $this->fail(ResponseCode::BUSINESS_UPLOAD_FAILED);
    }

    /**
     * 检测用户签到是否超时以及上传照片是否达到上线
     */
    public function check($user_id, $sign_id)
    {
        $arr = [
            'show_time'       => 0,
            'max_photo_count' => PhotoSignConstant::SIGN_MAX_IMG_NUM,
            'img_list'        => []
        ]; //定义默认返回数组

        $imgList = vss_model()->getPhotoSignImgModel()->where([
            'user_id' => $user_id,
            'sign_id' => $sign_id
        ])->get(['img_url'])->toArray();
        if ($imgList) {
            foreach ($imgList as $val) {
                $arr['img_list'][] = $val['img_url'];
            }
        }

        $signTaskInfo = vss_model()->getPhotoSignTaskModel()->select([
            'begin_time',
            'show_time'
        ])->where(['id' => $sign_id])->first();
        if ($signTaskInfo) {
            $end_time = $signTaskInfo['begin_time'] + $signTaskInfo['show_time'];
            if ($end_time > time()) {
                $arr['show_time'] = $end_time - time();
            }
        }

        return $arr;
    }

    /**
     * 检测签到任务是否存在，以及是否到时间.
     */
    public function checkTask($sign_id = 0, $user_id = 0)
    {
        $info = vss_model()->getPhotoSignTaskModel()->select(['*'])->where(['id' => $sign_id])->first();
        if (empty($info)) {
            $this->fail(ResponseCode::EMPTY_SIGN_TASK);
        }

        $end_time = $info['begin_time'] + $info['show_time'];
        if ($end_time < time()) {//倒计时时间到
            $this->fail(ResponseCode::COMP_SIGN_FINISH);
        }

        $imgNum = vss_model()->getPhotoSignImgModel()->where(['user_id' => $user_id, 'sign_id' => $sign_id])->count();
        if ($imgNum >= 5) {
            $this->fail(ResponseCode::COMP_PHOTO_SIGN_IMG_OVERFlOW);
        }
    }

    /**
     * 用户签到图片列表接口.
     */
    public function imgList($user_id, $sign_id)
    {
        $arr = ['max_photo_count' => PhotoSignConstant::SIGN_MAX_IMG_NUM, 'img_list' => []]; //定义默认返回数组

        $imgList = vss_model()->getPhotoSignImgModel()
            ->where(['user_id' => $user_id, 'sign_id' => $sign_id])
            ->orderByDesc('id')
            ->get(['img_url', 'id', 'created_at'])
            ->toArray();

        if ($imgList) {
            foreach ($imgList as $val) {
                $arr['img_list'][] = [
                    'id'          => $val['id'],
                    'img_url'     => $val['img_url'],
                    'create_time' => $val['created_at']
                ];
            }
        }

        return $arr;
    }

    /**
     * 签到任务列表.
     */
    public function taskList($user_id, $room_id, $page, $page_size, $source = '')
    {
        $condition = [
            'user_id' => $user_id,
            'room_id' => $room_id,
        ];

        $columns = ['id', 'status', 'begin_time'];
        return vss_model()->getPhotoSignTaskModel()->taskList($condition, $page, $page_size, $columns, $source);
    }

    /**
     * 签到任务详情.
     */
    public function taskDetail($status, $sign_id, $nickname, $page, $page_size, $source = '')
    {
        $condition = [
            'sign_id'  => (int)$sign_id,
            'status'   => (int)$status,
            'nickname' => $nickname,
        ];

        $columns = ['id', 'user_id', 'nickname', 'username', 'phone', 'status', 'sign_time', 'sign_id'];
        $list    = vss_model()->getPhotoSignRecordModel()->taskDetail($condition, $page, $page_size, $columns, $source);

        $noSignNum           = vss_model()->getPhotoSignRecordModel()->where([
            'sign_id' => $sign_id,
            'status'  => 0
        ])->count();
        $signNum             = vss_model()->getPhotoSignRecordModel()->where([
            'sign_id' => $sign_id,
            'status'  => 1
        ])->count();
        $list['no_sign_num'] = $noSignNum ?: 0;
        $list['sign_num']    = $signNum ?: 0;

        return $list;
    }

    //签到任务对应的导出任务列表
    public function exportList($user_id, $export, $page, $page_size)
    {
        $condition           = [
            'account_id' => $user_id,
            'export'     => $export,
        ];
        $data                = vss_model()->getExportModel()->setPerPage($page_size)->getList(
            $condition,
            [],
            $page
        )->toArray();
        $condition['status'] = 1;
        $data['is_export']   = 4;
        $waitData            = vss_model()->getExportModel()->where($condition)->count();
        if (0 === $waitData) {
            $data['is_export'] = 3;
        }

        return $data;
    }

    //导出任务创建
    public function exportCreate($fileName, $accountId, $params)
    {
        $params['app_id'] = '';
        $title            = [
            '签到ID',
            '用户ID',
            '用户昵称',
            '用户账号',
            '状态',
            '签到时间',
            '图一',
            '图二',
            '图三',
            '图四',
            '图五',
        ];

        $insert = [
            'export'     => 'photo-sign',
            'il_id'      => $params['il_id'] ?? 0,
            'account_id' => $accountId,
            'source_id'  => 13, //ExportConstant::SOURCE_LIVE_ADMIN,
            'file_name'  => $fileName,
            'title'      => json_encode($title),
            'params'     => json_encode($params),
            'callback'   => 'PhotoSign:getSignExportData',
        ];

        return vss_model()->getExportModel()->insert([$insert]);
    }

    //导出数据
    public function getSignExportData($export, $filePath)
    {
        set_time_limit(0);
        $params = json_decode($export['params'], true);
        $header = json_decode($export['title'], true);
        $file   = $filePath . $export['file_name'] . '.' . $export['ext'];

        // 根据 id 做分页查询
        $exportData          = [];
        $params['page']      = 1;
        $params['page_size'] = 1000;
        $params['version']   = 'v2';

        $exportProxyService = vss_service()->getExportProxyService()->init($file)->putRow($header);
        while (true) {
            //当前page下列表数据
            $condition = [
                'sign_id' => (int)$params['sign_id'],
            ];

            $columns = ['id', 'user_id', 'nickname', 'phone', 'status', 'sign_time', 'sign_id'];
            $list    = vss_model()->getPhotoSignRecordModel()->taskDetailTotal(
                $condition,
                $params['page'],
                $params['page_size'],
                $columns
            );
            if ($list['data']) {
                foreach ($list['data'] as $row) {
                    $row['status'] = $row['status'] ? '已签到' : '未签到';
                    $this->addExportDataRow($exportData, $row);
                }

                $exportProxyService->putRows($exportData);
                $exportData = [];
            } else {
                break;
            }

            ++$params['page'];
        }

        $exportProxyService->close();
        return true;
    }

    private function addExportDataRow(&$exportData, $row)
    {
        //设置Excel行数据
        $exportData[] = [
            $row['sign_id'],
            $row['user_id'],
            $row['nickname'],
            $row['phone'],
            $row['status'],
            $row['sign_time'],
            $row['img1'],
            $row['img2'],
            $row['img3'],
            $row['img4'],
            $row['img5'],
        ];
    }

    public function getCountByCondition($where = [])
    {
        return vss_model()->getExportModel()->where($where)->count();
    }

    //添加队列用户
    public function addQueueUser($data, $sign = false)
    {
        $arr = [
            'sign_id'       => $data['sign_id'],
            'user_id'       => $data['user_id'],
            'third_user_id' => $data['third_user_id'],
            'nickname'      => $data['nickname'],
            'phone'         => $data['phone'],
            'room_id'       => $data['room_id'],
        ];

        if ($sign) {//说明签到了
            $arr['status']    = 1;
            $arr['source']    = $data['source'];
            $arr['sign_time'] = $data['sign_time'];
        }

        return vss_model()->getPhotoSignRecordModel()->create($arr)->id;
    }

    public function updateQueueUser($data)
    {
        $where = ['sign_id' => $data['sign_id'], 'user_id' => $data['user_id']];
        return vss_model()->getPhotoSignRecordModel()->where($where)->update([
            'status'     => 1,
            'sign_time'  => $data['sign_time'],
            'updated_at' => date('Y-m-d H:i:s', time())
        ]);
    }

    public function updateTask($where, $data)
    {
        return vss_model()->getPhotoSignTaskModel()->where($where)->update($data);
    }

    public function getUserInfoById($account_id = 0)
    {
        $account = vss_model()->getAccountsModel()->where(['account_id' => $account_id])->first();
        if (!empty($account)) {
            return $account->toArray();
        }

        return $account;
    }
}
