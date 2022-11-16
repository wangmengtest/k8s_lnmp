<?php


namespace vhallComponent\vote\jobs;

use Vss\Queue\JobStrategy;

/**
 * 提交投票
 * Class SubmitVoteJob
 * @package vhallComponent\vote\jobs
 */
class SubmitVoteJob extends JobStrategy
{
    protected $answer;

    // 记录投递的任务，监控是否消费完成
    protected $roomVoteAnswerIds = 'queue:vote:';

    public function __construct(array $answer)
    {
        $this->answer = $answer;

        $this->roomVoteAnswerIds .= $answer['room_id'];

        // 记录投递的任务
        vss_redis()->sadd($this->roomVoteAnswerIds, $answer['answer_id']);
    }

    public function handle()
    {
        vss_service()->getVoteService()->queueAnswer($this->answer);

        // 任务消费完移除任务
        vss_redis()->srem($this->roomVoteAnswerIds, $this->answer['answer_id']);
    }

    public function failed(\Throwable $e)
    {
        // 任务消费失败移除任务
        vss_redis()->srem($this->roomVoteAnswerIds, $this->answer['answer_id']);
        parent::failed($e);
    }

    /**
     * 获取任务是否消费完成
     * @auther yaming.feng@vhall.com
     * @date 2021/5/10
     * @param $roomId
     * @return bool
     */
    public static function isFinish($roomId)
    {
        return !vss_redis()->scard("queue:vote:{$roomId}");
    }
}
