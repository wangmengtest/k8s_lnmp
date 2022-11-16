<?php

namespace App\Component\export\src\models;

use Illuminate\Database\Eloquent\Builder;
use vhallComponent\decouple\models\WebBaseModel;

/**
 * ExportModel
 *
 * @property int $id
 * @property int $il_id      直播id
 * @property int $account_id 操作人id
 * @property string  $file_name  文件名
 * @property string  $title      标题
 * @property string  $export     导出模块:message 消息，
 * @property string  $params     执行参数
 * @property int $status     执行状态,1.未执行；2.执行中；3.已完成
 * @property string  $ext        文件类型
 * @property string  $oss_file   上传后的文件地址
 * @property string  $callback   注册导出函数， 如: qa:getQaExportData
 * @property string  $updated_at
 * @property string  $created_at
 * @property string  $deleted_at
 *
 * @uses     yangjin
 * @date     2020-10-28
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class ExportModel extends WebBaseModel
{
    protected $table = 'export';

    protected $attributes = [
        'id'         => null,
        'il_id'      => null,
        'account_id' => null,
        'file_name'  => '',
        'title'      => '',
        'export'     => '',
        'status'     => 1,
        'ext'     => '',
        'created_at' => '0000-00-00 00:00:00',
        'updated_at' => '0000-00-00 00:00:00',
    ];

    protected $appends = ['file_url'];

    protected static function boot()
    {
        parent::boot();
        self::creating(function (self $data) {
            $data->title = json_encode($data->title);
        });
    }

    /**
     * 条件构造器
     *
     * @param Builder $model
     * @param array   $condition
     *
     * @return Builder
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 15:14:34
     *
     */
    protected function buildCondition(Builder $model, array $condition): Builder
    {
        $model = parent::buildCondition($model, $condition);

        $model->when($condition['il_id'] ?? '', function (Builder $query) use ($condition) {
            $query->where('il_id', $condition['il_id']);
        });

        $model->when($condition['account_id'] ?? '', function (Builder $query) use ($condition) {
            $query->where('account_id', $condition['account_id']);
        });

        $model->when($condition['export'] ?? '', function (Builder $query) use ($condition) {
            $query->where('export', $condition['export']);
        });

        $model->when($condition['status'] ?? '', function (Builder $query) use ($condition) {
            $query->where('status', $condition['status']);
        });

        //时间范围-开始
        $model->when($condition['begin_time'] ?? '', function (Builder $query) use ($condition) {
            $query->where('created_at', '>=', $condition['begin_time']);
        });
        //时间范围-结束
        $model->when($condition['end_time'] ?? '', function (Builder $query) use ($condition) {
            $query->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime($condition['end_time'])));
        });

        return $model;
    }

    public function getFileUrlAttribute()
    {
        return $this->oss_file;
        //return vss_config('application.url') . '/upload/export/' . $this->file_name . '.csv';
    }
}
