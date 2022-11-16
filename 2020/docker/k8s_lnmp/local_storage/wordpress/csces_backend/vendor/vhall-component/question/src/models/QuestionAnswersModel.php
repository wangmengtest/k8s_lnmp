<?php

namespace vhallComponent\question\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class QuestionsModel
 * @package App\Models
 * @property string $answer_id
 * @property int $join_id 参会id
 * @property int $room_id 房间id
 * @property int $question_id 问卷id
 * @property string $extend 业务扩展字段
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class QuestionAnswersModel extends WebBaseModel
{
    protected $table = 'question_answers';

    public $incrementing = false;

    protected $attributes = [
        'answer_id'=>null,
        'join_id'=>null,
        'room_id'=>null,
        'question_id'=>null,
        'extend'=>null,
        'created_at'=>'0000-00-00 00:00:00',
        'updated_at'=>'0000-00-00 00:00:00',
    ];

    protected $primaryKey = 'answer_id';

    /**
     * 查询各个问题的回答数
     * @auther yaming.feng@vhall.com
     * @date 2021/1/19
     * @param array $questionIds 问题 Id 数组
     * @param string $roomId 房间 Id
     * @return array ['question_id' => 'count']
     */
    public function getAnswerCountByQuestionIds($questionIds, $roomId)
    {
        $answersCountList = $this->newQuery()
            ->when($roomId, function ($query) use ($roomId) {
                $query->where('room_id', $roomId);
            })
            ->whereIn('question_id', (array)$questionIds)
            ->groupBy(['question_id'])
            ->selectRaw('question_id, count(*) as c')
            ->get()
            ->toArray();

        return array_column($answersCountList, 'c', 'question_id');
    }
}
