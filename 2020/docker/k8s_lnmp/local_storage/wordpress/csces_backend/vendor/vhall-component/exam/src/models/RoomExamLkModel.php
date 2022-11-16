<?php

namespace vhallComponent\exam\models;

use Illuminate\Database\Eloquent\Builder;
use vhallComponent\decouple\models\WebBaseModel;

/**
 * RoomExamLkModel
 * @property int $exam_id          试卷id
 * @property string $room_id          PAAS直播房间id
 * @property int $account_id       用户ID
 * @property int $publish          是否发布 0-未发布 1-已发布
 * @property int $publish_time     发布时间
 * @property int $bind          是否绑定 0-未绑定 1-已绑定
 * @property int $is_finish        是否结束 0-未结束 1-已结束
 * @property int $finish_time     发布时间
 * @uses     yangjin
 * @date     2020-07-23
 * @author   jin.yang@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class RoomExamLkModel extends WebBaseModel
{
    protected $table = 'room_exam_lk';

    protected $attributes = [
        'id'           => null,
        'exam_id'      => null,
        'room_id'      => null,
        'account_id'   => null,
        'is_finish'    => 0,
        'finish_time'  => 0,
        'publish'      => 0,
        'publish_time' => 0,
        'bind'         => 0,
        'answer'       => 0,
        'extend'       => null,
        'created_at'   => '0000-00-00 00:00:00',
        'updated_at'   => '0000-00-00 00:00:00',
    ];

    protected static function boot()
    {
        self::updated(function (self $data) {
            $data->putCache('InfoByRoomIdAndExamId', $data->room_id . 'and' . $data->exam_id, $data->getAttributes());
        });
        self::saved(function (self $data) {
            $data->putCache('InfoByRoomIdAndExamId', $data->room_id . 'and' . $data->exam_id, $data->getAttributes());
        });
        self::deleted(function (self $data) {
            $data->deleteCache('InfoByRoomIdAndExamId', $data->room_id . 'and' . $data->exam_id);
        });
        parent::boot();
    }

    /**
     * @param $room_id
     * @param $exam_id
     *
     * @return $this
     */
    public function findByRoomIdAndExamId($room_id, $exam_id)
    {
        $attributes = $this->getCache(
            'InfoByRoomIdAndExamId',
            $room_id . 'and' . $exam_id,
            function () use ($room_id, $exam_id) {
                $model = $this->where(['room_id' => $room_id, 'exam_id' => $exam_id])->first();

                return $model ? $model->getAttributes() : null;
            }
        );

        return empty($attributes) ? null : $this->newInstance($attributes, true)->syncOriginal();
    }

    /**
     * 获取试卷数量
     * @param $where
     * @return int
     */
    public function getExamCount($where)
    {
        $query = self::query();
        if (!empty($where['begin_date'])) {
            $query->where('created_at', '>=', "{$where['begin_date']}");
            unset($where['begin_date']);
        }
        if (!empty($where['end_date'])) {
            $query->where('created_at', '<=', "{$where['end_date']} 23:59:59");
            unset($where['end_date']);
        }
        $data = $query->where($where)->count();

        return $data;
    }

    /**
     * 通过exam_id进行删除
     * @param $examId
     * @return int
     */
    public function deleteByExamId($examId)
    {
        $list  = $this->getList(['exam_id' => $examId], [], null, ['id', 'room_id', 'exam_id']);
        $count = 0;
        foreach ($list as $exam) {
            if ($exam->delete()) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * 联查 exams 表 list
     * @param $condition
     * @param $columns
     * @param int $page
     * @param int $pagesize
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function joinExamsList($condition, $columns, $page = 1, $pagesize = 10, $type = 'inner')
    {
        $model = self::query()->join('exams', 'exams.exam_id', '=', 'room_exam_lk.exam_id', $type);
        $model = $this->joinCondition($model, $condition);
        $model->where($condition);
        $list = $model->orderBy('room_exam_lk.id', 'desc')->paginate($pagesize, $columns, 'page', $page);
        return $list;
    }

    /**
     * 联查exams 条件构造
     * @param Builder $model
     * @param array $condition
     * @return Builder
     */
    protected function joinCondition(Builder $model, array &$condition)
    {
        if (isset($condition['keyword']) && !empty($condition['keyword'])) {
            $model->where(function (Builder $query) use ($condition) {
                $query->where('room_exam_lk.exam_id', 'like', sprintf('%%%s%%', $condition['keyword']))
                    ->orWhere('exams.title', 'like', "%{$condition['keyword']}%");
            });
            unset($condition['keyword']);
        }
        if (!empty($condition['begin_time'])) {
            $model->where('room_exam_lk.created_at', '>=', $condition['begin_time']);
            unset($condition['begin_time']);
        }
        if (!empty($condition['end_time'])) {
            $model->where('room_exam_lk.created_at', '<=', date('Y-m-d 23:59:59', strtotime($condition['end_time'])));
            unset($condition['end_time']);
        }

        return $model;
    }

    /**
     * 联查 exams,rooms 表 list
     * @param $condition
     * @param $columns
     * @param int $page
     * @param int $pagesize
     * @param string $type
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function joinExamsAndRoomsList($condition, $columns, $page = 1, $pagesize = 10, $type = 'inner')
    {
        $model = self::query()
            ->join('exams', 'room_exam_lk.exam_id', '=', 'exams.exam_id', $type)
            ->join('rooms', 'room_exam_lk.room_id', '=', 'rooms.room_id', $type);
        $model = $this->joinCondition($model, $condition);
        $model->where($condition);
        $list = $model->orderBy('room_exam_lk.id', 'desc')->paginate($pagesize, $columns, 'page', $page);
        return $list;
    }

    /**
     * 联查 exams,rooms 表信息
     * @param $condition
     * @param $columns
     * @return array
     */
    public function joinExamsAndRoomsInfo($condition, $columns)
    {
        $model = self::query()
            ->join('exams', 'room_exam_lk.exam_id', '=', 'exams.exam_id')
            ->join('rooms', 'room_exam_lk.room_id', '=', 'rooms.room_id')
            ->where($condition)->first($columns);
        if (empty($model)) {
            return [];
        }
        return $model->toArray();
    }
}
