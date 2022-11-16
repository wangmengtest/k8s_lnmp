<?php

namespace vhallComponent\question\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class QuestionsModel
 * @package App\Models
 * @property int $question_id
 * @property string  $title       标题
 * @property string  $description 简介
 * @property string  $cover       封面
 * @property string  $extend      业务端扩展字段
 * @property int $account_id  用户id
 * @property int $is_public   是否公共问卷，1是0否，默认是
 * @property string  $source_id   来源id
 * @property string  $created_at
 * @property string  $updated_at
 * @property string  $deleted_at
 */
class QuestionsModel extends WebBaseModel
{
    protected $table        = 'questions';

    public $incrementing = false;

    protected $attributes = [
        'question_id' => null,
        'title'       => null,
        'description' => null,
        'cover'       => null,
        'extend'      => null,
        'account_id'  => null,
        'source_id'   => null,
        'is_public'   => 1,
        'created_at'  => '0000-00-00 00:00:00',
        'updated_at'  => '0000-00-00 00:00:00'
    ];

    protected $primaryKey = 'question_id';

    protected static function boot()
    {
        self::created(function (QuestionsModel $data) {
            $data->putCache('InfoByQuestionId', $data->question_id, $data->getAttributes());
        });
        self::updated(function (self $data) {
            $data->putCache('InfoByQuestionId', $data->question_id, $data->getAttributes());
        });
        self::deleted(function (self $data) {
            $data->deleteCache('InfoByQuestionId', $data->question_id);
        });
        parent::boot();
    }

    /**
     * @param $question_id
     *
     * @return $this
     */
    public function findByQuestionId($question_id)
    {
        $attributes = $this->getCache('InfoByQuestionId', $question_id, function () use ($question_id) {
            $model = $this->find($question_id);
            return $model ? $model->getAttributes() : null;
        });
        return empty($attributes) ? null : $this->newInstance($attributes, true)->syncOriginal();
    }

    /**
     * @param $source_id
     *
     * @return $this
     */
    public function findBySourceId($source_id)
    {
        $question_id = $this->getCache('InfoBySourceId', $source_id, function () use ($source_id) {
            $model = $this->where(compact('source_id'))->first();
            return $model ? $model->question_id : null;
        });
        return empty($question_id) ? null : $this->findByQuestionId($question_id);
    }
}
