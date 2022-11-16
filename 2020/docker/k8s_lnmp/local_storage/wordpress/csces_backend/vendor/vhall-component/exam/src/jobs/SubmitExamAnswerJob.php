<?php

namespace vhallComponent\exam\jobs;

use \Vss\Queue\JobStrategy;

class SubmitExamAnswerJob extends JobStrategy
{
    public $answer;

    public function __construct($answer)
    {
        $this->answer = $answer;
    }

    public function handle()
    {
        vss_service()->getExamService()->queueAnswer($this->answer);
    }
}
