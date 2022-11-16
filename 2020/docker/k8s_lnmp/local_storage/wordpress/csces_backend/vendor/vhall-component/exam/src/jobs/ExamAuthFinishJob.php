<?php
namespace vhallComponent\exam\jobs;

use \Vss\Queue\JobStrategy;

class ExamAuthFinishJob extends JobStrategy
{
    public $exam;

    public function __construct($exam)
    {
        $this->exam = $exam;
    }

    public function handle()
    {
        vss_service()->getExamService()->examFinish($this->exam);
    }
}
