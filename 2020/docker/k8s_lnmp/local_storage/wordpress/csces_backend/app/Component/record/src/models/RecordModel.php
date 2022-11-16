<?php

namespace App\Component\record\src\models;

use Illuminate\Database\Eloquent\Builder;
use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class RoleModel
 *
 * @package App\Models
 * @property int    $id
 * @property int    $account_id       用户id
 * @property string $room_id          房间活动id
 * @property bool   $source           来源: 0=回放，1=上传， 2=录制
 * @property string $vod_id           视频文件id
 * @property string $name             文件名
 * @property bool   $transcode_status 文件状态:0新增排队中 1转码成功 2转码失败 3转码中 4转码部分成功
 * @property int    $duration         时长/秒
 * @property int    $storage          存储量/KB
 * @property bool   $status           状态 0：正常   1：删除
 * @property string $updated_at
 * @property string $created_at
 * @property string $deleted_at
 * @property int    $il_id            房间id
 * @property string $created_time     统计时间
 */
class RecordModel extends WebBaseModel
{
    protected $table = 'record';

    protected $primaryKey = 'id';

    protected $attributes = [
        'account_id'       => '',
        'il_id'            => 0,
        'vod_id'           => '',
        'name'             => '',
        'storage'          => '',
        'transcode_status' => 0,
        'status'           => 0,
        'room_id'          => '',
        'duration'         => '',
        'created_at'       => '1970-01-01 00:00:00',
        'updated_at'       => '1970-01-01 00:00:00',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        //全局作用域-过滤掉存储为零的记录
        /*static::addGlobalScope('storage', function (Builder $builder) {
            $builder->where('record.storage', '>', 0);
        });*/
        self::updated(function (self $model) {
            $model->putCache('InfoByVodId', $model->vod_id, $model->getAttributes());
        });

        //绑定删除事件
        static::deleted(function (self $model) {
            //重置房间默认回放
            vss_model()->getRoomsModel()->updateRow($model->il_id, [
                'record_id' => '',
            ]);
            //获取回放对应的统计信息参数
            $condition         = [
                'il_id'     => $model->il_id,
                'record_id' => $model->record_id,
            ];
            $RecordAttendsInfo = vss_model()->getRecordAttendsModel()->getRow($condition);
            $RecordStatsInfo   = vss_model()->getRecordStatsModel()->getRow($condition);
            if ($RecordAttendsInfo) {
                //删除回放统计
                vss_model()->getRecordAttendsModel()->delRow($RecordAttendsInfo->id);
            }
            if ($RecordStatsInfo) {
                //删除回放访问记录
                vss_model()->getRecordStatsModel()->delRow($RecordStatsInfo->id);
            }
            self::getInstance()->deleteCache('InfoByVodId', $model->vod_id);
            //删除PAAS回放
            (new static())->syncDelRecord($model->vod_id);
        });
    }

    /**
     * 文档是否存在-访问器
     *
     * @return null|int
     * @author ensong.liu@vhall.com
     * @date   2019-02-21 16:27:07
     */
    public function getDocumentExistAttribute(): int
    {
        $isExist = 0;
        # vhallEOF-document-RecordModel-getDocumentExistAttribute-1-start

        $isExist = vss_model()->getDocumentStatusModel()->findExistsByRecordId($this->record_id, $this->il_id);

        # vhallEOF-document-RecordModel-getDocumentExistAttribute-1-end
        return $isExist;
    }

    /**
     * 是否默认回放-访问器
     *
     * @return int|null
     * @author ensong.liu@vhall.com
     * @date   2019-02-21 20:12:21
     */
    public function getIsDefaultAttribute(): int
    {
        $condition = [
            'il_id'     => $this->il_id,
            'record_id' => $this->record_id,
        ];

        return vss_model()->getRoomsModel()->getCount($condition) > 0 ? 1 : 0;
    }

