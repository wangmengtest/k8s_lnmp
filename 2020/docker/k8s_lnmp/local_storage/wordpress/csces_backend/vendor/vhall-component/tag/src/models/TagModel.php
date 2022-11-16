<?php

/**
 * 标签
 *Created by PhpStorm.
 *DATA: 2019/11/7 14:31
 */
namespace vhallComponent\tag\models;

use Illuminate\Database\Eloquent\Builder;
use vhallComponent\tag\constants\TagConstant;
use vhallComponent\decouple\models\WebBaseModel;

class TagModel extends WebBaseModel
{
    protected $table      = 'tag';

    protected $primaryKey = 'tag_id';

    protected $attributes = [
        'name'       => '',
        'status'     => 0,
        'created_at' => '1970-01-01 00:00:00',
        'updated_at' => '1970-01-01 00:00:00',
        'type'       => 0,
        'use_count'  => 0,
    ];

    protected $fillable = ['name', 'status', 'app_id', 'use_count', 'type', 'tag_id'];

    /**
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param array                                 $condition
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @author   ming.wang@vhall.com
     *
     * @uses     wangming
     */
    protected function buildCondition(Builder $model, array $condition): Builder
    {
        $model = parent::buildCondition($model, $condition);

        //名字搜索
        $model->when($condition['name']??'', function (Builder $query) use ($condition) {
            $query->where('name', 'like', sprintf('%%%s%%', $condition['name']))
                ->orWhere('tag_id', 'like', sprintf('%%%s%%', $condition['name']));
        });

        $model->when($condition['type'] == TagConstant::COMMON_LABEL, function (Builder $query) use ($condition) {
            $query->orderBy('use_count', 'desc')->orderBy(
                'created_at',
                'desc'
            );
        });

        $model->when($condition['type'] == TagConstant::CUSTOM_LABEL, function (Builder $query) use ($condition) {
            $query->orderBy('status', 'asc')->orderBy('created_at', 'asc');
        });

        return $model;
    }

    /**
     * 获取用户信息
     *
     * @param $ids
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     * @author   ming.wang@vhall.com
     *
     * @uses     wangming
     */
    public function getInfo($ids)
    {
        return self::query()->whereIn('tag_id', $ids)->get();
    }

    /**
     * 批量删除
     *
     * @param      $ids
     * @param bool $force
     *
     * @return mixed
     * @author   ming.wang@vhall.com
     *
     * @uses     wangming
     */
    public function deleteIds($ids, $force = true)
    {
        $infoBuilder = self::query()->whereIn('tag_id', $ids);
        if ($force) {
            $result = $infoBuilder->forceDelete();
        } else {
            $result = $infoBuilder->delete();
        }

        return $result;
    }
}
