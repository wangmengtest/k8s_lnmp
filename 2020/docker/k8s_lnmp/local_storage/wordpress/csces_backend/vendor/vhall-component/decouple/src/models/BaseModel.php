<?php
/**
 * Created by PhpStorm.
 * User: gaoningning
 * Date: 2018/8/3
 * Time: 15:18
 */

namespace vhallComponent\decouple\models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;

/**
 * Class BaseModel
 * @package core\common
 * @method $this get()
 * @method $this create($data)
 * @method $this find($id)
 * @method $this delete()
 * @method $this forceDelete()
 * @method Builder|$this where(...$where)
 * @method Builder orWhere(...$where)
 * @method Builder select($field)
 * @method Builder|$this whereIn($field, $fields)
 * @method Builder updateOrCreate($where, $data) static
 */
class BaseModel extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s'
    ];

    /**
     * @return string
     */
    public function getTable()
    {
        if (!$this->table) {
            return strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', class_basename($this)));
        }
        return $this->table;
    }

    /**
     * 为数组 / JSON 序列化准备日期。
     * 在 Eloquent 模型上使用 toArray 或 toJson 方法时，Laravel 7 将使用新的日期序列化格式。为了格式化日期以进行序列化，
     * Laravel 将会使用 Carbon 的 toJSON 方法，该方法将生成与 ISO-8601 兼容的日期，包括时区信息及小数秒。
     * 造成的结果是使用模型查询时 created_at, updated_at 比数据小 8 小时 bug
     *
     * @param  \DateTimeInterface  $date
     *
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
