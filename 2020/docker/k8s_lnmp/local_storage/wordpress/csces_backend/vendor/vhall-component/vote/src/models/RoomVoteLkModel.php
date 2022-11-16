<?php
/**
 * Created by PhpStorm.
 * User: liuxiangliang
 * Date: 2020/3/27
 * Time: 16:38
 */
namespace vhallComponent\vote\models;

use Illuminate\Database\Eloquent\Builder;
use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class RoomVoteLkModel
 * @package App\Models
 * @property int $id
 * @property int $room_id 房间id
 * @property int $account_id 用户id
 * @property int $finish_time 投票活动结束时间
 * @property int $publish 是否发布
 * @property int $bind 是否绑定
 * @property int $is_release 是否发布结果
 * @property int $is_finish 活动是否结束
 * @property int $extend 业务扩展字段
 */
class RoomVoteLkModel extends WebBaseModel
{
    protected $table = 'room_vote_lk';

    protected $attributes = [
        'id' => null,
        'vote_id' => null,
        'room_id' => null,
        //        'account_id' => 0,
        'finish_time' => null,
        'publish' => 0,
        'bind' => 0,
        'is_release'=>0,
        'is_finish' => 0,
        'extend' => null,
        'created_at' => '0000-00-00 00:00:00',
        'updated_at' => '0000-00-00 00:00:00',
    ];

    protected static function boot()
    {
        self::created(function (self $data) {
            $data->putCache('InfoByRoomIdAndVoteId', $data->room_id . 'and' . $data->vote_id, $data->getAttributes());
        });
        self::updated(function (self $data) {
            $data->putCache('InfoByRoomIdAndVoteId', $data->room_id . 'and' . $data->vote_id, $data->getAttributes());
        });
        self::deleted(function (self $data) {
            $data->deleteCache('InfoByRoomIdAndVoteId', $data->room_id . 'and' . $data->vote_id);
        });
        parent::boot();
    }

    /**
     * @param $room_id
     * @param $vote_id
     * @return $this
     */
    public function findByRoomIdAndVoteId($room_id, $vote_id)
    {
        $attributes = $this->getCache('InfoByRoomIdAndVoteId', $room_id . 'and' . $vote_id,
            function () use ($room_id, $vote_id) {
            $model = $this->where(['room_id' => $room_id, 'vote_id' => $vote_id])->first();
            return $model ? $model->getAttributes() : null;
        });
        return empty($attributes) ? null : $this->newInstance($attributes, true)->syncOriginal();
    }

    /**
     * 查询数据列表
     * @param array $condition
     * @param array $field
     * @return array
     */
    public function getRoomVoteLkList($condition = [], $field = ['*'])
    {
        $list = $this->where($condition)->get($field)->toArray();
        return $list;
    }

    /**
     * 查询数据信息
     * @param array $condition
     * @param array $field
     * @return array
     */
    public function getRoomVoteLkInfo($condition = [], $field = ['*'])
    {
        $model = $this->where($condition)->first($field);
        return $model ? $model->getAttributes() : null;
    }

    /**
     * 修改
     * @param array $update
     * @param array $condition
     * @return int
     */
    public function updateRoomVoteLk($update, $condition)
    {
        return $this->where($condition)->update($update);
    }

    /**
     * 条件构造器
     * @param Builder $model
     * @param array $condition
     * @return Builder
     */
    protected function buildCondition(Builder $model, array $condition): Builder
    {
        //当前表字段条件构建
        $model = parent::buildCondition($model, $condition);
        //ids in查询
        $model->when(
            isset($condition['vote_ids']) && !empty($condition['vote_ids']),
            function (Builder $query) use ($condition) {
                $query->whereIn('vote_id', $condition['vote_ids']);
            }
        );

        return $model;
    }
}
