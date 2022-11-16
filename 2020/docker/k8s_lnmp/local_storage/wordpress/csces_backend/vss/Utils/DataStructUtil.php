<?php

namespace Vss\Utils;

use App\Constants\ResponseCode;
use vhallComponent\decouple\models\WebBaseModel;
use Vss\Exceptions\ValidationException;

/**
 * 格式化数据结构
 * Class RspStructUtil
 * @package Vss\Utils
 */
class DataStructUtil
{
    const NIL = 'nil';

    protected $casts = [];

    /**
     * 分页字段，自动添加，忽略检查
     * @var string[]
     */
    protected $ignoreField = [
        'current_page',
        'per_page',
        'total'
    ];

    /**
     * 是否是严格模式， 严格模式下，缺失字段，并且没有设置默认值，会抛出错误
     * @var bool
     */
    protected $strict;

    public function __construct(array $casts, bool $strict = false)
    {
        $this->casts  = $casts;
        $this->strict = $strict;
    }

    /**
     * @param array $data 返回数据
     *
     * @return array
     * @throws ValidationException
     * @since  2021/7/13
     *
     * @author yaming.feng@vhall.com
     */
    public function cast(array $data): array
    {
        return $this->castStruct($data, $this->casts);
    }

    /**
     * @param array|WebBaseModel $data
     * @param array $casts
     *
     * @return array
     * @throws ValidationException
     * @author yaming.feng@vhall.com
     * @since  2021/7/13
     *
     */
    protected function castStruct($data, array $casts): array
    {
        if (!$data) {
            return $data;
        }

        if ($data instanceof WebBaseModel) {
            $data = $data->toArray();
        }

        // 列表数据
        if (isset($data[0])) {
            return $this->castList($data, $casts);
        }

        return $this->castDict($data, $casts);
    }

    /**
     * 列表数据校验
     *
     * @param array $data
     * @param array $casts
     *
     * @return array
     * @throws ValidationException
     * @author fym
     * @since  2021/7/16
     */
    protected function castList(array $data, array $casts): array
    {
        foreach ($data as $i => $value) {
            if (is_array($value) || $value instanceof WebBaseModel) {
                $data[$i] = $this->castStruct($value, $casts);
                continue;
            }

            $data[$i] = $this->castAttribute($i, $value, $casts);
        }
        return $data;
    }

    /**
     * 键值对数组校验
     *
     * @param array $data
     * @param array $casts
     *
     * @return array
     * @throws ValidationException
     * @author fym
     * @since  2021/7/16
     */
    protected function castDict(array $data, array $casts): array
    {
        // 删除定义结构中不存在的字段
        $data = array_intersect_key($data, array_merge($casts, array_flip($this->ignoreField)));

        foreach ($casts as $key => $type) {
            // 结构中的 key 在数据中不存在
            if (!isset($data[$key])) {
                // 非严格模式直接跳过
                if (!$this->strict) {
                    continue;
                }

                // 严格模式，获取默认值，如果没有默认值，则抛出错误
                $val = $this->getDefaultVal($type);
                if ($val == self::NIL) {
                    if (is_numeric($key)) {
                        continue;
                    }
                    vss_logger()->error('data_struct_error: ', [
                        'data' => $data,
                        'casts' => $casts
                    ]);
                    throw new ValidationException(ResponseCode::TYPE_INVALID_RSP, ['field' => $key]);
                }
                $data[$key] = $this->castAttribute($key, $val, $casts);
                continue;
            }

            if (is_array($data[$key])) {
                $data[$key] = $this->castStruct($data[$key], $type);
                continue;
            }

            $data[$key] = $this->castAttribute($key, $data[$key], $casts);
        }

        return $data;
    }

    /**
     * @param string $type
     *
     * @return string
     * @since  2021/7/13
     *
     * @author yaming.feng@vhall.com
     */
    protected function getDateFormat(string $type = 'datetime'): string
    {
        if ($type == 'date') {
            return 'Y-m-d';
        }

        return 'Y-m-d H:i:s';
    }

    /**
     * @param $type
     *
     * @return mixed
     * @since  2021/7/13
     *
     * @author yaming.feng@vhall.com
     */
    protected function getDefaultVal($type)
    {
        if (is_array($type)) {
            return [];
        }

        if (strpos($type, 'default:') === false) {
            return self::NIL;
        }

        $keys = explode('|', $type);
        foreach ($keys as $key) {
            if (strpos($key, 'default:') === false) {
                continue;
            }
            return explode(':', $key)[1];
        }

        return null;
    }

    /**
     * @param $key
     *
     * @return string
     * @since  2021/7/13
     *
     * @author yaming.feng@vhall.com
     */
    protected function getCastType($key): string
    {
        return explode(':', explode('|', $key)[0])[0];
    }

    /**
     * @param       $key
     * @param       $value
     * @param array $casts 数据的校验规则
     *
     * @return bool|float|int|mixed|string
     * @since  2021/7/13
     *
     * @author yaming.feng@vhall.com
     */
    protected function castAttribute($key, $value, array $casts)
    {
        $type     = $casts[$key] ?? last($casts);
        $castType = $this->getCastType($type);

        switch ($castType) {
            case 'int':
                return (int)$value;
            case 'float':
                return $this->toFloat($value);
            case 'decimal':
                return $this->toDecimal($value, explode(':', $type, 2)[1]);
            case 'string':
                return (string)$value;
            case 'bool':
                return (bool)$value;
            case 'date':
            case 'datetime':
                return $this->toDate(explode('|', $type)[0], $value, $castType);
            case 'timestamp':
                return (int)(is_numeric($value) ? $value : strtotime($value));
        }

        return $value;
    }

    /**
     * @param $value
     *
     * @return float|int
     * @since  2021/7/13
     *
     * @author yaming.feng@vhall.com
     */
    protected function toFloat($value)
    {
        switch ((string)$value) {
            case 'Infinity':
                return INF;
            case '-Infinity':
                return -INF;
            case 'NaN':
                return NAN;
            default:
                return (float)$value;
        }
    }

    /**
     * @param $value
     * @param $decimals
     *
     * @return string
     * @since  2021/7/13
     *
     * @author yaming.feng@vhall.com
     */
    protected function toDecimal($value, $decimals): string
    {
        return number_format($value, $decimals, '.', '');
    }

    /**
     * @param $type
     * @param $val
     * @param $castType
     *
     * @return false|mixed|string
     * @since  2021/7/13
     *
     * @author yaming.feng@vhall.com
     */
    protected function toDate($type, $val, $castType)
    {
        if (strpos($type, ':')) {
            $format = substr($type, strpos($type, ':') + 1);
            if (!is_numeric($val)) {
                $val = strtotime($val);
            }
            if (!$format) {
                $format = $this->getDateFormat($castType);
            }
            $val = date($format, $val);
        }
        return $val;
    }
}
