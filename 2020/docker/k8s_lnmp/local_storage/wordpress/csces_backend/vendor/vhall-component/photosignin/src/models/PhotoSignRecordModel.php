<?php

namespace vhallComponent\photosignin\models;

use Illuminate\Database\Eloquent\Builder;
use vhallComponent\decouple\models\WebBaseModel;

class PhotoSignRecordModel extends WebBaseModel
{
    protected $primaryKey = 'id';
    protected $table      = 'photo_sign_record';

    //某个签到任务签到详情(已签到人员列表或未签到人员列表)
    public function taskDetail($condition, $page, $pageSize = 10, $columns = ['*'], $source = '')
    {
        $model = $this->buildCondition(self::query(), $condition);

        if (1 == $condition['status']) {//已签到 按签到时间倒叙排序
            $model->orderBy('sign_time', 'desc');
            $model->orderBy('id', 'desc');
        } else {
            $model->orderBy('user_id', 'asc');
        }

        $list = $model->paginate($pageSize, $columns, 'page', $page)->toArray();
        if (!empty($list['data'])) {
            $userIds  = array_column($list['data'], 'user_id');
            $imgModel = new PhotoSignImgModel();
            $imgList  = $imgModel->where(['sign_id' => $condition['sign_id']])->whereIn('user_id',
                $userIds)->get(['user_id', 'img_url'])->toArray();
            $temp     = [];
            foreach ($imgList as $imgv) {
                $temp[$imgv['user_id']][] = $imgv['img_url'];
            }

            foreach ($list['data'] as &$val) {
                if ($val['sign_time'] > 0) {
                    $val['sign_time'] = in_array($source, ['android', 'ios']) ? date('Y/m/d H:i:s',
                        $val['sign_time']) : date('Y-m-d H:i:s', $val['sign_time']);
                }
                $val['img_list'] = $temp[$val['user_id']] ?: [];

                $val['phone'] = empty($val['phone']) ? '****' : substr_replace($val['phone'], '****', 3, 4);
            }
        }

        return $list;
    }

    //签到任务详情 根据条件获取所有用户签到记录
    public function taskDetailTotal($condition, $page, $pageSize = 10, $columns = ['*'])
    {
        $model = $this->buildCondition(self::query(), $condition);

        $model->orderBy('sign_time', 'desc');

        $list = $model->paginate($pageSize, $columns, 'page', $page)->toArray();
        if (!empty($list['data'])) {
            $userIds  = array_column($list['data'], 'user_id');
            $imgModel = new PhotoSignImgModel();
            $imgList  = $imgModel->where(['sign_id' => $condition['sign_id']])->whereIn('user_id',
                $userIds)->get(['user_id', 'img_url'])->toArray();
            $temp     = [];
            foreach ($imgList as $imgv) {
                $temp[$imgv['user_id']][] = $imgv['img_url'];
            }

            foreach ($list['data'] as &$val) {
                if ($val['sign_time'] > 0) {
                    $val['sign_time'] = date('Y-m-d H:i:s', $val['sign_time']);
                }

                //目前用户最多可以上传5张照片，在导出excel等地方需要展示用户所有图片列，没有的为空(对应导出列图片1、图片2、....共5列)
                for ($i = 0; $i <= 4; ++$i) {
                    $val['img' . ($i + 1)] = $temp[$val['user_id']][$i] ?: '';
                }
            }
        }

        return $list;
    }

    protected function buildCondition(Builder $model, array $condition): Builder
    {
        $model = parent::buildCondition($model, []);

        //签到id
        if (!empty($condition['sign_id'])) {
            $model->where('sign_id', '=', $condition['sign_id']);
        }

        //签到状态
        if (isset($condition['status'])) {
            $model->where('status', '=', $condition['status']);
        }

        //昵称
        if (!empty($condition['nickname'])) {
            $model->where('nickname', 'like', '%' . $condition['nickname'] . '%');
        }

        return $model;
    }
}
