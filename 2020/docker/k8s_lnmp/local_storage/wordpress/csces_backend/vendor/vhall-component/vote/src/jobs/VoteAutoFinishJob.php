<?php

namespace vhallComponent\vote\jobs;

use vhallComponent\room\constants\CachePrefixConstant;
use vhallComponent\vote\constants\VoteConstant;
use Vss\Queue\JobStrategy;

/**
 * 投票定时结束
 * Class VoteAutoFInishJob
 * @package vhallComponent\vote\jobs
 */
class VoteAutoFinishJob extends JobStrategy
{
    protected $roomVoteLkId;

    public function __construct(int $roomVoteLkId)
    {
        $this->roomVoteLkId = $roomVoteLkId;
    }

    public function handle()
    {
        $roomVoteLkModel = vss_model()->getRoomVoteLkModel();
        $condition       = ['id' => $this->roomVoteLkId];
        $voteLk          = $roomVoteLkModel->getRoomVoteLkInfo($condition);

        if (empty($voteLk) || $voteLk['is_finish']) {
            return;
        }

        $update = ['is_finish' => 1];
        $result = $roomVoteLkModel->updateRoomVoteLk($update, $condition);

        if ($result) {
            $roomId = $voteLk['room_id'];
            $voteId = $voteLk['vote_id'];

            vss_service()->getVoteService()->delRunningVoteIdCache($roomId, $voteId);

            // 删除缓存
            $roomVoteLkModel->deleteCache('InfoByRoomIdAndVoteId', $roomId . 'and' . $voteId);

            // 广播消息
            vss_service()->getPaasChannelService()->sendMessage($roomId, [
                'type'    => 'vote_finish',
                'vote_id' => $voteId
            ]);

            // 发送投票完成公告
            vss_service()->getPaasChannelService()->sendNotice($roomId, $voteId, 0, 'vote_finish');
        }
    }
}
