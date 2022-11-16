<?php
/**
 * Created by PhpStorm.
 * User: liuxiangliang
 * Date: 2020/3/27
 * Time: 16:25
 */

namespace vhallComponent\vote\services;

use App\Constants\ResponseCode;
use vhallComponent\vote\jobs\SubmitVoteJob;
use vhallComponent\vote\jobs\VoteAutoFinishJob;
use Vss\Utils\DateUtil;
use Illuminate\Support\Arr;
use vhallComponent\room\constants\CachePrefixConstant;
use vhallComponent\vote\constants\VoteConstant;
use vhallComponent\vote\models\VotesModel;
use Vss\Common\Services\WebBaseService;

class VoteService extends WebBaseService
{
    /**
     * 投票-投票创建
     *
     * @param $params
     *
     * @return mixed
     *
     */
    public function create($params)
    {
        $rule = [
            'room_id'    => '',
            'title'      => 'required',
            'extend'     => '',
            'vote_id'    => 'required',
            'account_id' => 'required',
            'is_public'  => '',
            'source_id'  => '',
            'limit_time' => '',
            'option_num' => 'required',
            'app_id'     => 'required'
        ];
        $data = vss_validator($params, $rule);

        try {
            vss_model()->getVotesModel()->getConnection()->beginTransaction();
            //创建
            unset($data['room_id']);
            $vote = vss_model()->getVotesModel()->updateOrCreate(['vote_id' => $params['vote_id']], $data);

            $params['room_id'] && $this->bindRoom([
                'vote_id' => $vote->vote_id,
                'room_id' => $params['room_id']
            ]);
            vss_model()->getVotesModel()->getConnection()->commit();
            return $vote;
        } catch (\Exception $e) {
            vss_model()->getVotesModel()->getConnection()->rollBack();
            $this->fail(ResponseCode::BUSINESS_CREATE_FAILED);
        }
    }

    /**
     * 投票-获取投票列表
     *
     * @param $params
     *
     * @return mixed
     *
     */
    public function list($params)
    {
        vss_validator($params, [
            'room_id'      => ['required_without:account_id', 'required_if:type,2'],
            'account_id'   => ['required_without:room_id'],
            'keyword'      => '',
            'publish'      => '',
            'is_public'    => '',
            'is_release'   => '',
            'is_finish'    => '',
            'from_room_id' => '',
            'page'         => '',
            'pagesize'     => '',
            'begin_time'   => '',
            'end_time'     => '',
            'type'         => '',
        ]);
        $page     = $params['page'] ?? 1;
        $pagesize = $params['pagesize'] ?? 20;
        $query    = vss_model()->getVotesModel()->newQuery();
        if ($params['room_id']) {
            if ($params['type'] == 2) {//排除某个房间绑定
                $vote_ids = vss_model()->getRoomVoteLkModel()->where([
                    'room_id' => $params['room_id'],
                    'bind'    => 1
                ])->pluck('vote_id');
                $query->whereNotIn('votes.vote_id', $vote_ids);
            } elseif (!empty($params['publish'])) {//获取发布中的投票
                $query->leftJoin('room_vote_lk', 'votes.vote_id', 'room_vote_lk.vote_id')
                    ->where('room_vote_lk.room_id', $params['room_id'])
                    ->where('room_vote_lk.publish', $params['publish']);
//                    ->where('room_vote_lk.finish_time', '>=', date('Y-m-d H:i:s'));
            } else {
                $query->leftJoin('room_vote_lk', 'votes.vote_id', 'room_vote_lk.vote_id')
                    ->where('room_vote_lk.room_id', $params['room_id'])
                    ->where('bind', 1);
            }
        }
        // 时间筛选
        if ($params['begin_time']) {
            $begin_time = $params['begin_time'];
            $end_time   = !empty($params['end_time']) ? $params['end_time'] : date('Y-m-d H:i:s');
            $query->whereBetween('votes.created_at', [$begin_time, $end_time]);
        }
        if ($params['account_id']) {
            $query->where('votes.account_id', $params['account_id']);
            $query->leftJoin('room_vote_lk', 'room_vote_lk.vote_id', 'votes.vote_id');
        }
        if (!is_null($params['is_public'])) {
            $query->where('votes.is_public', $params['is_public']);
        }

        $keyword = trim($params['keyword'] ?? '');
        if ($keyword) {
            $query->where(function ($query) use ($keyword) {
                $query->where('votes.title', 'like', '%' . $keyword . '%')
                    ->orWhere('votes.vote_id', 'like', '%' . $keyword . '%');
            });
        }
        $query->leftJoin('rooms', 'rooms.room_id', 'room_vote_lk.room_id');
        $field = 'votes.*,room_vote_lk.updated_at as lk_update_time,room_vote_lk.publish,room_vote_lk.is_finish,room_vote_lk.is_release,rooms.subject as room_subject,room_vote_lk.room_id';
        $list  = $query->selectRaw($field)->groupBy('votes.vote_id')->orderBy(
            'votes.updated_at',
            'desc'
        )->paginate($pagesize, ['votes.*'], 'page', $page);
        $list  = json_decode(json_encode($list, JSON_UNESCAPED_UNICODE), true);
        //发布过的返回发布时间 publish_time = finish_time - limit_time
        if ($params['publish']) {
            $list['data'] = array_map(function ($v) {
                $v['public_time'] = date('Y-m-d H:i:s', strtotime($v['finish_time']) - $v['limit_time']);
                return $v;
            }, $list['data']);
        }
        //查询是否回答过问卷
        if (!empty($params['answer_account_id'])) {
            $joinUser    = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId(
                $params['answer_account_id'],
                $params['room_id']
            );
            $answer_list = vss_model()->getVoteAnswersModel()
                ->where(['room_id' => $params['room_id'], 'join_id' => $joinUser->join_id])
                ->whereIn('vote_id', array_column($list['data'], 'vote_id'))->get()->toArray();
            $answerMap   = [];
            foreach ($answer_list as $alk) {
                $answerMap[$alk['vote_id']] = $alk;
            }

            $list['data'] = array_map(function ($v) use ($answerMap) {
                $v['answer']    = empty($answerMap[$v['vote_id']]) ? 0 : 1;
                $v['answer_id'] = (int)$answerMap[$v['vote_id']]['answer_id'];
                return $v;
            }, $list['data']);
        }
        if ($params['from_room_id']) {
            if (!empty($list['data'])) {
                $lk_list = vss_model()->getRoomVoteLkModel()->where(
                    'room_id',
                    $params['from_room_id']
                )->whereIn(
                    'vote_id',
                    array_column($list['data'], 'vote_id')
                )->get()->toArray();

                $map = [];
                foreach ($lk_list as $lk) {
                    $map[$lk['vote_id']] = $lk;
                }
                $list['data'] = array_map(function ($v) use ($map) {
                    $v['publish'] = empty($map[$v['vote_id']]) ? 0 : $map[$v['vote_id']]['publish'];
                    $v['bind']    = empty($map[$v['vote_id']]) ? 0 : $map[$v['vote_id']]['bind'];
                    $v['answer']  = empty($map[$v['vote_id']]) ? 0 : 1;
                    return $v;
                }, $list['data']);
            }
        }
        return $list;
    }

