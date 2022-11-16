<?php


namespace vhallComponent\vote\constants;

class VoteConstant
{
    //投票
    const VOTE_RVLK = 'op:vote:rvlk:';

    const VOTE_QUESTION = 'op:vote:question:';

    const VOTE_ANSWER = 'op:vote:answer:';

    const USLEEP_TIME = 1000;

    //是否发布 1-是 0-否
    const PUBLISH_YES = 1;

    const PUBLISH_NO = 0;

    //是否结束 1-是 0-否
    const FINISH_YES = 1;

    const FINISH_NO = 0;

    //  记录正在进行中的投票 ID,
    const INTERACT_TOOL_FILED = 'vote_id';

    // 导出类型
    const EXPORT_VOTE = 'vote';

}
