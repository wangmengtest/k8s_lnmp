<?php


namespace vhallComponent\question\jobs;

use Vss\Queue\JobStrategy;

/**
 * 提交问卷
 * Class SubmitQuestionJob
 * @package question\src\jobs
 */
class SubmitQuestionJob extends JobStrategy
{
    protected $answer;

    public function __construct(array $answer)
    {
        $this->answer = $answer;
    }

    public function handle()
    {
        vss_service()->getQuestionService()->queueAnswer($this->answer);
    }
}