    /**
     * 投票-投票删除
     *
     * @param $params
     *
     *
     */
    public function delete($params)
    {
        vss_validator($params, [
            'vote_id'    => 'required',
            'account_id' => 'required' //required
        ]);
        $voteId = $params['vote_id'];
        $vote   = $this->getVote($voteId);

        $roomId = vss_model()->getRoomVoteLkModel()
            ->where('vote_id', $voteId)
            ->value('room_id');

        // 删除缓存的投票 ID
        $this->delRunningVoteIdCache($roomId, $voteId);

        return $vote->delete();
    }

    /**
     * 投票-投票详情
     *
     * @param $params
     *
     * @return VotesModel
     *
     */
    public function info($params)
    {
        vss_validator($params, [
            'vote_id' => 'required',
            'room_id' => '',
        ]);

        if (empty($params['room_id'])) {
            $data = vss_model()->getVotesModel()->findByVoteId($params['vote_id']);
            !$data && $this->fail(ResponseCode::COMP_VOTE_INVALID);
        } else {
            $lk = vss_model()->getRoomVoteLkModel()->findByRoomIdAndVoteId(
                $params['room_id'],
                $params['vote_id']
            );
            !$lk && $this->fail(ResponseCode::COMP_VOTE_INVALID);
            $data                 = $lk->toArray();
            $data['current_time'] = DateUtil::getCurrentDateTime();

            $remainTime          = DateUtil::dateToTime($lk['finish_time']) - time();
            $data['remain_time'] = max($remainTime, 0);
        }

        // 查询用户是否回答过该问卷
        $condition      = [
            'vote_id'    => $params['vote_id'],
            'account_id' => vss_service()->getTokenService()->getAccountId()
        ];
        $existAnswer    = vss_model()->getVoteAnswersModel()->getVoteAnswersInfo($condition, ['answer_id']);
        $data['answer'] = $existAnswer ? 1 : 0;

        return $data;
    }

