<?php

namespace vhallComponent\exam\models;

use Illuminate\Database\Eloquent\Builder;
use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class ExamModel
 *
 * @property int $exam_id
 * @property string $title      标题
 * @property string $desc       简介
 * @property string $extend     业务端扩展字段
 * @property int $account_id 用户id
 * @property int $is_public  是否公共试卷，1是0否，默认是
 * @property string $source_id  来源id
 * @property string $score  试卷总分
 * @property string $question_num  试卷数量
 * @property string $limit_time  考试限时时长 默认0 为不限时
 * @property string $type  试卷类型 0：试卷库 1:考试
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class ExamsModel extends WebBaseModel
{
    //试卷类型 0：试卷  1：考试
    const TYPE_PAPER = 0;

    const TYPE_EXAM = 1;

    protected $table = 'exams';

    public $incrementing = false;

    protected $attributes = [
        'exam_id'      => null,
        'title'        => null,
        'desc'         => '',
        'extend'       => null,
        'account_id'   => null,
        'source_id'    => null,
        'score'        => 0,
        'question_num' => 0,
        'limit_time'   => 0,
        'type'         => 0,
        'is_public'    => 1,
        'created_at'   => '0000-00-00 00:00:00',
        'updated_at'   => '0000-00-00 00:00:00',
    ];

    protected $primaryKey = 'exam_id';

    protected static function boot()
    {
        parent::boot();
        self::created(function (self $model) {
            $model->putCache('InfoByExamId', $model->exam_id, $model->getAttributes());
        });
        self::updated(function (self $model) {
            $model->putCache('InfoByExamId', $model->exam_id, $model->getAttributes());
        });
        self::saved(function (self $model) {
            $model->putCache('InfoByExamId', $model->exam_id, $model->getAttributes());
        });
        self::deleted(function (self $model) {
            vss_model()->getRoomExamLkModel()->deleteByExamId($model->exam_id);
            $model->deleteCache('InfoByExamId', $model->exam_id);
        });
    }

    /**
     * @param $examId
     * @param $data
     * @return bool|array
     */
    public function saveByExamId($examId, $data)
    {
        $save = $this->updateOrCreate(['exam_id' => $examId], $data);
        if (!$save) {
            vss_logger()->error($save->errorInfo);
            return false;
        }
        return $save->toArray();
    }

    /**
     * 通过exam_id 获取信息
     * @param $examId
     * @return ExamsModel|null
     */
    public function findByExamId($examId)
    {
        $attributes = $this->getCache('InfoByExamId', $examId, function () use ($examId) {
            $model = $this->find($examId);
            return $model ? $model->getAttributes() : null;
        });

        return empty($attributes) ? null : $this->newInstance($attributes, true)->syncOriginal();
    }

    /**
     * 条件构造器
     *
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param array $condition
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildCondition(Builder $model, array $condition): Builder
    {
        $model = parent::buildCondition($model, $condition);
        //关键字搜索
        $model->when($condition['keyword'], function ($query) use ($condition) {
            $query->where('title', 'like', "%{$condition['keyword']}%")
                ->orWhere('exam_id', 'like', sprintf('%%%s%%', $condition['keyword']));
        });
        //时间范围-开始
        $model->when($condition['begin_time'], function ($query) use ($condition) {
            $query->where('created_at', '>=', $condition['begin_time']);
        });
        //时间范围-结束
        $model->when($condition['end_time'], function ($query) use ($condition) {
            $query->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime($condition['end_time'])));
        });

        return $model;
    }
}
