<?php

//namespace vhallComponent\exam;
namespace vhallComponent\exam\models;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class ExamAnswersModel
 *
 * @package App\Models
 * @property string $answer_id
 * @property int $join_id        参会id
 * @property int $room_id        房间id
 * @property int $exam_id        试卷id
 * @property int $account_id     答题用户id
 * @property string $nickname       答题人昵称
 * @property string $avatar         答题人头像
 * @property string $extend         业务扩展字段
 *
 * @property int $answerer_score 回答分数
 * @property int $elect_score 客观题分数
 * @property int $is_graded      是否批阅
 * @property string $graded_mark    批阅记录
 * @property int $operator_account_id      操作人用户ID
 * @property string $operator_nickname    操作人昵称
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class ExamAnswersModel extends WebBaseModel
{
    protected $table = 'exam_answers';

    public $incrementing = false;

    protected $attributes = [
        'answer_id'           => null,
        'join_id'             => null,
        'room_id'             => null,
        'exam_id'             => null,
        'account_id'          => 0,
        'nickname'            => '',
        'avatar'              => '',
        'extend'              => '',
        'answerer_score'      => 0,
        'elect_score'         => 0,
        'is_graded'           => 0,
        'graded_mark'         => '',
        'operator_account_id' => 0,
        'operator_nickname'   => '',
        'created_at'          => '0000-00-00 00:00:00',
        'updated_at'          => '0000-00-00 00:00:00',
    ];

    protected $primaryKey = 'answer_id';

    protected static function boot()
    {
        parent::boot();
        self::created(function (self $model) {
            $model->putCache('InfoByAnswerId', $model->answer_id, $model->getAttributes());
        });
        self::updated(function (self $model) {
            $model->putCache('InfoByAnswerId', $model->answer_id, $model->getAttributes());
        });
        self::saved(function (self $model) {
            $model->putCache('InfoByAnswerId', $model->answer_id, $model->getAttributes());
        });
        self::deleted(function (self $model) {
            $model->deleteCache('InfoByAnswerId', $model->answer_id);
        });
    }

    /**
     * 通过exam_id 获取信息
     * @param $examId
     * @return ExamsModel|null
     */
    public function findByAnswerId($answerId)
    {
        $attributes = $this->getCache('InfoByAnswerId', $answerId, function () use ($answerId) {
            $model = $this->find($answerId);
            return $model ? $model->getAttributes() : null;
        });

        return empty($attributes) ? null : $this->newInstance($attributes, true)->syncOriginal();
    }

    /**
     * 答卷联查直播间用户
     * @param $condition
     * @param $columns
     * @param int $page
     * @param int $pagesize
     * @param string $type
     * @param string $order
     * @param string $sort
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function joinRoomJoinsList($condition, $columns, $page = 1, $pagesize = 10, $type = 'inner', $order = 'exam_answers.answerer_score', $sort = 'desc')
    {
        $model = self::query()->join('room_joins', 'room_joins.join_id', '=', 'exam_answers.join_id', $type);
        //关键字查询
        if (isset($condition['keyword']) && !empty($condition['keyword'])) {
            $model->where(function (Builder $query) use ($condition) {
                $query->where('room_joins.nickname', 'like', "%{$condition['keyword']}%")
                    ->orWhere('room_joins.username', 'like', "%{$condition['keyword']}%");
            });

            unset($condition['keyword']);
        }
        $model->where($condition);
        $list = $model->orderBy($order, $sort)->paginate($pagesize, $columns, 'page', $page);
        return $list;
    }

    /**
     * 答卷关联试卷
     * @param $condition
     * @param $columns
     * @param int $page
     * @param int $pagesize
     * @param string $type
     * @param string $order
     * @param string $sort
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function joinExamsList($condition, $columns, $page = 1, $pagesize = 10, $type = 'inner', $order = 'exam_answers.answer_id', $sort = 'desc')
    {
        $model = self::query()->join('exams', 'exams.exam_id', '=', 'exam_answers.exam_id', $type);
        //关键字查询
        if (isset($condition['keyword']) && !empty($condition['keyword'])) {
            $model->where(function (Builder $query) use ($condition) {
                $query->where('exams.title', 'like', "%{$condition['keyword']}%")
                    ->orWhere('exam_answers.nickname', 'like', "%{$condition['keyword']}%");
            });

            unset($condition['keyword']);
        }
        $model->where($condition);
        $list = $model->orderBy($order, $sort)->paginate($pagesize, $columns, 'page', $page);
        return $list;
    }

    /**
     * 答题人数
     *
     * @param $params
     *
     * @return int
     *
     */
    public function getAnswerCount($where)
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
        $count = $query->where($where)->count(['answer_id']);
        return $count;
    }
}
