<?php

namespace vhallComponent\exam\constants;

class ExamConstant
{
    //回答
    const EXAM_ANSWER = 'op:exam:answer:';

    //批阅记录
    const EXAM_GRADED_MARK = 'op:exam:graded:mark:';

    //答卷用户
    const EXAM_ANSWER_ACCOUNTIDS = 'op:exam:answer:account_ids:';

    //考试状态
    const LK_STATUS_NOT_PUBLISH = 1;    //未发布

    const LK_STATUS_PUBLISH = 2;        //已发布

    const LK_STATUS_FINISH = 3;     //已收卷(待批阅)

    const LK_STATUS_GRADED = 4;     //批阅完毕

    const LK_STATUS_PUSH = 5;     //已公布结果

    //考试导出
    const EXPORT_EXAM_ANSWER = 'examAnswer';

    //是否考试中
    const IN_EXAMING = 'in_exam';

    //是否有考试
    const HAS_EXAM = 'has_exam';

    //考试结束锁定提交状态
    const LOCK_EXAM_FINISH_SUBMIT = 'lock_exam_finish_submit_';

    //考试结束队列 redis有序集合 做延时队列
    const QUEUE_EXAM_FINISH_LIST = 'queue:exam:finish:list';

    //考试结束队列执行锁
    const LOCK_EXAM_FINISH_QUEUE_TIME = 'lock_exam_finish_queue';

    //考试结束队列处理间隔时间
    const EXAM_FINISH_BLOCK_TIME = 1;

    //考试队列信息
    const QUEUE_EXAM_SYNC_VISITOR_INFO = 'form_queue_exam_info_';

    //考试结束给自动提交预留的时间 10秒
    const SUBMIT_REMAIN_TIME = 10;
}
