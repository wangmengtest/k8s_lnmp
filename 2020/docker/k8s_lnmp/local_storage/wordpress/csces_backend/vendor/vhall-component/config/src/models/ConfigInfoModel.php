<?php

namespace vhallComponent\config\models;

use Illuminate\Database\Eloquent\Model;
use vhallComponent\decouple\models\WebBaseModel;

/**
 * ConfigInfoModel
 *
 * @uses     yangjin
 * @date     2020-08-25
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class ConfigInfoModel extends WebBaseModel
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $table = 'config_info';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['key', 'value'];

    /**
     * 获取配置
     *
     * @param $key
     *
     * @return Model|ConfigInfoModel|null
     * @author  jin.yang@vhall.com
     * @date    2020-08-25
     */
    public function getValue($key)
    {
        $value = $this->getRow(['key' => $key]);
        if (empty($value)) {
            return '';
        }
        return $value->getAttributeValue('value');
    }

    public function setConfig($id, $key, $value)
    {
        return $this->where(['id' => $id, 'key' => $key])->update(['value' => $value]);
    }
}
