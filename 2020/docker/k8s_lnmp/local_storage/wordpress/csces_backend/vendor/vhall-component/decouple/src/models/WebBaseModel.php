<?php

namespace vhallComponent\decouple\models;

use Illuminate\Database\Eloquent\Builder;
use Vss\Traits\CacheTrait;
use Vss\Traits\SingletonTrait;

class WebBaseModel extends BaseModel
{
    use CacheTrait;
    use SingletonTrait;

    /**
     * 是否删除
     */
    const ACTIVE = 0; //否

    const INACTIVE = 1; //是

    /**
     * 获取一条记录
     *
     * @author ensong.liu@vhall.com
     * @date   2019-01-30 15:53:06
     *
     * @param array $condition
     * @param array $with
     *
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function getRow(array $condition, array $with = [])
    {
        return $this->buildCondition($this->newQuery(), $condition)->with($with)->first();
    }

    /**
     * 新增记录
     *
     * @author ensong.liu@vhall.com
     * @date   2019-02-16 21:05:41
     *
     * @param array $attributes
     *
     * @return static|null
     */
    public function addRow(array $attributes)
    {
        return $this->create($attributes);
    }

    /**
     * 删除一条记录
     *
     * @author ensong.liu@vhall.com
     * @date   2019-02-21 13:44:58
     *
     * @param int $primaryId 主键id
     * @param bool $force true:物理删除 false:软删除
     *
     * @return bool
     */
    public function delRow(int $primaryId, $force = false)
    {
        $model = $this->getRow([$this->getKeyName() => $primaryId]);
        if (!$model) {
            return false;
        }
        if ($force == true) {
            return $model->forceDelete() ?: false;
        }
        if ($force == false) {
            return $model->update(['is_delete' => self::INACTIVE, 'isdelete' => self::INACTIVE]) ?: false;
        }
    }

    /**
     * 修改一条记录
     *
     * @author ensong.liu@vhall.com
     * @date   2019-02-16 21:14:50
     *
     * @param int $primaryId
     * @param array $attributes
     *
     * @return bool
     */
    public function updateRow(int $primaryId, array $attributes = []): bool
    {
        $model = $this->getRow([$this->getKeyName() => $primaryId]);
        return $model ? $model->update($attributes) : false;
    }

    /**
     * 获取列表
     *
     * @author ensong.liu@vhall.com
     * @date   2019年02月14日20:03:53
     *
     * @param array $condition
     * @param array $with
     * @param null $page
     * @param array $columns
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getList(array $condition = [], array $with = [], $page = null, $columns = ['*'])
    {
        return $this->buildCondition($this->newQuery(), $condition)
            ->with($with)
            ->orderBy($this->getTable() . '.' . $this->getKeyName(), 'desc')
            ->paginate($this->getPerPage(), $columns, 'page', $page);
    }

    /**
     * 获取记录数
     *
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 16:46:21
     *
     * @param array $condition
     *
     * @return int
     */
    public function getCount(array $condition = []): int
    {
        return $this->buildCondition($this->newQuery(), $condition)->count();
    }

    /**
     * 获取记录(不分页)
     *
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 16:46:21
     *
     * @param array $condition
     *
     * @return int
     */
    public function getListByFilter(array $condition = [], $fields = [])
    {
        return $this->buildCondition($this->newQuery(), $condition)->get($fields);
    }

    /**
     * 获取列表
     *
     * @param array $condition
     * @param array $with
     * @param array $columns
     *
     * @return mixed
     */
    public function getListForColumns(array $condition = [], array $with = [], $columns = ['*'])
    {
        return $this->buildCondition($this->newQuery(), $condition)
            ->with($with)
            ->orderBy($this->getTable() . '.' . $this->getKeyName(), 'desc')
            ->get($columns);
    }

    /**
     * 条件构造器
     *
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 15:14:34
     *
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param array $condition
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildCondition(Builder $model, array $condition): Builder
    {
        $this->cacheExpire['TableColumn'] = 60;
        //当前表字段条件构建
        foreach ($condition as $column => $value) {
            if($value === ""){
                continue;
            }

            $tableColumn = $this->getCache('TableColumn', $this->getTable(), function () {
                $column = $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
                return $column;
            });

            $hasColumn = in_array(
                strtolower($column), array_map('strtolower', $tableColumn)
            );
            if ($hasColumn) {
                if ($this->getTable() == 'room_attends' && in_array($column, ['begin_time', 'end_time'])) {
                    continue;
                }
                $model->where($this->getTable() . '.' . $column, '=', $value);
            }
        }

        return $model;
    }
}
