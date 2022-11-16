<?php

namespace vhallComponent\photosignin\jobs;

use Vss\Queue\JobStrategy;

/**
 * 自动结束
 * Class PhotoSignAutoFinishJob
 * @package vhallComponent\photosignin\jobs
 */
class PhotoSignAutoFinishJob extends JobStrategy
{

    /**
     * @var int
     */
    protected $signId;

    public function __construct(int $signId)
    {
        $this->signId = $signId;
    }

    public function handle()
    {
        $time = microtime(true);
        $this->info('签到任务到时结束-queue', ['sign_id' => $this->signId]);

        vss_service()->getPhotoSignService()->updateTask(
            ['id' => $this->signId],
            ['status' => 1, 'updated_at' => date('Y-m-d H:i:s')]
        );

        $this->info('签到任务结束成功-queue,耗时' . (microtime(true) - $time), ['sign_id' => $this->signId]);
    }
}
