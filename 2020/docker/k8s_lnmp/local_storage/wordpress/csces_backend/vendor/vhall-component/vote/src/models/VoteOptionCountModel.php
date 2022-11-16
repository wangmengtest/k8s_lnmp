<?php
/**
 * Created by PhpStorm.
 * User: liuxiangliang
 * Date: 2020/3/27
 * Time: 16:42
 */
namespace vhallComponent\vote\models;

use vhallComponent\vote\constants\VoteConstant;
use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class VoteOptionCountModel
 * @package App\Models
 * @property int $id
 * @property int $option_id 表单选项id
 * @property int $question_id 表单问题id
 * @property int $rvlk_id 房间投票关联id
 * @property string $option 投票选项 A-T
 * @property int $count 选项投票数
 */
class VoteOptionCountModel extends WebBaseModel
{
    const VOTE_RVLK_EXPIRE_TIME = 60;

    const VOTE_QUESTION_EXPIRE_TIME = 61;    //RVLK先过期 QUESTION后过期 可减少判断流程; QUESTION先过期无影响;

    protected $table = 'vote_option_count';

    public $incrementing = false;

    protected $attributes = [
        'id' => null,
        'option_id' => null,
        'question_id' => null,
        'rvlk_id' => null,
        'option' => '',
        'count' => 0,
        'created_at' => '0000-00-00 00:00:00',
        'updated_at' => '0000-00-00 00:00:00',
    ];

    protected $primaryKey = 'id';

    /**
     * 选项插入
     * @param $insert
     * @return mixed
     */
    public function insertVoteOption($insert)
    {
        return $this->create($insert);
    }

    /**
     * 获取房间下投票选项计数信息
     * @param $rvlkId
     * @param array $field
     * @return array
     */
    public function getVoteOptionCountInfoByRvlkId($rvlkId)
    {
        //刷新缓存 标识   false-刷新
        $flag = true;
        $option_list = [];
        //获取key剩余时间
        $ttl_time = vss_redis()->ttl(VoteConstant::VOTE_RVLK . $rvlkId);
        ($ttl_time<10) && $flag = false;
        //缓存获取选项信息
        $flag && $question_list = vss_redis()->hgetall(VoteConstant::VOTE_RVLK . $rvlkId);
        if (!empty($question_list)) {
            foreach ($question_list as $question_id => $question) {
                $question = unserialize($question);
                foreach ($question as $option) {
                    $flag && $flag = vss_redis()->exists(VoteConstant::VOTE_QUESTION . $question_id);
                    if (!$flag) {
                        continue;
                    }
                    $count = vss_redis()->hget(VoteConstant::VOTE_QUESTION . $question_id, $option['option']);
                    $option['count'] = $count;
                    $option_list[] = $option;
                }
            }
        }

        //无缓存 或 key不存在重新写入缓存
        if (!$flag || empty($option_list)) {
            $option_list = $this->where('rvlk_id', $rvlkId)->get()->toArray();
            $rvlk_data = [];
            $question_data = [];
            foreach ($option_list as $option) {
                $rvlk_data[$option['question_id']][] = $option;
                $question_data[$option['question_id']][$option['option']] = $option['count'];
            }

            $rvlk_list = [];
            foreach ($question_data as $questionId => $questionInfo) {
                //以question_id 分配key  field为选项(A,B..)  value为投票数
                vss_redis()->hmset(VoteConstant::VOTE_QUESTION . $questionId, $questionInfo);
                vss_redis()->expire(VoteConstant::VOTE_QUESTION . $questionId, self::VOTE_QUESTION_EXPIRE_TIME);
                $rvlk_list[$questionId] = serialize($rvlk_data[$questionId]);
            }
            //以房间投票关联id 分配key  field为 question_id  value  为question_id 对应的序列化后的选项信息
            vss_redis()->hmset(VoteConstant::VOTE_RVLK . $rvlkId, $rvlk_list);
            vss_redis()->expire(VoteConstant::VOTE_RVLK . $rvlkId, self::VOTE_RVLK_EXPIRE_TIME);
        }
        return $option_list;
    }

    /**
     * 选项count自增
     * @param $condition
     * @return int
     */
    public function increVoteOptionCount($condition)
    {
        return $this->where($condition)->increment('count', 1);
    }

    /**
     * rvlk_id对应选项删除
     * @param $rvlk_id
     * @return int
     */
    public function delVoteOptionCountByRvlkId($rvlk_id)
    {
        vss_redis()->del(VoteConstant::VOTE_RVLK . $rvlk_id);
        return $this->where('rvlk_id', $rvlk_id)->delete();
    }
}
