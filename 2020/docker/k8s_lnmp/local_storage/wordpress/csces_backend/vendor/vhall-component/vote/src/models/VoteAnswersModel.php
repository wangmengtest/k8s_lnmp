<?php
/**
 * Created by PhpStorm.
 * User: liuxiangliang
 * Date: 2020/3/27
 * Time: 16:30
 */
namespace vhallComponent\vote\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class VoteAnswersModel
 * @package App\Models
 * @property string $answer_id
 * @property int $join_id 参会id
 * @property int $room_id 房间id
 * @property int $vote_id 投票id
 * @property int $account_id 用户id
 * @property string $extend 业务扩展字段
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class VoteAnswersModel extends WebBaseModel
{
    protected $table = 'vote_answers';

    public $incrementing = false;

    protected $attributes = [
        'answer_id'=>null,
        'join_id'=>null,
        'room_id'=>null,
        'vote_id'=>null,
        'account_id'=>null,
        'extend'=>null,
        'created_at'=>'0000-00-00 00:00:00',
        'updated_at'=>'0000-00-00 00:00:00',
    ];

    protected $primaryKey = 'answer_id';

    /**
     * @param $condition
     * @param array $field
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    public function getVoteAnswersInfo($condition, $field = ['*'])
    {
        return $this->where($condition)->first($field);
    }
}
