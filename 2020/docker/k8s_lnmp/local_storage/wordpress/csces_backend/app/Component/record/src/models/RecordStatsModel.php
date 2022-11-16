<?php

namespace App\Component\record\src\models;

use Illuminate\Database\Eloquent\Builder;
use vhallComponent\decouple\models\WebBaseModel;

/**
 *+----------------------------------------------------------------------
 * Class RecordStats Model
 *+----------------------------------------------------------------------
 *
 * @property int    $id
 * @property int    $il_id        互动直播id
 * @property int    $account_id   用户id
 * @property string $record_id    回放id
 * @property int    $flow         流量/KB
 * @property int    $pv_num       pv量/次
 * @property int    $uv_num       uv量/人
 * @property int    $duration     观看时长/秒
 * @property string $created_time 统计时间
 * @property string $updated_at
 * @property string $created_at
 * @property string $deleted_at
 *
 * @package App\Models
 * @author  ensong.liu@vhall.com
 * @date    2019-02-15 13:46:18
 * @version
 *+----------------------------------------------------------------------
 */
class RecordStatsModel extends WebBaseModel
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $table = 'record_stats';

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
        foreach ($condition as $column => $value) {
            if ($value !== '' && $this->getConnection()->getSchemaBuilder()->hasColumn($this->getTable(), $column)) {
                if ($this->getTable() == 'room_attends' && in_array($column, ['begin_time', 'end_time'])) {
                    continue;
                }
                $model->where($this->getTable() . '.' . $column, '=', $value);
            }
        }

        return $model;
    }

    /**
     * 创建回放统计记录
     *
     * @param int    $ilId
     * @param int    $accountId
     * @param string $recordId
     * @param array  $data
     *
     * @return array|bool
     * @author ensong.liu@vhall.com
     * @date   2019-02-15 13:47:14
     *
     */
    public function createRecordStats(int $ilId, int $accountId, string $recordId, array $data)
    {
        $datetime           = date('Y-m-d H:i:s');
        $this->il_id        = $ilId;
        $this->account_id   = $accountId;
        $this->record_id    = $recordId;
        $this->flow         = $data['flow'];
        $this->pv_num       = $data['pv_num'];
        $this->uv_num       = $data['uv_num'];
        $this->duration     = $data['tt'];
        $this->created_time = $data['start_time'];
        $this->updated_at   = $datetime;
        $this->created_at   = $datetime;

        return !$this->save() ? false : $this->toArray();
    }

    /**
     * 获取指定字段的SUM值
     *
     * @param string    $column
     * @param int       $accountId
     * @param int|array $ilId
     * @param string    $beginTime
     * @param string    $endTime
     *
     * @return int|null
     * @author ensong.liu@vhall.com
     * @date   2019-02-15 17:03:50
     */
    public static function getColumnSum(
        string $column,
        int $accountId = 0,
        $ilId = 0,
        string $beginTime = '',
        string $endTime = ''
    ): int {
        $model = self::query();
        $model->when($accountId, function ($query) use ($accountId) {
            $query->where('account_id', $accountId);
        });
        $model->when($ilId, function ($query) use ($ilId) {
            is_array($ilId) ? $query->whereIn('il_id', $ilId) : $query->where('il_id', $ilId);
        });
        $model->when($beginTime, function ($query) use ($beginTime) {
            $query->where('created_time', '>=', date('Y-m-d H:i:s', strtotime($beginTime)));
        });
        $model->when($endTime, function ($query) use ($endTime) {
            $query->where('created_time', '<=', date('Y-m-d H:i:s', strtotime($endTime)));
        });

        return (int)$model->sum($column);
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
     * 修改
     *
     * @param $ids
     * @param $data
     *
     * @return bool|int
     */
    public function updateRecordStats($ids, $data)
    {
        $update             = [];
        $update['flow']     = $data['flow'];
        $update['pv_num']   = $data['pv_num'];
        $update['uv_num']   = $data['uv_num'];
        $update['duration'] = $data['tt'];

        $res = $this->whereIn('id', $ids)->update($update);

        if ($res === false) {
            return false;
        }
        return $res;
    }

    /**
     * 获取统计数据
     *
     * @param int    $account_id
     * @param int    $il_id
     * @param string $begin_time
     * @param string $end_time
     * @param string $format
     * @param int    $cache_time
     *
     * @return array
     */
    public function reCountListByCreatedTime($account_id = 0, $il_id = 0, $begin_time = '', $end_time = '')
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
        $data       = $collection ? $collection->toArray() : [];

        return $data;
    }

    /**
     * paas 数据同步
     *
     * @param array $item      要同步的数据
     * @param int   $ilId      房间 ID
     * @param int   $accountId 用户 ID
     *
     * @return array|bool
     * @author fym
     * @since  2021/6/17
     */
    public function syncData(array $item, int $ilId, int $accountId)
    {
        // 检查数据是否存在 record_stats 表中
        $recordStatsId = $this->where('created_time', $item['start_time'])
            ->where('record_id', $item['record_id'])
            ->value('id');

        // 存在，则更新, 不存在则新增
        if ($recordStatsId) {
            return vss_model()->getRecordStatsModel()->update([
                'flow'       => $item['flow'],
                'pv_num'     => $item['pv_num'],
                'uv_num'     => $item['uv_num'],
                'duration'   => $item['tt'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            return vss_model()->getRecordStatsModel()->createRecordStats(
                $ilId,
                $accountId,
                $item['record_id'],
                $item
            );
        }
    }
}
