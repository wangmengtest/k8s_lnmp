<?php


namespace vhallComponent\lottery\models;

use vhallComponent\decouple\models\WebBaseModel;

class LotteryUserModel extends WebBaseModel
{
    protected $table = 'lottery_user';

    protected $primaryKey = 'id';

    protected $attributes = [
        'il_id' => '',
        'room_id' => '',
        'title' => '',
        'username' => '',
        'nickname' => '',
        'is_winner' => 0,
        'status' => 0,
        'app_id' => '',
        'created_at' => '1970-01-01 00:00:00',
        'updated_at' => '1970-01-01 00:00:00'
    ];

    protected static function boot()
    {
        parent::boot();
    }

    /**
     * 获取房间下抽奖用户
     * @param $ilId
     * @param array $columns
     * @return array
     */
    public function getListByIlId($ilId, $columns = ['*'])
    {
        $list = $this->where('il_id', $ilId)->get($columns);
        if (empty($list)) {
            return [];
        }
        return $list->toArray();
    }

    /**
     * 获取
     * @param $roomId
     * @param array $ids
     * @param array $columns
     * @return array
     */
    public function getListByRoomIdAndIds($roomId, $ids = [], $columns = ['*'])
    {
        $model = $this->where('room_id', $roomId);
        if (!empty($ids)) {
            $model = $model->whereIn('id', $ids);
        }
        $list = $model->get($columns);
        if (empty($list)) {
            return [];
        }
        return $list->toArray();
    }

    /**
     * 获取抽奖用户信息
     * @param $condition
     * @param $columns
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    public function findLotteryUserInfo($condition, $columns = ['*'])
    {
        return $this->where($condition)->first($columns);
    }

    /**
     * 通过id修改数据
     * @param $ids
     * @param $update
     * @return int
     */
    public function updateByIds($ids, $update)
    {
        return $this->whereIn('id', $ids)->update($update);
    }

    /**
     * 删除房间下抽奖用户
     * @param $ilId
     * @param bool $force
     * @return int
     */
    public function delByIlId($ilId, $force = false)
    {
        $model = $this->where('il_id', $ilId);
        if ($force) {
            return $model->forcedelete();
        }
        return $model->delete();
    }
}