    /**
     * 投票统计详情
     *
     * @param $params
     *
     * @return array
     *
     */
    public function voteDetail($params)
    {
        vss_validator($params, [
            'vote_id'    => 'required',
            'room_id'    => 'required',
            'account_id' => '',
            'form'       => '',
        ]);

        $roomId = $params['room_id'];
        $voteId = $params['vote_id'];
        $lk     = vss_model()->getRoomVoteLkModel()->findByRoomIdAndVoteId(
            $roomId,
            $voteId
        );
        (empty($lk) || $lk->publish != 1) && $this->fail(ResponseCode::COMP_VOTE_NOT_PUBLISH);

        $voteInfo = vss_model()->getVotesModel()->findByVoteId($voteId);
        !$voteInfo && $this->fail(ResponseCode::EMPTY_VOTE);

        $vote_option_count_arr = vss_model()->getVoteOptionCountModel()->getVoteOptionCountInfoByRvlkId($lk->id);

        empty($vote_option_count_arr) && $this->fail(ResponseCode::COMP_VOTE_INVALID);

        //投票用户查看详情
        $user_vote = [];
        if ($params['account_id']) {
            $account_id = vss_service()->getTokenService()->getAccountId();
            if ($params['account_id'] != $account_id) {
                $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
            }
            $join_user = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId(
                $account_id,
                $roomId
            );
            empty($join_user) && $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
            $condition            = [];
            $condition['vote_id'] = $voteId;
            $condition['room_id'] = $roomId;
            $condition['join_id'] = $join_user->join_id;
            $answer_info          = vss_model()->getVoteAnswersModel()->getVoteAnswersInfo(
                $condition,
                ['answer_id', 'extend']
            );

            if ($answer_info && $answer_info['extend']) {
                $extend = json_decode($answer_info['extend'], true);
                foreach ($extend as $key => $value) {
                    $user_vote[$value['id']] = $value;
                }
            }
        }

        //获取表单内容
        if ($params['form']) {
            $form_info = vss_service()->getPaasService()->getFormInfo($voteId);
            empty($form_info['detail']) && $this->fail(ResponseCode::COMP_VOTE_INVALID);

            //获取问卷表单选项数据
            $vote_question = [];
            foreach ($form_info['detail'] as $key => $question_detail) {
                $vote_question[$question_detail['id']]['question_id'] = $question_detail['id'];
                $vote_question[$question_detail['id']]['title']       = $form_info['title'];
                $vote_question[$question_detail['id']]['description'] = $form_info['description'];
                $vote_question[$question_detail['id']]['imgUrl']      = $question_detail['imgUrl'];
                $vote_question[$question_detail['id']]['type']        = $question_detail['type'];
                if (empty($question_detail['detail']['list'])) {
                    continue;
                }
                $vote_option = [];
                foreach ($question_detail['detail']['list'] as $k => $option_detail) {
                    $vote_option[$option_detail['id']]['question_id'] = $question_detail['id'];
                    $vote_option[$option_detail['id']]['option_id']   = $question_detail['id'];
                    $vote_option[$option_detail['id']]['key']         = $option_detail['key'];
                    $vote_option[$option_detail['id']]['value']       = $option_detail['value'];
                }
                //保证key值正序排列
                ksort($vote_option);
                $vote_question[$question_detail['id']]['list'] = $vote_option;
            }
        }

        $data = [];
        //返回投票信息详情
        $info = vss_model()->getVotesModel()->findByVoteId($params['vote_id']);
        foreach ($vote_option_count_arr as $option_count) {
            $data[$option_count['question_id']]['question_id'] = $option_count['question_id'];
            $data[$option_count['question_id']]['title']       = '';
            $data[$option_count['question_id']]['description'] = '';
            $data[$option_count['question_id']]['imgUrl']      = '';
            $data[$option_count['question_id']]['type']        = '';
            $data[$option_count['question_id']]['extend']      = $info['extend'] ?? '';

            $data[$option_count['question_id']]['list'][$option_count['option']]['option_id'] = $option_count['option_id'];
            $data[$option_count['question_id']]['list'][$option_count['option']]['count']     = $option_count['count'];
            $data[$option_count['question_id']]['list'][$option_count['option']]['option']    = $option_count['option'];
            $data[$option_count['question_id']]['list'][$option_count['option']]['value']     = '';
            $data[$option_count['question_id']]['list'][$option_count['option']]['answer']    = 0;     //是否回答  0-未投票 1-已投票

            if (isset($user_vote[$option_count['question_id']])) {
                if ($user_vote[$option_count['question_id']]['type'] == 'radio') {
                    $replys = $user_vote[$option_count['question_id']]['replys'];
                    if ($replys == $option_count['option']) {
                        $data[$option_count['question_id']]['list'][$option_count['option']]['answer'] = 1;
                    }
                }
                if ($user_vote[$option_count['question_id']]['type'] == 'checkbox') {
                    $replys = $user_vote[$option_count['question_id']]['replys'];
                    if (in_array($option_count['option'], $replys)) {
                        $data[$option_count['question_id']]['list'][$option_count['option']]['answer'] = 1;
                    }
                }
            }

            if ($params['form'] && !empty($vote_question)) {
                $data[$option_count['question_id']]['question_id'] = $vote_question[$option_count['question_id']]['question_id'];
                $data[$option_count['question_id']]['title']       = $vote_question[$option_count['question_id']]['title'];
                $data[$option_count['question_id']]['description'] = $vote_question[$option_count['question_id']]['description'];
                $data[$option_count['question_id']]['imgUrl']      = $vote_question[$option_count['question_id']]['imgUrl'];
                $data[$option_count['question_id']]['type']        = $vote_question[$option_count['question_id']]['type'];
                //选项内容
                $data[$option_count['question_id']]['list'][$option_count['option']]['value'] = $vote_question[$option_count['question_id']]['list'][$option_count['option_id']]['value'];
            }
        }
        $data = array_values($data);
        return $data;
    }

