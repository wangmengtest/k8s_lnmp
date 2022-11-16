<?php

namespace vhallComponent\room\models;

use Illuminate\Database\Eloquent\Builder;
use vhallComponent\record\models\RecordModel;
use vhallComponent\room\constants\RoomConstant;
use vhallComponent\account\models\AccountsModel;
use vhallComponent\decouple\models\WebBaseModel;

/**
 *+----------------------------------------------------------------------
 * Class RoomsModel
 * 房间模型类
 *+----------------------------------------------------------------------
 *
 * @author  ensong.liu@vhall.com
 * @date    2019-06-09 16:08:25
 * @version v1.0.0
 *+----------------------------------------------------------------------
 * @property int    $il_id            房间id
 * @property string $room_id          PAAS直播房间id
 * @property string $subject          房间标题
 * @property int    $account_id       用户id
 * @property string $inav_id          PAAS互动房间id
 * @property string $channel_id       PAAS频道ID
 * @property string $nify_channel     PAAS通知消息频道ID
 * @property string $record_id        默认回放id
 * @property string $start_time       预计开始时间
 * @property string $introduction     直播介绍
 * @property bool   $category         所属列表
 * @property string $cover_image      封面图片地址
 * @property string $topics           标签,多个逗号隔开
 * @property bool   $layout           布局>1|为单视频,2|音频+文档,3|文档+视频
 * @property bool   $status           状态>0|待直播/预约,1|直播中,2|直播结束
 * @property bool   $is_delete        是否删除>0|否,1|是
 * @property bool   $message_approval 聊天审核 1允许 2阻止
 * @property string $created_at
 * @property string $updated_at
 * @property string $app_id           paasAppId
 * @property int    $like             点赞数
 * @property string $deleted_at
 * @property bool   $live_type        直播类型 1 互动直播 2 纯直播
 * @property bool   $warm_type        暖场类型| 0：图片 1：视频
 * @property string $warm_vod_id      暖场视频id
 * @property string $teacher_name     讲师名称
 * @property string $begin_live_time  直播开始时间
 * @property string $end_live_time    直播结束时间
 * @property bool   $is_open_document 开启文档>0|未开启,1|已开启
 * @property int    $live_mode        直播模式
 * @property int    $message_total    聊天总数
 * @property bool   $mode             模式>1|助理模式,0|普通模式
 * @property string $assistant_sign   助理口令
 * @property string $interaction_sign 互动口令
 */
class RoomsModel extends WebBaseModel
{
    /**
     * @var string
     */
    protected $table = 'rooms';

    protected $primaryKey = 'il_id';

    protected $attributes = [
        'il_id'            => 0,
        'room_id'          => null,
        'subject'          => null,
        'account_id'       => null,
        'inav_id'          => null,
        'channel_id'       => null,
        'nify_channel'     => null,
        'record_id'        => '',
        'start_time'       => '0000-00-00 00:00:00',
        'introduction'     => null,
        'category'         => 0,
        'cover_image'      => '',
        'topics'           => '',
        'layout'           => 1,
        'status'           => 0,
        'is_delete'        => 0,
        'message_approval' => 0,
        'created_at'       => '0000-00-00 00:00:00',
        'updated_at'       => '0000-00-00 00:00:00',
        'app_id'           => null,
        'live_type'        => 1,
        'like'             => 0,
        'warm_type'        => 0,
        'warm_vod_id'      => null,
        'mode'             => 1,
        'is_open_document' => 0,
        'limit_type'       => 0,
    ];

    protected $appends = [
        'name',
        'desc',
        'image',
        'view_url',
        'watch_url',
        'status_str',
        'live_room_type_str',
        'begin_time',
    ];

    /**
     * @var string
     */
    private $begin_time;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        self::created(function (self $data) {
            RoomSupplyModel::create([
                'il_id'      => $data->il_id,
                'room_id'    => $data->room_id,
                'account_id' => $data->account_id,
                'mode'       => $data->mode,
                'live_type'  => $data->live_type,
            ]);
            $data->putCache('InfoByIlId', $data->il_id, $data->getAttributes());
        });
        self::saved(function (self $data) {
            $data->putCache('InfoByIlId', $data->il_id, $data->getAttributes());
        });
        self::updated(function (RoomsModel $data) {
            vss_logger()->info('func_self_log', ['data' => $data]);

            if ($data->status != $data->getOriginal('status')) {
                if ($data->status == 1) {
                    vss_service()->getInavService()->clearGlobal($data->room_id);
                    vss_service()->getInavService()->initGlobal($data->room_id);
                } else {
                    vss_service()->getInavService()->clearGlobal($data->room_id);
                    if ($data->status == 2) {

                        # vhallEOF-redpacket-RoomsModel-boot-1-start
        
                        $redpacketService = vss_service()->getRedpacketService();
                        $redpacketService->overBySourceId(["app_id" => $data->app_id, "source_id" => $data->room_id]);

        # vhallEOF-redpacket-RoomsModel-boot-1-end
                    }
                }
            }

            $data->putCache('InfoByIlId', $data->il_id, $data->getAttributes());
        });
        self::deleted(function (self $data) {
            $data->deleteCache('InfoByRoomId', $data->room_id);
            $data->deleteCache('InfoByIlId', $data->il_id);
            $data->deleteCache('InfoByInavId', $data->inav_id);

            if ($data->isForceDeleting()) {
                vss_service()->getPaasService()->roomDelete($data->room_id);
                vss_service()->getPaasService()->inavDelete($data->inav_id);
                vss_service()->getPaasService()->channelDelete($data->channel_id);
            }
        });

