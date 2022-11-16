<?php

namespace vhallComponent\room\models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use vhallComponent\account\models\AccountsModel;
use vhallComponent\decouple\models\WebBaseModel;

/**
 * RoomStatsModel
 *
 * @property int $id
 * @property int $il_id        互动直播id
 * @property int $account_id   用户id
 * @property int $flow         流量/KB
 * @property int $bandwidth    带宽/kbps
 * @property int $pv_num       pv量/次
 * @property int $uv_num       uv量/人
 * @property int $duration     观看时长/秒
 * @property string  $created_time 统计时间
 * @property string  $updated_at
 * @property string  $created_at
 * @property string  $deleted_at
 *
 * @uses     yangjin
 * @date     2020-07-15
 * @author   jin.yang@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class RoomStatsModel extends WebBaseModel
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $table      = 'room_stats';

    /**
     * 模型关联-用户表
     *
     * @return BelongsTo
     * @author ensong.liu@vhall.com
     * @date   2019-02-14 10:39:24
     */
    public function account()
    {
        return $this->belongsTo(AccountsModel::class, 'account_id', 'account_id');
    }

    /**
     * 模型关联-房间表
     *
     * @return BelongsTo
     * @author ensong.liu@vhall.com
     * @date   2019-02-14 10:41:10
     */
    public function rooms()
    {
        return $this->belongsTo(RoomsModel::class, 'il_id', 'il_id');
    }

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
        $model->when($condition['master_account_id'] ?? '', function (Builder $query) use ($condition) {
            $query->where('account_id', '=', $condition['master_account_id']);
        });
        //时间范围-开始
        $model->when($condition['begin_time'] ?? '', function (Builder $query) use ($condition) {
            $query->where('created_time', '>=', $condition['begin_time']);
        });
        //时间范围-结束
        $model->when($condition['end_time'] ?? '', function (Builder $query) use ($condition) {
            $query->where('created_time', '<=', date('Y-m-d 23:59:59', strtotime($condition['end_time'])));
        });

        return $model;
    }

    /**
     * 获取消耗流量
     *
     * @param array $condition
     *
     * @return float
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 15:51:11
     *
     */
    public static function getFolwSum(array $condition = []): float
    {
        return (float)(new self())->buildCondition(self::query(), $condition)->sum('flow');
    }

    /**
     * 获取最高并发数
     *
     * @param array $condition
     *
     * @return int
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 16:26:58
     *
     */
    public static function getUvMax(array $condition = []): int
    {
        return (int)(new self())->buildCondition(self::query(), $condition)->max('uv_num');
    }

    public function createLiveStats($ilId, $accountId, $data)
    {
        $datetime           = date('Y-m-d H:i:s');
        $this->il_id        = $ilId;
        $this->account_id   = $accountId;
        $this->flow         = $data['flow'];
        $this->bandwidth    = $data['bandwidth'];
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

    /**
     * 获取直播时长统计
     *
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 15:55:26
     *
     * @param array $condition
     *
     * @return int
     */
    public static function getDurationSum(array $condition = []):int
    {
        return (int)(new self())->buildCondition(self::query(), $condition)->sum('duration');
    }

    public function updateLiveStats($ids, $data)
    {
        $update = [];
        $update['flow'] = $data['flow'];
        $update['pv_num'] = $data['pv_num'];
        $update['uv_num'] = $data['uv_num'];
        $update['duration'] = $data['tt'];

        $res = $this->whereIn('id', $ids)->update($update);

        if ($res === false) {
            return false;
        }
        return $res;
    }

    public function getCountListByCreatedTime($account_id = 0, $il_id = 0, $begin_time = '', $end_time = '')
    {
        $filed = implode(',', [
            'il_id',
            'account_id',
            'pv_num',
            'uv_num',
            'duration',
            'created_time'
        ]);
        $query = self::query()->selectRaw($filed);
        if ($account_id) {
            $query->where('account_id', intval($account_id));
        }
        if ($il_id) {
            is_array($il_id) ? $query->whereIn('il_id', $il_id) : $query->where('il_id', $il_id);
        }
        if ($begin_time && $end_time) {
            $query->whereBetween('created_time', [$begin_time, $end_time . '  23:59:59']);
        }
        $query->orderBy('created_time');
        $collection = $query->get();
        $data = $collection ? $collection->toArray() : [];

        return $data;
    }
}