    /**
     * 投票-投票更新
     *
     * @param $params
     *
     *
     */
    public function update($params)
    {
        $rule = [
            'title'      => '',
            'extend'     => '',
            'room_id'    => '',
            'vote_id'    => 'required',
            'account_id' => 'required',
            'limit_time' => '',
            'option_num' => '',
        ];
        $data = vss_validator($params, $rule);

        $vote = $this->getVote($params['vote_id']);
        empty($vote) && $this->fail(ResponseCode::COMP_VOTE_INVALID);

        $lk_count = vss_model()->getRoomVoteLkModel()->where(['vote_id' => $params['vote_id']])->count();
        ($lk_count > 1) && $this->fail(ResponseCode::COMP_VOTE_REPEAT_BIND);
        //直播间内修改操作
        if ($params['room_id']) {
            $lk = vss_model()->getRoomVoteLkModel()->getRoomVoteLkInfo(['vote_id' => $params['vote_id']]);
            empty($lk['id']) && $this->fail(ResponseCode::COMP_VOTE_INVALID);
            ($lk['publish'] == 1) && $this->fail(ResponseCode::COMP_VOTE_PUBLISHED_NOT_EDIT);

            vss_model()->getVoteOptionCountModel()->delVoteOptionCountByRvlkId($lk['id']);
            $this->voteOptionCountCreate($params['vote_id'], $lk['id']);

            if ($lk['room_id'] != $params['room_id']) {
                $lk = vss_model()->getRoomVoteLkModel()->findByRoomIdAndVoteId(
                    $params['vote_id'],
                    $lk['room_id']
                );
                $lk->update(['room_id' => $params['room_id']]);
            }
        }
        unset($data['vote_id'], $data['room_id']);
        return $vote->update($data + ['updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * 投票-投票绑定
     *
     * @param $params
     *
     *
     */
    public function bindRoom($params)
    {
        $rule = [
            'vote_id' => 'required',
            'room_id' => 'required',
        ];
        $data = vss_validator($params, $rule);
        $vote = $this->getVote($params['vote_id']);
        $room = vss_model()->getRoomsModel()->findByRoomId($params['room_id']);
        $room->account_id != $vote->account_id && $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        $lk = vss_model()->getRoomVoteLkModel()->findByRoomIdAndVoteId(
            $params['room_id'],
            $params['vote_id']
        );
        if (empty($lk)) {
            $res = vss_model()->getRoomVoteLkModel()->create($data + ['bind' => 1]);
            $this->voteOptionCountCreate($params['vote_id'], $res->id);
        } else {
            $lk->update(['bind' => 1]);
        }
    }

    /**
     * 投票-投票解绑
     *
     * @param $params
     *
     *
     */
    public function unbindRoom($params)
    {
        vss_validator($params, [
            'vote_id' => 'required',
            'room_id' => 'required'
        ]);
        $this->getVote($params['vote_id'], $params['room_id']);
        $lk = vss_model()->getRoomVoteLkModel()->findByRoomIdAndVoteId(
            $params['room_id'],
            $params['vote_id']
        );
        $lk && $lk->update(['bind' => 0]);
    }

    /**
     * 投票-投票复制
     *
     * @param $params
     *
     * @return $this
     *
     */
    public function copy($params)
    {
        vss_validator($params, [
            'room_id'    => 'required',
            'vote_id'    => 'required',
            'account_id' => 'required',
            'app_id'     => 'required',
        ]);

        $account_id = vss_service()->getTokenService()->getAccountId();
        ($account_id != $params['account_id']) && $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        if ($params['room_id']) {
            $room = vss_model()->getRoomsModel()->findByRoomId($params['room_id']);
            ($params['account_id'] != $room->account_id) && $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        //获取vss问卷信息
        $voteId = $params['vote_id'];
        $vote   = vss_model()->getVotesModel()->find($voteId);
        if (empty($vote)) {
            $this->fail(ResponseCode::COMP_VOTE_INVALID);
        }
        //从微吼云获取问卷详细信息
        $info = vss_service()->getPaasService()->getFormInfo($voteId);
        if (empty($info)) {
            $this->fail(ResponseCode::COMP_VOTE_INVALID);
        }

        $createArr = [
            'title'       => $info['title'],
            'description' => $info['description'] ?? '',
            'publish'     => 'Y', //PaaS没有发布问卷功能,默认写死
            'detail'      => $this->formatDetail($info['detail']),
            'owner_id'    => $params['account_id']
        ];
        $createArr = $this->unsetEmptyArr($createArr);
        //在微吼创建问卷
        $newVote = vss_service()->getPaasService()->createForm($createArr);
        if (empty($newVote['id'])) {
            $this->fail(ResponseCode::COMP_VOTE_INVALID);
        }

        try {
            vss_model()->getVotesModel()->getConnection()->beginTransaction();
            $createVote = [
                'vote_id'    => $newVote['id'],
                'title'      => $vote->title,
                'extend'     => $vote->extend,
                'account_id' => $vote->account_id,
                'app_id'     => $vote->app_id,
                'is_public'  => $vote->is_public,
                'limit_time' => $vote->limit_time,
                'option_num' => $vote->option_num
            ];
            //在VSS创建问卷
            $vote = vss_model()->getVotesModel()->create($createVote);
            $params['room_id'] && $this->bindRoom([
                'vote_id' => $newVote['id'],
                'room_id' => $params['room_id']
            ]);
            vss_model()->getVotesModel()->getConnection()->commit();
            return $vote;
        } catch (\Exception $e) {
            vss_model()->getVotesModel()->getConnection()->rollBack();
            $this->fail(ResponseCode::BUSINESS_COPY_FAILED);
        }
    }

    /**
     * 提交答案
     *
     * @param $params
     *
     *
     */
    public function answer($params)
    {
        vss_validator($params, [
            'room_id'   => 'required',
            'extend'    => 'required',
            'vote_id'   => 'required',
            'answer_id' => 'required',
        ]);

        $key = 'vote_answer_' . $params['room_id'] . $params['vote_id'] . $params['third_party_user_id'];

        if (vss_redis()->setnx($key, 1)) {
            vss_redis()->setex($key, 86400, 1);
        } else {
            $this->fail(ResponseCode::BUSINESS_REPEAT_SUBMIT);
        }

        $vote = $this->getVote($params['vote_id']);
        empty($vote) && $this->fail(ResponseCode::COMP_VOTE_INVALID);
        $params['option_num'] = $vote->option_num;
        $params['account_id'] = $params['third_party_user_id'];

        //是否超出提交时间
        $lk = vss_model()->getRoomVoteLkModel()->findByRoomIdAndVoteId($params['room_id'], $params['vote_id']);
        //超出提交时间 不允许提交
        if ($lk->is_finish) {
            $this->fail(ResponseCode::BUSINESS_SUBMIT_FAILED);
        }
        $params['vote_lk_id'] = $lk->id;

        //写入队列
        vss_queue()->push(new SubmitVoteJob($params));
        return true;
    }

    /**
     * 队列消费答案提交
     *
     * @author  jin.yang@vhall.com
     * @date    2020-11-05
     */
    public function queueAnswer($params)
    {
        $rule = [
            'room_id'    => 'required',
            'extend'     => 'required',
            'vote_id'    => 'required',
            'answer_id'  => 'required',
            'account_id' => '',
        ];
        $data = vss_validator($params, $rule);

        if (vss_model()->getVoteAnswersModel()->find($params['answer_id'])) {
            vss_logger()->info('已投票-queue', [$params]);

            return true;
        }

        $joinUser = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId($params['account_id'],
            $params['room_id']);
        if (empty($joinUser)) {
            vss_logger()->info('用户未找到-vote-queue', [$params]);
            return true;
        }

        //处理extend信息
        $extend = $this->dealExtend(
            $params['vote_lk_id'],
            $params['extend'],
            $params['option_num']
        );
        if (empty($extend)) {
            vss_logger()->info('处理extend信息错误-vote-queue', [$params]);
            return true;
        }
        vss_model()->getVoteAnswersModel()->create($data + ['join_id' => $joinUser->join_id]);
        $joinUser->update(['is_answered_vote' => 1]);

        return true;
    }

    /**
     * 取消投票发布
     *
     * @param $params
     *
     *
     */
    public function cancelPublish($params)
    {
        vss_validator($params, [
            'vote_id' => 'required',
            'room_id' => 'required'
        ]);
        $vote = $this->getVote($params['vote_id'], $params['room_id']);
        empty($vote) && $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $join_user->role_name == 2 && $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        $lk = vss_model()->getRoomVoteLkModel()->findByRoomIdAndVoteId(
            $params['room_id'],
            $params['vote_id']
        );
        $lk && $lk->update(['publish' => 0, 'finish_time' => 0]);

        // 删除缓存的投票 ID
        $this->delRunningVoteIdCache($params['room_id'], $params['vote_id']);
    }

    /**
     * 发布投票(主播)
     *
     * @param $params
     *
     *
     */
    public function publish($params)
    {
        $rule = [
            'vote_id' => 'required',
            'room_id' => 'required',
        ];
        $data = vss_validator($params, $rule);

        $roomId = $params['room_id'];
        $voteId = $params['vote_id'];

        // 检查是否有正在进行中的投票
        if (vss_redis()->hget(CachePrefixConstant::INTERACT_TOOL . $roomId, VoteConstant::INTERACT_TOOL_FILED)) {
            $this->fail(ResponseCode::COMP_VOTE_RUNNING_NOT_PUBLISH);
        }

        $vote = $this->getVote($voteId, $roomId);
        empty($vote) && $this->fail(ResponseCode::COMP_VOTE_INVALID);

        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($roomId);
        $join_user->role_name == 2 && $this->fail(ResponseCode::AUTH_NOT_PERMISSION);

        $lk = vss_model()->getRoomVoteLkModel()->findByRoomIdAndVoteId($roomId, $voteId);
        // 绑定account_id
        $res = vss_model()->getRoomsModel()->findByRoomId($roomId);
        if (empty($res)) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }

        // 发布投票，投票信息本地静态化
        $ossUrl = vss_service()->getFormService()->writeInfoLocal($vote->vote_id, 'vote', [
            'limit_time' => $vote['limit_time']
        ]);

        //结算结束时间  从发布开始
        $currTime    = time();
        $finish_time = date('Y-m-d H:i:s', $currTime + $vote['limit_time']);
        if (empty($lk)) {
            $lk = vss_model()->getRoomVoteLkModel()->create($data + [
                    'publish'     => 1,
                    'account_id'  => $res['account_id'],
                    'finish_time' => $finish_time
                ]);
        } elseif ($lk->publish == 1) {
            // 投票已发布
            $this->fail(ResponseCode::COMP_VOTE_INVALID);
        } else {
            $lk->update(['publish' => 1, 'account_id' => $res['account_id'], 'finish_time' => $finish_time]);
        }

        vss_service()->getPaasChannelService()->sendMessage($roomId, [
            'type'         => 'vote_push',
            'vote_id'      => $voteId,
            'nick_name'    => $join_user->nickname,
            'room_join_id' => $join_user->account_id,
            'room_role'    => $join_user->role_name,
            'info_url'     => $ossUrl,
            'start_time'   => $currTime,
            'limit_time'   => $vote['limit_time'],
        ]);

        // 记录投票 ID
        vss_redis()->hset(
            CachePrefixConstant::INTERACT_TOOL . $roomId,
            VoteConstant::INTERACT_TOOL_FILED,
            $voteId
        );

        vss_service()->getPaasChannelService()->sendNotice($roomId, $voteId, $join_user->account_id, 'vote_push');

        // 投递任务，定时结束队列
        vss_queue()->push(new VoteAutoFinishJob($lk->id), $vote['limit_time']);
    }

    /**
     * 是否提交过投票
     *
     * @param $params
     *
     * @return array
     *
     */
    public function checkSurvey($params)
    {
        vss_validator($params, [
            'room_id' => 'required',
            'vote_id' => 'required',
        ]);
        $join_user = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId(
            vss_service()->getTokenService()->getAccountId(),
            $params['room_id']
        );

        $answer = vss_model()->getVoteAnswersModel()->where([
            'vote_id' => $params['vote_id'],
            'room_id' => $params['room_id'],
            'join_id' => $join_user->join_id,
        ])->first();

        $rvlk = vss_model()->getRoomVoteLkModel()->findByRoomIdAndVoteId(
            $params['room_id'],
            $params['vote_id']
        );

        // 检查投票是否被删除
        $voteInfo = vss_model()->getVotesModel()->findByVoteId($params['vote_id']);
        !$voteInfo && $this->fail(ResponseCode::EMPTY_VOTE);

        //用户是否回答
        $data['is_answer'] = empty($answer) ? false : true;
        //投票是否结束
        $data['is_finish'] = ($rvlk->is_finish || (DateUtil::getCurrentDateTime() > $rvlk->finish_time)) ? true : false;
        return $data;
    }

    /**
     * 投票结束
     *
     * @param $params
     *
     *
     */
    public function voteFinish($params)
    {
        vss_validator($params, [
            'vote_id' => 'required',
            'room_id' => 'required',
        ]);
        $vote = $this->getVote($params['vote_id'], $params['room_id']);
        empty($vote) && $this->fail(ResponseCode::COMP_VOTE_INVALID);

        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $join_user->role_name == 2 && $this->fail(ResponseCode::AUTH_NOT_PERMISSION);

        $lk = vss_model()->getRoomVoteLkModel()->findByRoomIdAndVoteId(
            $params['room_id'],
            $params['vote_id']
        );
        empty($lk) && $this->fail(ResponseCode::COMP_VOTE_INVALID);
        $lk['publish'] != 1 && $this->fail(ResponseCode::COMP_VOTE_NOT_PUBLISH);
        $lk['is_finish'] == 1 && $this->fail(ResponseCode::COMP_VOTE_FINISHED);
        $finish_time = DateUtil::getCurrentDateTime();
        $lk->update(['is_finish' => 1, 'finish_time' => $finish_time]);

        vss_service()->getPaasChannelService()->sendMessage($params['room_id'], [
            'type'         => 'vote_finish',
            'vote_id'      => $params['vote_id'],
            'nick_name'    => $join_user->nickname,
            'room_join_id' => $join_user->account_id,
            'room_role'    => $join_user->role_name
        ]);
        //发问卷发送公告信息
        vss_service()->getPaasChannelService()->sendNotice(
            $params['room_id'],
            $params['vote_id'],
            $join_user->account_id,
            'vote_finish'
        );

        // 删除缓存的投票 ID
        $this->delRunningVoteIdCache($params['room_id'], $params['vote_id']);
    }

    /**
     * 投票实时统计结果推送(不发公告)
     *
     * @param $params
     *
     *
     */
    public function pushStatis($params)
    {
        vss_validator($params, [
            'vote_id' => 'required',
            'room_id' => 'required',
        ]);
        $vote = $this->getVote($params['vote_id'], $params['room_id']);
        empty($vote) && $this->fail(ResponseCode::COMP_VOTE_INVALID);

        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $join_user->role_name == 2 && $this->fail(ResponseCode::AUTH_NOT_PERMISSION);

        //获取表单详细内容
        $params['form'] = 1;
        $vote_detail    = $this->voteDetail($params);

        vss_service()->getPaasChannelService()->sendMessage($params['room_id'], [
            'type'         => 'vote_statis',
            'vote_id'      => $params['vote_id'],
            'nick_name'    => $join_user->nickname,
            'room_join_id' => $join_user->account_id,
            'room_role'    => $join_user->role_name,
            'vote_static'  => $vote_detail
        ]);
    }

    /**
     * 投票公布结果
     *
     * @param $params
     *
     *
     */
    public function votePush($params)
    {
        vss_validator($params, [
            'vote_id' => 'required',
            'room_id' => 'required',
        ]);

        //判断是否消费完
        if (!SubmitVoteJob::isFinish($params['room_id'])) {
            $this->fail(ResponseCode::BUSINESS_RESULT_STATING);
        }

        $vote = $this->getVote($params['vote_id'], $params['room_id']);
        empty($vote) && $this->fail(ResponseCode::COMP_VOTE_INVALID);

        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $join_user->role_name == 2 && $this->fail(ResponseCode::AUTH_NOT_PERMISSION);

        $lk = vss_model()->getRoomVoteLkModel()->findByRoomIdAndVoteId(
            $params['room_id'],
            $params['vote_id']
        );
        empty($lk) && $this->fail(ResponseCode::COMP_VOTE_INVALID);
        if ($lk['publish'] != 1 || $lk['is_finish'] != 1) {
            $this->fail(ResponseCode::COMP_VOTE_NOT_PUBLISH_RESULT);
        }
        ($lk['is_release'] == 1) && $this->fail(ResponseCode::COMP_VOTE_RESULT_PUBLISHED);
        $lk->update(['is_release' => 1]);
        //获取表单详细内容
        $params['form'] = 1;
        $vote_detail    = $this->voteDetail($params);

        vss_service()->getPaasChannelService()->sendMessage($params['room_id'], [
            'type'         => 'vote_final_statis',
            'vote_id'      => $params['vote_id'],
            'nick_name'    => $join_user->nickname,
            'room_join_id' => $join_user->account_id,
            'room_role'    => $join_user->role_name,
            'vote_static'  => $vote_detail
        ]);
        //发问卷发送公告信息
        vss_service()->getPaasChannelService()->sendNotice(
            $params['room_id'],
            $params['vote_id'],
            $join_user->account_id,
            'vote_final_statis'
        );
    }

    /**
     * 投票用户数量
     *
     * @param $params
     *
     * @return int
     *
     */
    public function getAnswerNum($params)
    {
        vss_validator($params, [
            'room_id'    => '',
            'account_id' => '',
        ]);
        $query = vss_model()->getVoteAnswersModel()->newQuery();

        $query->leftJoin('votes', 'vote_answers.vote_id', 'votes.vote_id');
        if (!empty($params['room_id'])) {
            $query->where('vote_answers.room_id', $params['room_id']);
        }
        if (!empty($params['account_id'])) {
            $query->where('votes.account_id', $params['account_id']);
        }
        $query->whereNull('vote_answers.deleted_at');

        if (!empty($params['begin_date']) && !empty($params['end_date'])) {
            $query->where('vote_answers.created_at', '>=', "{$params['begin_date']}");
            $query->where('vote_answers.created_at', '<=', "{$params['end_date']} 23:59:59");
        }

        $total_query = clone $query;
        $total       = $total_query->count('vote_answers.vote_id');
        return $total;
    }

    /**
     * 投票活动数量
     *
     * @param $params
     *
     * @return int
     */
    public function getVoteNums($params)
    {
        vss_validator($params, [
            'room_id'    => '',
            'account_id' => '',
        ]);
        $query = vss_model()->getRoomVoteLkModel()->newQuery();
        $query->leftJoin('votes', 'room_vote_lk.vote_id', 'votes.vote_id');
        if (!empty($params['room_id'])) {
            $query->where('room_vote_lk.room_id', $params['room_id']);
        }
        if (!empty($params['account_id'])) {
            $query->where('votes.account_id', $params['account_id']);
        }
        $query->where('room_vote_lk.publish', 1);
        $query->whereNull('votes.deleted_at');

        if (!empty($params['begin_date']) && !empty($params['end_date'])) {
            $query->where('room_vote_lk.created_at', '>=', "{$params['begin_date']}");
            $query->where('room_vote_lk.created_at', '<=', "{$params['end_date']} 23:59:59");
        }

        $total_query = clone $query;
        $total       = $total_query->distinct('room_vote_lk.vote_id')->count('room_vote_lk.vote_id');
        return $total;
    }

    /**
     * 根据关联ID获取问卷数量
     *
     * @param $params
     *
     * @return int|mixed
     *
     */
    public function getNum($params)
    {
        $params = array_filter(array_map('trim', $params));
        if (!empty($params['room_id'])) {
            vss_validator($params, [
                'room_id' => 'required',
            ]);
        }
        if (!empty($params['account_id'])) {
            vss_validator($params, [
                'account_id' => '',
            ]);
        }
        $res['by_room_num']    = $this->getVoteNums($params);
        $res['by_account_num'] = $this->getAnswerNum($params);

        return $res;
    }

    /**
     * 投票统计查询
     * @auther yaming.feng@vhall.com
     * @date 2021/1/5
     *
     * @param string $roomId
     * @param int    $page
     * @param int    $pageSize
     * @param array
     */
    public function statByRoomId($roomId, $page, $pageSize)
    {
        // 1. 查询房间下已发布的投票
        $columns   = [
            'room_vote_lk.id',
            'room_vote_lk.vote_id',
            'room_vote_lk.is_finish',
            'room_vote_lk.created_at',
            'votes.title'
        ];
        $paginator = vss_model()->getRoomVoteLkModel()->newQuery()
            ->leftJoin('votes', 'votes.vote_id', 'room_vote_lk.vote_id')
            ->where('room_vote_lk.room_id', $roomId)
            ->where('room_vote_lk.publish', 1)
            ->whereNull('votes.deleted_at')
            ->orderByDesc('room_vote_lk.id')
            ->paginate($pageSize, $columns, 'page', $page);

        $result = json_decode(json_encode($paginator, JSON_UNESCAPED_UNICODE), true);

        if (!$result['data']) {
            return $result;
        }

        // 2. 查询每个投票的参与人数
        $voteIds    = array_column($result['data'], 'vote_id');
        $voteCounts = vss_model()->getVoteAnswersModel()->where('room_id', $roomId)
            ->whereIn('vote_id', $voteIds)
            ->whereNull('deleted_at')
            ->groupBy(['vote_id'])
            ->selectRaw('vote_id, count(*) as c')
            ->get()
            ->toArray();

        $voteCountMap = array_column($voteCounts, 'c', 'vote_id');

        foreach ($result['data'] as &$item) {
            $item['count'] = Arr::get($voteCountMap, $item['vote_id'], 0);
        }

        return $result;
    }

    /**
     * 创建导出记录
     * @auther yaming.feng@vhall.com
     * @date 2021/1/5
     *
     * @param int $roomId
     * @param int $accountId
     * @param int $voteId
     */
    public function createExport($roomId, $accountId, $voteId)
    {
        $liveInfo = vss_model()->getRoomsModel()->findByRoomId($roomId);
        if (empty($liveInfo)) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }

        $ilId     = $liveInfo['il_id'];
        $fileName = sprintf('%s投票详情%s', $ilId, date('Ymd'));

        $params = [
            'vote_id'   => $voteId,
            'il_id'     => $ilId,
            'room_id'   => $roomId,
            'room_name' => $liveInfo['subject'],
        ];

        $insert = [
            'export'     => VoteConstant::EXPORT_VOTE,
            'il_id'      => $ilId,
            'account_id' => $accountId,
            'source_id'  => $voteId,
            'file_name'  => $fileName,
            'title'      => ['房间ID', '房间名称', '投票标题', '投票截止时间'],
            'params'     => json_encode($params),
            'callback'   => 'vote:getVoteExportData'
        ];

        return vss_model()->getExportModel()->create($insert);
    }

    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/1/5
     *
     * @param array  $export
     * @param string $filePath
     *
     * @return bool
     */
    public function getVoteExportData($export, $filePath)
    {
        $params = json_decode($export['params'], true);
        $header = json_decode($export['title'], true);
        $file   = $filePath . $export['file_name'];

        // 获取投票信息
        $roomVoteInfo = vss_model()->getRoomVoteLkModel()->findByRoomIdAndVoteId($params['room_id'],
            $params['vote_id']);

        // 获取投票详情
        $formInfo   = vss_service()->getPaasService()->getFormInfo($params['vote_id']);
        $voteDetail = $formInfo['detail'][0];
        // 导出表格第二行，投票信息
        $voteLine = [
            $params['il_id'],
            $params['room_name'],
            $formInfo['title'],
            $roomVoteInfo['finish_time'],
        ];

        // 获取每个选项的投票人数
        $voteOptions = vss_model()->getVoteOptionCountModel()->newQuery()
            ->where('question_id', $voteDetail['id'])
            ->where('rvlk_id', $roomVoteInfo['id'])
            ->whereNull('deleted_at')
            ->get(['option', 'count'])
            ->toArray();

        $emptyLine        = array_pad([], count($header), '');
        $optionsCountLine = $emptyLine; //  导出表格第三行，每个选项的参与人数
        $voteOptionsMap   = array_column($voteOptions, 'count', 'option');

        // 投票选项信息
        foreach ($voteDetail['detail']['list'] as $i => $option) {
            $header[]           = '选项' . ($i + 1);
            $voteLine[]         = $option['value'];
            $optionsCountLine[] = Arr::get($voteOptionsMap, $option['key'], 0);
            $emptyLine[]        = '';
        }

        // 导出数据的前四行
        $exportData = [$header, $voteLine, $optionsCountLine, $emptyLine];

        $page     = 1;
        $pageSize = 1000;
        while (true) {
            // 获取所有投票用户
            $voteUsers = vss_model()->getVoteAnswersModel()->newQuery()
                ->leftJoin('accounts', 'accounts.account_id', '=', 'vote_answers.account_id')
                ->where('vote_answers.vote_id', $params['vote_id'])
                ->where('vote_answers.room_id', $params['room_id'])
                ->whereNull('vote_answers.deleted_at')
                ->forPage($page, $pageSize)
                ->get(['vote_answers.extend', 'accounts.username'])
                ->toArray();

            if (!$voteUsers) {
                break;
            }

            foreach ($voteUsers as $item) {
                $extend  = json_decode($item['extend'], true)[0];
                $answers = is_array($extend['replys']) ? $extend['replys'] : [$extend['replys']];
                foreach ($answers as $answer) {
                    $answerUserMap[$answer][] = $item['username'];
                }
            }

            $page++;
        }

        $i         = 0;
        $userLines = [];
        while (true) {
            $flag     = false;
            $userLine = $emptyLine;
            foreach ($answerUserMap as $answer => $users) {
                $index            = ord(strtoupper($answer)) - 61;
                $userLine[$index] = $users[$i] ?? '';
                if ($userLine[$index]) {
                    $flag = true;
                }
            }

            if (!$flag) {
                break;
            }

            $userLines[] = $userLine;
            $i++;
        }

        $userLines[0][3] = '投票详情';
        vss_service()->getExportProxyService()->init($file)->putRows($exportData)->close();

        //修改导出表状态
        vss_model()->getExportModel()->getInstance()->where('id', $export['id'])->update(['status' => 3]);

        return true;
    }

    /**
     * 获取投票及房间详情
     *
     * @param      $vote_id
     * @param null $room_id
     *
     * @return \vhallComponent\vote\models\VotesModel
     *
     */
    public function getVote($vote_id, $room_id = null)
    {
        $vote = vss_model()->getVotesModel()->findByVoteId($vote_id);
        empty($vote) && $this->fail(ResponseCode::COMP_VOTE_INVALID);
        if ($room_id) {
            $room = vss_model()->getRoomsModel()->findByRoomId($room_id);
            empty($room) && $this->fail(ResponseCode::EMPTY_ROOM);
            ($room->account_id != $vote->account_id) && $this->fail(ResponseCode::COMP_VOTE_INVALID);
        }
        return $vote;
    }

    public function unsetEmptyArr($arr)
    {
        foreach ($arr as $k => $v) {
            if (empty($v)) {
                unset($arr[$k]);
            }
        }
        return $arr;
    }

    public function formatDetail($detail)
    {
        foreach ($detail as $k => $v) {
            unset($v['id'], $v['third_party_user_id'], $v['created_at'], $v['app_id']);
        }
        return json_encode($detail);
    }

    /**
     * * 处理投票内容 对投票数量进行累加
     *
     * @param $rvlkId
     * @param $extend
     * @param $option_num
     *
     * @return bool
     */
    public function dealExtend($rvlkId, $extend, $option_num)
    {
        $answer_content = json_decode($extend, true);
        if (empty($answer_content)) {
            return false;
        }

        //保证key存在
        $option_list = vss_model()->getVoteOptionCountModel()->getVoteOptionCountInfoByRvlkId($rvlkId);
        empty($option_list) && $this->fail(ResponseCode::COMP_VOTE_OPTION_INVALID);
        $question_ids = vss_redis()->hkeys(VoteConstant::VOTE_RVLK . $rvlkId);

        foreach ($answer_content as $answer) {
            //投票对应表单问题id
            $question_id = $answer['id'];
            //验证投票提交问题id与投票问题id一致
            !in_array($question_id, $question_ids) && $this->fail(ResponseCode::COMP_VOTE_INVALID_CONTENT);
            //回答内容为空
            if (empty($answer['replys'])) {
                continue;
            }

            //条件
            $where   = [];
            $where[] = ['question_id', '=', $question_id];
            $where[] = ['rvlk_id', '=', $rvlkId];
            //累加投票数据
            $option_model = vss_model()->getVoteOptionCountModel();
            switch ($answer['type']) {
                case 'radio':
                    //投票选项计数自增
                    vss_redis()->hincrby(VoteConstant::VOTE_QUESTION . $question_id, $answer['replys'], 1);
                    $where[] = ['option', '=', $answer['replys']];
                    $option_model->where($where)->increment('count', 1);
                    break;
                case 'checkbox':
                    count($answer['replys']) > $option_num && $this->fail(ResponseCode::COMP_VOTE_OPTION_COUNT_OVERFLOW);
                    //投票选项计数自增
                    foreach ($answer['replys'] as $reply) {
                        vss_redis()->hincrby(VoteConstant::VOTE_QUESTION . $question_id, $reply, 1);
                    }
                    $option_model->where($where)->whereIn('option', $answer['replys'])->increment('count', 1);
                    break;
                default:
                    break;
            }
        }
        return true;
    }

    /**
     * 投票选项创建
     *
     * @param $vote_id
     * @param $rvlk_id
     *
     *
     */
    public function voteOptionCountCreate($vote_id, $rvlk_id)
    {
        if (empty($vote_id) || empty($rvlk_id)) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }
        //从微吼云获取问卷详细信息
        $form_info   = vss_service()->getPaasService()->getFormInfo($vote_id);
        $form_detail = $form_info['detail'];
        if (empty($form_detail)) {
            $this->fail(ResponseCode::COMP_VOTE_INVALID);
        }

        //获取问卷表单选项数据
        foreach ($form_detail as $key => $question_detail) {
            if (empty($question_detail['detail']['list'])) {
                continue;
            }
            foreach ($question_detail['detail']['list'] as $k => $option_detail) {
                $vote_option                = [];
                $vote_option['rvlk_id']     = $rvlk_id;
                $vote_option['question_id'] = $question_detail['id'];
                $vote_option['option_id']   = $option_detail['id'];
                $vote_option['option']      = $option_detail['key'];
                vss_model()->getVoteOptionCountModel()->insertVoteOption($vote_option);
            }
        }
    }

    /**
     * 删除正在进行中的投票 ID 缓存
     * @auther yaming.feng@vhall.com
     * @date 2021/2/25
     *
     * @param $roomId
     * @param $voteId
     */
    public function delRunningVoteIdCache($roomId, $voteId)
    {
        // 先检查，再删除，防止误删除
        $lua = <<<EOF
        local runVoteId = redis.call('hget', KEYS[1], KEYS[2])
        if runVoteId == ARGV[1] then
            redis.call('hdel', KEYS[1], KEYS[2])
        end
        return runVoteId
EOF;

        $runVoteIdCacheKey = CachePrefixConstant::INTERACT_TOOL . $roomId;
        return vss_redis()->eval($lua, [$runVoteIdCacheKey, VoteConstant::INTERACT_TOOL_FILED, $voteId], 2);
    }
}
