<?php

namespace App\Http\Modules\Health\models;
use vhallComponent\decouple\models\WebBaseModel;

/**
 * @property integer $id
 * @property integer $val      随机值
 *
 * Class OpsMonitorModel
 * mysql健康检测
 */
class OpsMonitorModel extends WebBaseModel
{
    public $timestamps = false;

    protected $table = 'ops_monitor';

    protected $attributes = [
        'id'         => null,
        'val'        => null,
    ];
}