        parent::boot();
    }

    /**
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getBeginTimeAttribute()
    {
        return $this->begin_time = $this->start_time;
    }

    /**
     * @return string
     */
    public function getDescAttribute()
    {
        return $this->introduction;
    }

    /**
     * 房间海报页地址属性-访问器
     *
     * @return string
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 14:09:50
     */
    public function getViewUrlAttribute(): string
    {
        return sprintf(
            '%s/watch/%d',
            rtrim(vss_config('application.host'), '/'),
            $this->getAttribute('il_id')
        );
    }

    /**
     * 房间观看页页地址属性-访问器
     *
     * @return string
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 14:09:50
     */
    public function getWatchUrlAttribute(): string
    {
        return sprintf(
            '%s/watch/%d',
            rtrim(vss_config('application.host'), '/'),
            $this->getAttribute('il_id')
        );
    }

    /**
     * 状态字符串-访问器
     *
     * @return string
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 14:09:50
     */
    public function getStatusStrAttribute(): string
    {
        if (is_null($this->status)) {
            return '';
        }

        switch ($this->status) {
            case RoomConstant::STATUS_WAITING:
                $string = '待直播';
                break;
            case RoomConstant::STATUS_START:
                $string = '直播中';
                break;
            case RoomConstant::STATUS_STOP:
                $string = '已结束';
                break;
            default:
                $string = null;
        }

        return $string;
    }

    /**
     *图片地址-访问器
     *
     * @return string
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 14:09:50
     */
    public function getImageAttribute(): string
    {
        $image = $this->cover_image;
        if (!strpos($image, '://')) {
            $image = $this->cover_image ? sprintf(
                '%s%s',
                rtrim(vss_config('application.url'), '/'),
                $this->cover_image
            ) : '';
        }
        return $image;
    }

    /**
     * 图片地址-修改器
     *
     * @param string $value
     *
     * @return string
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 14:09:50
     */
    public function setImageAttribute($value): string
    {
        return $this->attributes['image'] = $value ? parse_url($value, PHP_URL_PATH) : '';
    }

    /**
     * 房间等级-访问器
     *
     * @return string
     * @author shilong.zhang@vhall.com
     * @date   2019-11-18 14:09:50
     */
    public function getLiveRoomTypeStrAttribute(): string
    {
        if (is_null($this->getAttribute('live_room_type'))) {
            return '';
        }
        $liveRoomTypeInfo = RoomConstant::LIVE_ROOM_TYPE;
        if (isset($liveRoomTypeInfo[$this->getAttribute('live_room_type')])) {
            return $liveRoomTypeInfo[$this->getAttribute('live_room_type')];
        }

        return '';
    }

    /**
     * 模型关联-用户表
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @author ensong.liu@vhall.com
     * @date   2019年02月14日19:50:17
     */
    public function account()
    {
        return $this->belongsTo(AccountsModel::class, 'account_id', 'account_id');
    }

    /**
     * 获取房间信息
     *
     * @param $roomId
     *
     * @return $this|null
     */
    public function findByRoomId($roomId)
    {
        $ilId = $this->getCache('InfoByRoomId', $roomId, function () use ($roomId) {
            $ilId = $this->where('room_id', $roomId)->value('il_id');
            return $ilId;
        });

        // 之前的缓存存储的是对象，这是为了兼容之前逻辑,后续可以删除
        if (is_array($ilId)) {
            $ilId = $this->where('room_id', $roomId)->value('il_id');
            $this->putCache('InfoByRoomId', $roomId, $ilId);
        }

        return $ilId ? $this->getInfoByIlId($ilId) : null;
    }

    /**
     * 依据 inav_id 获取房间信息
     *
     * @param $inavId
     *
     * @return $this|null
     * @author fym
     * @since  2021/6/16
     */
    public function findByInavId($inavId)
    {
        $ilId = $this->getCache('InfoByInavId', $inavId, function () use ($inavId) {
            $ilId = $this->where('inav_id', $inavId)->value('il_id');
            return $ilId;
        });

        return $ilId ? $this->getInfoByIlId($ilId) : null;
    }

    /**
     * 依据il_id获取房间信息
     *
     * @param $ilId
     *
     * @return $this|null
     */
    public function getInfoByIlId($ilId)
    {
        $attributes = $this->getCache('InfoByIlId', $ilId, function () use ($ilId) {
            $model = $this->where('il_id', $ilId)->first();

            return $model ? $model->getAttributes() : null;
        });

        return empty($attributes) ? null : $this->newInstance($attributes, true)->syncOriginal();
    }

    /**
     * 依据il_id 和 account_id获取房间信息
     *
     * @param        $ilId
     * @param string $accountId
     *
     * @return array
     */
    public function getInfoByIlIdAndAccountId($ilId, $accountId = '')
    {
        $info = $this->getInfoByIlId($ilId);
        if ($info && $accountId && $info['account_id'] != $accountId) {
            return [];
        }
        return $info->toArray();
    }

    /**
     * 依据account_id 和 il_id获取房间列表信息
     *
     * @param        $accountId
     * @param string $ilId
     *
     * @return array
     */
    public function getListByAccountIdAndIlId($accountId, $ilId = '')
    {
        $model = $this->where('is_delete', 0);
        if ($accountId) {
            $model = $model->where('account_id', $accountId);
        }
        if ($ilId) {
            $model = $model->where('il_id', $ilId);
        }

        $model = $model->get();
        if (empty($model)) {
            return [];
        }

        return $model->toArray();
    }

    public function updateIsOpenDocument($accountId, $ilId, $isOpenDocument)
    {
        $model = $this->where('il_id', $ilId)->where('account_id', $accountId)->first();
        if (empty($model)) {
            return [];
        }
        $model->is_open_document = $isOpenDocument;
        if (!$model->save()) {
            return false;
        }

        return $model->toArray();
    }

    /**
     * 房间点赞数缓存
     * @return int
     */
    public function getLikeNumAttribute()
    {
        return (vss_redis()->get(RoomConstant::LIKE . $this->room_id) ?: 0) + $this->like;
    }

    public function findRoomStatusById($roomId)
    {
        $model = $this->whereIn('room_id', explode(',', $roomId))->select('room_id', 'status')->get()->toArray();

        return $model;
    }

    public function getInteractiveLivesAll($begindate, $enddate)
    {
        $model = $this->where('end_live_time', '>=', $begindate)->where('end_live_time', '<', $enddate);
        $model->orwhere(function (Builder $query) {
            $query->whereRaw('end_live_time < begin_live_time OR end_live_time IS NULL')
                ->where('status', RoomConstant::STATUS_START);
        });
        $model = $model->orderBy('il_id', 'desc');
        $model = $model->get();
        if (empty($model)) {
            return [];
        }

        return $model->toArray();
    }

    /**
     * 计算某个用户下的房间聊天信息总数
     *
     * @param int    $accountId
     * @param int    $ilId
     *
     * @param string $beginTime
     * @param string $endTime
     *
     * @return Object
     */
    public static function sumMessageTotalByAccountId($accountId, $ilId = 0, $beginTime = '', $endTime = '')
    {
        $model = self::where('account_id', $accountId);

        if ($ilId) {
            $model->where('il_id', $ilId);
        }
        if (!empty($beginTime)) {
            $beginDate = $beginTime;
            if (!empty($endTime)) {
                $endData = $endTime . ' 23:59:59';
            } else {
                $endData = $beginDate . ' 23:59:59';
            }
            $model->whereBetween('created_at', [$beginDate, $endData]);
        }

        return $model->sum('message_total');
    }

    /**
     * 条件构造器
     *
     * @param array                                 $condition
     *
     * @param \Illuminate\Database\Eloquent\Builder $model
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 15:14:34
     *
     */
    protected function buildCondition(Builder $model, array $condition): Builder
    {
        //查找结束直播并设置了回放的房间
        if ($condition['status'] == RoomConstant::V_STATUS_RECORD) {
            $model->where('status', RoomConstant::STATUS_STOP)
                ->where('record_id', '!=', '');
            unset($condition['status']);
        }
        //当前表字段条件构建
        $model = parent::buildCondition($model, $condition);

        $model->when(
            isset($condition['keyword']) && !empty($condition['keyword']),
            function (Builder $query) use ($condition) {
                $query->leftJoin(AccountsModel::getInstance()
                        ->getTable() . ' as accounts', 'rooms.account_id', '=', 'accounts.account_id')
                    ->where(function (Builder $query) use (
                        $condition
                    ) {
                        $query->where('rooms.il_id', 'like', sprintf('%%%s%%', $condition['keyword']))
                            ->orWhere('rooms.subject', 'like', sprintf('%%%s%%', $condition['keyword']))
//                            ->orWhere('accounts.username', 'like', sprintf('%%%s%%', $condition['keyword']))
                            ->orWhere('accounts.nickname', 'like', sprintf('%%%s%%', $condition['keyword']));
                    });
            }
        );

        $model->when(isset($condition['created']) && !empty($condition['created']), function ($query) use ($condition) {
            if (!empty($condition['created'][0])) {
                if (empty($condition['created'][1])) {
                    $condition['created'][1] = date('Y-m-d H:i:s');
                }
                $query->whereBetween('created_at', $condition['created']);
            }
        });

        $model->when(
            isset($condition['account_id']) && is_array($condition['account_id']),
            function ($query) use ($condition) {
                $query->whereIn('account_id', $condition['account_id']);
            }
        );

        //时间范围-开始
        $model->when(
            isset($condition['created_at_begin']) && !empty($condition['created_at_begin']),
            function (Builder $query) use ($condition) {
                $query->where('rooms.created_at', '>=', $condition['created_at_begin']);
            }
        );
        //时间范围-结束
        $model->when(
            isset($condition['created_at_end']) && !empty($condition['created_at_end']),
            function (Builder $query) use ($condition) {
                $query->where(
                    'rooms.created_at',
                    '<=',
                    date('Y-m-d 23:59:59', strtotime($condition['created_at_end']))
                );
            }
        );
        $model->when(
            isset($condition['record_id']) && $condition['record_id'] === '',
            function (Builder $query) use ($condition) {
                $query->where('rooms.record_id', '=', '');
            }
        );

        return $model;
    }

    /**
     * 同步流状态
     *
     * @param $roomStreamStatus
     *
     * @return bool
     */
    public function syncStreamStatus($roomStreamStatus)
    {
        // 1 为推流状态,这里判断非推流状态才做处理
        if ($roomStreamStatus['stream_status'] != 1) {
            // 查询当前房间
            $info = $this->where('room_id', $roomStreamStatus['room_id'])->first();
            // 如果存在
            if (!empty($info)) {
                // 修改状态为"停止"
                $info->status = RoomConstant::STATUS_STOP;
                if ($roomStreamStatus['push_time'] != '0000-00-00 00:00:00') {
                    $info->begin_live_time = $roomStreamStatus['push_time'];
                }
                if ($roomStreamStatus['end_time'] != '0000-00-00 00:00:00') {
                    $info->end_live_time = $roomStreamStatus['end_time'];
                } else {
                    $info->end_live_time = $info->begin_live_time;
                }

                return $info->save();
            }
            return false;
        }
        return false;
    }

    /**
     * 获取列表-包含汇总信息
     *
     * @param array $with
     * @param null  $page
     *
     * @param array $condition
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @author    ensong.liu@vhall.com
     * @date      2019-03-28 21:13:27
     *
     */
    public function getListWithStat(array $condition = [], array $with = [], $page = null)
    {
        $expression = implode(',', [
            'rooms.il_id',
            'rooms.account_id',
            'rooms.subject',
            'rooms.room_id',
            '( SELECT IFNULL(SUM(duration)+0, 0) FROM ' . RecordModel::getInstance()
                ->getTable() . ' WHERE il_id = rooms.il_id) AS duration_total',
            '( SELECT count(DISTINCT watch_account_id) FROM ' . RoomAttendsModel::getInstance()
                ->getTable() . ' WHERE il_id = rooms.il_id AND ' . RoomAttendsModel::getInstance()->getTable() . '.type=1) AS uv_total',
            '( SELECT count(*) FROM ' . RoomAttendsModel::getInstance()
                ->getTable() . ' WHERE il_id = rooms.il_id AND ' . RoomAttendsModel::getInstance()->getTable() . '.type=1) AS pv_total',
        ]);

        return $this->buildCondition($this->newQuery(), $condition)
            ->selectRaw($expression)
            ->with($with)
            ->orderBy('rooms.il_id', 'desc')
            ->paginate($this->getPerPage(), ['*'], 'page', $page);
    }

    /**
     * 获取直播中的活动频道信息
     *
     * @return array
     */
    public function getAllOnlineChannelInfos(): array
    {
        $model = $this->where('status', RoomConstant::STATUS_START)
            ->select(['channel_id', 'status', 'il_id', 'room_id', 'account_id'])
            ->get();

        return $model ? $model->toArray() : [];
    }

    /**
     * 获取数量
     *
     * @param array $ilids
     * @param array $condition
     *
     * @return int
     * @author  xiangliang.liu
     * @date    2021/6/30
     */
    public function getIlIdsCount(array $ilids, array $condition = [])
    {
        $count = $this->whereIn('il_id', $ilids)
            ->where($condition)
            ->count();

        return $count;
    }

}