    /**
     * 同步删除回放
     *
     * @param string $recordId
     *
     * @return bool
     * @author ensong.liu@vhall.com
     * @date   2019-02-21 20:00:54
     *
     */
    public function syncDelRecord(string $recordId): bool
    {
        try {
            $data['app_id'] = vss_service()->getTokenService()->getAppId();
            $data['vod_id'] = $recordId;
            vss_service()->getPaasService()->recordDel($data);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function createRecord($roomInfo, $data)
    {
        $datetime               = date('Y-m-d H:i:s');
        $this->il_id            = $roomInfo['il_id'];
        $this->room_id          = $roomInfo['room_id'];
        $this->account_id       = $roomInfo['account_id'];
        $this->vod_id           = $data['vod_id'];
        $this->name             = $data['name'];
        $this->transcode_status = $data['transcode_status'];
        $this->duration         = $data['duration'];
        $this->storage          = $data['storage'];
        $this->source           = $data['source'];
        $this->created_time     = $data['created_at'];
        $this->updated_at       = $datetime;
        $this->created_at       = $datetime;
        if (!$this->save()) {
            return false;
        }

        return $this->toArray();
    }

    /**
     * 获取直播间最后一条回放信息
     *
     * @see    http://doc.vhall.com/docs/show/1485
     * @author ensong.liu@vhall.com
     * @date   2019-01-22 11:05:53
     *
     * @param string $ilId
     * @param array  $roomInfo
     *
     * @return bool|array
     * @throws \Exception
     */
    public function getLastRecord($ilId, array $roomInfo = [])
    {
        $record = false;
        //直播间信息
        $roomInfo = $roomInfo ?: vss_model()->getRoomsModel()->getRow(['il_id' => $ilId]);
        if ($roomInfo['status'] == 2) {
            $record = bcsub(strtotime($roomInfo['end_live_time']), strtotime($roomInfo['begin_live_time']));
        }
        if ($roomInfo['status'] == 1) {
            $streamStatus = vss_service()->getPaasService()->getStreamStatus($roomInfo['room_id']);
            if ($streamStatus[$roomInfo['room_id']]['stream_status'] == 1) {//推流中
                $record = bcsub(time(), strtotime($roomInfo['begin_live_time']));
            } else {
                $record = bcsub(
                    strtotime($streamStatus[$roomInfo['room_id']]['end_time']),
                    strtotime($streamStatus[$roomInfo['room_id']]['push_time'])
                );
            }
        }
        return $record;
    }

    /**
     * 获取点播时长统计
     *
     *
     * @param array $condition
     *
     * @return int
     */
    public function getDurationSum(array $condition = []): int
    {
        return (int)$this->buildCondition(self::query(), $condition)->sum('duration');
    }

    /**
     * 获取回放存储空间
     *
     * @param array $condition
     *
     * @return float
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 16:03:01
     *
     */
    public static function getStorageSum(array $condition = []): float
    {
        return (float)(new self())->buildCondition(self::query(), $condition)->sum('storage');
    }

    /**
     * 同步房间回放数据
     *
     * @param int $ilId
     *
     * @return bool
     * @author ensong.liu@vhall.com
     * @date   2019-02-21 19:28:54
     *
     */
    public function syncRecords(int $ilId): bool
    {
        $roomInfo = vss_model()->getRoomsModel()->getRow(['il_id' => $ilId]);
        if (!empty($roomInfo)) {
            try {
                $beginDate           = date('Y-m-d 00:00:00', strtotime('-1 year'));
                $endDate             = date('Y-m-d H:i:s', time());
                $params['room_id']   = $roomInfo['room_id'];
                $params['starttime'] = $beginDate;
                $params['endtime']   = $endDate;
                $recordList          = vss_service()->getPaasService()->getAllRecordList($params);
                vss_logger()->info('huifang', $recordList);
                array_walk($recordList, function ($record) use ($roomInfo) {
                    $count = self::query()
                        ->where('account_id', $roomInfo['account_id'])
                        ->where('il_id', $roomInfo['il_id'])
                        ->where('vod_id', $record['vod_id'])
                        ->count();
                    if ($count <= 0) {
                        self::createRecord($roomInfo, $record);
                    }
                });
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

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
        $model->when($condition['search'] ?? '', function (Builder $query) use ($condition) {
            $query->where(function ($queryNew) use ($condition) {
                $queryNew->where('name', 'like', sprintf('%%%s%%', $condition['search']))
                    ->orWhere('vod_id', $condition['search']);
            });

        });

        // record_id搜索
        $model->when(
            (isset($condition['record_id']) && !empty($condition['record_id'])),
            function (Builder $query) use ($condition) {
                $query->where('vod_id', '=', $condition['record_id']);
            }
        );
        // source in 搜索
        $model->when(
            (isset($condition['source_in']) && !empty($condition['source_in'])),
            function (Builder $query) use ($condition) {
                $query->whereIn('source', $condition['source_in']);
            }
        );

        //时间范围-开始
        $model->when($condition['begin_time'] ?? '', function (Builder $query) use ($condition) {
            $query->where('created_time', '>=', $condition['begin_time']);
        });

        //时间范围-开始
        $model->when($condition['start_time'] ?? '', function (Builder $query) use ($condition) {
            $query->where('created_time', '>=', $condition['start_time']);
        });
        //时间范围-结束
        $model->when($condition['end_time'] ?? '', function (Builder $query) use ($condition) {
            $query->where('created_time', '<=', date('Y-m-d 23:59:59', strtotime($condition['end_time'])));
        });
        $model->where('record.storage', '>', 0);

        return $model;
    }

    public function deleteIds($ids, $force = true)
    {
        $build = self::query()->whereIn('vod_id', $ids);
        if ($force) {
            return $build->forcedelete();
        }
        return $build->delete();
    }

    /**
     * 通过ID获取信息
     *
     * @param $vodId
     *
     * @return array
     */
    public function getInfoByVodId($vodId): array
    {
        return $this->getCache('InfoByVodId', $vodId, function () use ($vodId) {
            $model = $this->where('vod_id', $vodId)->first();

            return $model ? $model->getAttributes() : [];
        });
    }

    /**
     * 数据同步
     *
     * @param array $item
     *
     * @return bool
     * @author fym
     * @since  2021/6/17
     */
    public function syncData(array $item): bool
    {
        $info = $this->getInfoByVodId($item['vod_id']);
        if (!$info) {
            $roomInfo = vss_model()->getRoomsModel()->findByRoomId($item['room_id']);
            return (bool)$this->createRecord($roomInfo, $item);
        }
        return false;
    }
}
