<?php

namespace vhallComponent\photosignin\models;

use Illuminate\Database\Eloquent\Builder;
use vhallComponent\decouple\models\WebBaseModel;

class PhotoSignTaskModel extends WebBaseModel
{
    protected $primaryKey = 'id';
    protected $table      = 'photo_sign_task';

    public function taskList($condition, $page, $pageSize = 10, $columns = ['*'], $source = '')
    {
        $list = $this->buildCondition(self::query(), $condition)
            ->orderBy('id', 'desc')
            ->paginate($pageSize, $columns, 'page', $page)->toArray();
        if (!empty($list['data'])) {
            $recordModel    = new PhotoSignRecordModel();
            $signIds        = array_column($list['data'], 'id');
            $recordInfoList = $recordModel->selectRaw('sign_id,status,count(*) as num')->whereIn('sign_id', $signIds)
                ->groupBy('sign_id')->groupBy('status')->get()->toArray();
            $temp           = [];
            foreach ($recordInfoList as $record) {
                if (1 == $record['status']) {//签到人数
                    $temp[$record['sign_id']]['sign_num'] = $record['num'];
                } else {
                    $temp[$record['sign_id']]['no_sign_num'] = $record['num'];
                }
            }

            foreach ($list['data'] as &$val) {
                if (0 == $val['status']) {//签到中
                    $val['sign_num']    = '--';
                    $val['no_sign_num'] = '--';
                } else {
                    $val['no_sign_num'] = $temp[$val['id']]['no_sign_num'] ?: 0;
                    $val['sign_num']    = $temp[$val['id']]['sign_num'] ?: 0;
                }

                if (in_array($source, ['android', 'ios'])) {
                    $val['begin_time'] = date('Y/m/d H:i:s', $val['begin_time']);
                } else {
                    $val['begin_time'] = date('Y-m-d H:i:s', $val['begin_time']);
                }
            }
        }

        return $list;
    }

    protected function buildCondition(Builder $model, array $condition): Builder
    {
        $model = parent::buildCondition($model, []);

        //签到任务创建者uid
        if (!empty($condition['user_id'])) {
            $model->where('user_id', '=', $condition['user_id']);
        }

        //签到任务所属room_id
        if (!empty($condition['room_id'])) {
            $model->where('room_id', '=', $condition['room_id']);
        }

        return $model;
    }
}
