<?php

namespace vhallComponent\room\models;

use Illuminate\Database\Eloquent\Builder;
use vhallComponent\decouple\models\WebBaseModel;

/**
 *+----------------------------------------------------------------------
 * Class InavStats
 *
 * @property int $id
 * @property int $il_id        互动直播id
 * @property int $account_id   用户id
 * @property int $flow         流量/KB
 * @property int $pv_num       pv量/次
 * @property int $uv_num       uv量/人
 * @property int $duration     互动时长/秒
 * @property string  $created_time 统计时间
 * @property string  $updated_at
 * @property string  $created_at
 * @property string  $deleted_at
 *
 *+----------------------------------------------------------------------
 * @package App\Models
 * @author  ensong.liu@vhall.com
 * @date    2019-05-08 12:56:28
 * @version v1.0.0
 *+----------------------------------------------------------------------
 */
class InavStatsModel extends WebBaseModel
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $table      = 'inav_stats';

    /**
     * 条件构造器
     *
     * @param Builder $model
     * @param array   $condition
     *
     * @return Builder
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 15:14:34
     *
     */
    protected function buildCondition(Builder $model, array $condition): Builder
    {
        $model = parent::buildCondition($model, $condition);

        //房主ID
        $model->when($condition['master_account_id'] ?? '', function ($query) use ($condition) {
            $query->leftJoin(
                RoomsModel::getInstance()->getTable() . ' AS rooms',
                'rooms.il_id',
                '=',
                'inav_stats.il_id'
            )
                ->where(function ($query) use ($condition) {
                    $query->where('rooms.account_id', '=', $condition['master_account_id']);
                });
        });
        //统计时间范围-开始
        $model->when($condition['begin_time'] ?? '', function ($query) use ($condition) {
            $query->where('inav_stats.created_time', '>=', $condition['begin_time']);
        });
        //统计时间范围-结束
        $model->when($condition['end_time'] ?? '', function ($query) use ($condition) {
            $query->where('inav_stats.created_time', '<=', date('Y-m-d 23:59:59', strtotime($condition['end_time'])));
        });

        return $model;
    }

    /**
     * 获取互动时长统计
     *
     * @param array $condition
     *
     * @return int
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 15:55:26
     *
     */
    public static function getDurationSum(array $condition = []): int
    {
        return (int)(new self())->buildCondition(self::query(), $condition)->sum('duration');
    }

    public function createInavStats($ilId, $accountId, $data)
    {
        $datetime           = date('Y-m-d H:i:s');
        $this->il_id        = $ilId;
        $this->account_id   = $accountId;
        $this->flow         = $data['flow'];
        $this->pv_num       = $data['pv_num'];
        $this->uv_num       = $data['uv_num'];
        $this->duration     = $data['tt'];
        $this->created_time = $data['created_time'];
        $this->updated_at   = $datetime;
        $this->created_at   = $datetime;
        if (!$this->save()) {
            return false;
        }

        return $this->toArray();
    }
}
