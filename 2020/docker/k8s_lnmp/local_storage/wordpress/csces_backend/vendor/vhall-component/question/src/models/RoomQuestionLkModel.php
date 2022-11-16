<?php

namespace vhallComponent\question\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class RoomQuestionLkModel
 * @package App\Models
 * @property int    $id
 * @property int    $question_id 问卷id
 * @property string $room_id     房间id
 * @property string $finish_time 结束时间
 * @property int    $publish     是否发布，1是0否
 * @property int    $bind        是否绑定，1是0否
 * @property int    $answer      是否有答案，1是0否
 * @property string $extend      扩展业务字段
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class RoomQuestionLkModel extends WebBaseModel
{
    protected $table = 'room_question_lk';

    protected $attributes = [
        'id'          => null,
        'question_id' => null,
        'room_id'     => null,
        'finish_time' => null,
        'publish'     => 0,
        'bind'        => 0,
        'answer'      => 0,
        'extend'      => null,
        'created_at'  => '0000-00-00 00:00:00',
        'updated_at'  => '0000-00-00 00:00:00',
    ];

    protected static function boot()
    {
        self::created(function (self $data) {
            $data->putCache('InfoByRoomIdAndQuestionId', $data->room_id . 'and' . $data->question_id,
                $data->getAttributes());
        });
        self::updated(function (self $data) {
            $data->putCache('InfoByRoomIdAndQuestionId', $data->room_id . 'and' . $data->question_id,
                $data->getAttributes());
        });
        self::deleted(function (self $data) {
            $data->deleteCache('InfoByRoomIdAndQuestionId', $data->room_id . 'and' . $data->question_id);
        });
        parent::boot();
    }

    /**
     * @param $room_id
     * @param $question_id
     *
     * @return $this
     */
    public function findByRoomIdAndQuestionId($room_id, $question_id)
    {
        $attributes = $this->getCache('InfoByRoomIdAndQuestionId', $room_id . 'and' . $question_id,
            function () use ($room_id, $question_id) {
                $model = $this->where(['room_id' => $room_id, 'question_id' => $question_id])->first();
                return $model ? $model->getAttributes() : null;
            });
        return empty($attributes) ? null : $this->newInstance($attributes, true)->syncOriginal();
    }

    /**
     * Notes: 根据room_id获取问卷数量
     * User: michael
     * Date: 2019/8/21
     * Time: 19:12
     *
     * @param $room_id
     *
     * @return int
     */
    public function getQuestionNum($params)
    {
        // $data['by_room_num'] = $data['by_account_num'] = 0;
        /* if(!empty($params['room_id'])){
             $condition = ['room_id' => $params['room_id'],['created_at','>=',"{$params['begin_date']}"],['created_at','<=',"{$params['end_date']} 23:59:59"]];
         }else{
             $condition = [['created_at','>=',"{$params['begin_date']}"],['created_at','<=',"{$params['end_date']} 23:59:59"]];
         }
         $data['by_room_num'] = $this->where($condition)->count();
        */

        //问卷数量
        if (!empty($params['account_id'])) {
            $data['by_account_num'] = $this->where([
                'account_id' => $params['account_id'],
                'publish'    => 1,
                ['created_at', '>=', "{$params['begin_date']}"],
                ['created_at', '<=', "{$params['end_date']} 23:59:59"]
            ])->count();
        }
        return empty($data) ? [] : $data;
    }

    /**
     * 检查问卷是否已发布
     * @auther yaming.feng@vhall.com
     * @date 2021/1/25
     *
     * @param $questionId
     *
     * @return bool
     */
    public function questionIsPublish($questionId)
    {
        $roomQuestionLkId = $this->where('question_id', $questionId)
            ->where('publish', 1)
            ->whereNull('deleted_at')
            ->value('id');

        return boolval($roomQuestionLkId);
    }

    /**
     * 查询问卷的发布状态
     * @auther yaming.feng@vhall.com
     * @date 2021/1/29
     *
     * @param array  $questionIds
     * @param string $roomId
     *
     * @return mixed
     */
    public function getQuestionPublishStatus($questionIds, $roomId = '')
    {
        $list = $this->newQuery()
            ->when($roomId, function ($query) use ($roomId) {
                $query->where('room_id', $roomId);
            })
            ->whereIn('question_id', (array)$questionIds)
            ->select(['publish', 'question_id'])
            ->get()
            ->toArray();

        return array_column($list, 'publish', 'question_id');
    }

    /**
     * 查询主持人的所有房间，如果有和指定问卷ID关联的房间，关联房间放在第一条
     * @auther yaming.feng@vhall.com
     * @date 2021/6/2
     *
     * @param int         $page       当前页码
     * @param int         $pageSize   每页条数
     * @param int         $accountId  主持人 ID
     * @param int         $questionId 问卷 ID
     * @param null|string $keyword    查询的关键词
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getLinkRoomList($page, $pageSize, $accountId, $questionId = 0, $keyword = null)
    {
        $field = [
            'rooms.il_id',
            'rooms.room_id',
            'rooms.subject',
            'rooms.status',
            'rooms.created_at',
            'lk.question_id'
        ];

        $list = vss_model()->getRoomsModel()->newQuery()
            ->leftJoin('room_question_lk as lk', function ($join) use ($questionId) {
                $join->on('rooms.room_id', '=', 'lk.room_id')
                    ->where('lk.question_id', $questionId)
                    ->whereNull('lk.deleted_at');
            })
            ->where('rooms.account_id', $accountId)
            ->when($keyword, function ($query) use ($keyword) {
                $keyword = "%{$keyword}%";
                $query->where(function ($query) use ($keyword) {
                    $query->where('rooms.subject', 'like', $keyword)
                        ->orWhere('rooms.il_id', 'like', $keyword);
                });
            })
            ->orderByDesc('lk.question_id')
            ->orderByDesc('rooms.il_id')
            ->paginate($pageSize ?: 1, $field, 'page', $page)
            ->toArray();

        return $list;
    }
}
