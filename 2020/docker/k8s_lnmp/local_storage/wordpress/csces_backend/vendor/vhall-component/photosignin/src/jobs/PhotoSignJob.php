<?php

namespace vhallComponent\photosignin\jobs;

use vhallComponent\photosignin\constants\PhotoSignConstant;
use Vss\Queue\JobStrategy;

/**
 * 用户签到队列
 * Class PhotoSignJob
 * @package vhallComponent\photosignin\jobs
 */
class PhotoSignJob extends JobStrategy
{

    /**
     * @var array
     */
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        $time = microtime(true);
        $this->info($this->data['sign_id'] . '签到队列消费开始-queue,开始时间=' . $time, $this->data);

        if ($this->data['status'] ?? false) {
            vss_service()->getPhotoSignService()->updateQueueUser($this->data);
        }

        $this->info('签到队列消费成功-queue,耗时' . (microtime(true) - $time), $this->data);

    }
}
