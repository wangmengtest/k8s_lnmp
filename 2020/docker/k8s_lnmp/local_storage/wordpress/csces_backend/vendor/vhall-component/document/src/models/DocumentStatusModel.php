<?php
/**
 * File Name: DocumentStatus.php
 * Author: songyue
 * mail: songyue118@gmail.com
 * Created Time: 2018年09月19日 星期三 10时22分14秒
 */

namespace vhallComponent\document\models;

use Illuminate\Database\Eloquent\Builder;
use vhallComponent\decouple\models\WebBaseModel;

/**
 * 文档开关记录日志表
 * This is the model class for table "document_status".
 *
 * @property int  $id
 * @property int  $account_id
 * @property int  $il_id
 * @property int  $status
 * @property int  $document_id
 * @property datetime $updated_at
 * @property datetime $created_at
 */
class DocumentStatusModel extends WebBaseModel
{
    protected $table = 'document_status';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'ko_id';

    /**
     * 文档开启状态
     */
    const STATUS_OPEN  = 1; //开启

    const STATUS_CLOSE = 0; //关闭

    /**
     * 条件构造器
     *
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param array                                 $condition
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 15:14:34
     *
     */
    protected function buildCondition(Builder $model, array $condition): Builder
    {
        $model = parent::buildCondition($model, $condition);

        return $model;
    }

    /**
     * 记录文档加载上报
     *
     * @param $accountId
     * @param $il_id
     *
     * @return array|bool
     */
    public function insert($accountId, $il_id, $document_id)
    {
        $datetime          = date('Y-m-d H:i:s');
        $this->account_id  = $accountId;
        $this->document_id = $document_id;
        $this->status      = self::STATUS_OPEN;
        $this->il_id       = $il_id;
        $this->updated_at  = $datetime;
        $this->created_at  = $datetime;
        if (!$this->save()) {
            return false;
        }

        return $this->toArray();
    }

    /**
     * 查询回放是否有文档
     *
     * @param $recodeId
     * @param $il_id
     *
     * @return int
     * @throws \Exception
     */
    public function findExistsByRecordId($recodeId, $il_id)
    {
        if (empty($recodeId)) {
            return 0;
        }
        // 根据回放id调用paas接口查询回放信息
        try {
            $recodeInfo = [];
            //回放是否存在
            # vhallEOF-record-DocumentStatusModel-findExistsByRecordId-1-start
        
            $recodeInfo = vss_model()->getRecordModel()->getInfoByVodId($recodeId);

        # vhallEOF-record-DocumentStatusModel-findExistsByRecordId-1-end
        } catch (\Exception $e) {
            return 0;
        }

        if (empty($recodeInfo)) {
            return 0;
        }

        // $recodeInfo['created_at']  是回放创建时间，也就是结束时间，那么开始时间应该为结束时间减掉‘直播时长’
        $end_time   = $recodeInfo['created_at'];
        $begin_time = date('Y-m-d H:i:s', strtotime($end_time . ' - ' . ($recodeInfo['duration'] + 5) . ' seconds'));

        $model = $this->where('il_id', $il_id)->whereBetween('created_at', [$begin_time, $end_time])->exists();

        return (int)$model;
    }
}
