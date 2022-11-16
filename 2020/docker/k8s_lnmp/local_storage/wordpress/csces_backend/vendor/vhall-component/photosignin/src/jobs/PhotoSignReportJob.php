<?php

namespace vhallComponent\photosignin\jobs;

use vhallComponent\photosignin\constants\PhotoSignConstant;
use Vss\Queue\JobStrategy;

/**
 * 签到用户上报
 * Class PhotoSignReportJob
 * @package vhallComponent\photosignin\jobs
 */
class PhotoSignReportJob extends JobStrategy
{

    protected $data;

    // 保存已上报的的用户id， 防止重复上报
    protected $repostUserKey = "photo_sign:report_user_set:";

    public function __construct(array $data)
    {
        if (!$this->addUser($data['room_id'], $data['sign_id'], $data['user_id'])) {
            return;
        }

        $this->data = $data;
    }

    public function handle()
    {
        if (!$this->data) {
            return;
        }

        $time = microtime(true);
        $this->info($this->data['sign_id'] . '签到上报队列消费开始-queue,开始时间=' . $time, $this->data);

        // 用户拍照签到上报过来的
        if ($this->data['status'] ?? false) {
            vss_service()->getPhotoSignService()->addQueueUser($this->data, true);
        } else {
            vss_service()->getPhotoSignService()->addQueueUser($this->data);
        }

        $this->info('签到上报队列消费成功-queue,耗时' . (microtime(true) - $time), $this->data);
    }

    /**
     * 保存已上报的用户, 如果已存在，则返回 false
     *
     * @param $roomId
     * @param $signId
     * @param $userId
     *
     * @return bool
     * @author fym
     * @since  2021/6/30
     */
    public function addUser($roomId, $signId, $userId): bool
    {
        $this->repostUserKey .= $roomId . ':' . $signId;

        // 保存已上报的用户
        $ok = vss_redis()->sadd($this->repostUserKey, $userId);
        if (!$ok) {
            $this->info($this->data['sign_id'] . '上报用户已存在:' . $userId);
            return false;
        }

        vss_redis()->expire($this->repostUserKey, PhotoSignConstant::SIGN_SHOW_TIME);
        return true;
    }
}
