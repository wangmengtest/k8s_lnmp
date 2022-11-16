<?php

namespace App\Component\room\src\models;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Component\room\src\constants\RoomAttendsConstant;
use vhallComponent\account\models\AccountsModel;
use vhallComponent\decouple\models\WebBaseModel;

/**
 * RoomAttendsModel
 *
 * @property int    $id
 * @property int    $il_id
 * @property int    $account_id   用户id
 * @property int    $watch_account_id
 * @property string $start_time
 * @property string $end_time
 * @property int    $duration     观看时长,单位秒
 * @property string $terminal     终端
 * @property string $browser      浏览器
 * @property string $province     地域
 * @property bool   $type         数据来源 1-直播房间数据 2-互动房间数据
 * @property string $created_time 统计时间
 * @property string $updated_at
 * @property string $created_at   创建时间
 * @property string $deleted_at
 *
 * @uses     yangjin
 * @date     2020-07-15
 * @author   jin.yang@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class RoomAttendsModel extends WebBaseModel
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $table = 'room_attends';

    const TYPE_LIVE = 1;    //数据来源 直播数据

    const TYPE_INAV = 2;    //数据来源 互动数据

    /**
     * 模型关联-用户表
     *
     * @return BelongsTo
     * @author ensong.liu@vhall.com
     * @date   2019-02-14 10:39:24
     */
    public function account()
    {
        return $this->belongsTo(AccountsModel::class, 'account_id', 'account_id');
    }

    /**
     * 模型关联-用户表(观众)
     *
     * @return BelongsTo
     * @author ensong.liu@vhall.com
     * @date
     */
    public function watchAccount()
    {
        return $this->belongsTo(AccountsModel::class, 'watch_account_id', 'account_id');
    }

    /**
     * 模型关联-房间表
     *
     * @return BelongsTo
     * @author ensong.liu@vhall.com
     * @date   2019-02-14 10:41:10
     */
    public function rooms()
    {
        return $this->belongsTo(RoomsModel::class, 'il_id', 'il_id');
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
        //房主ID
        $model->when($condition['master_account_id'] ?? '', function ($query) use ($condition) {
            $query->where('account_id', '=', $condition['master_account_id']);
        });
        //时间范围-开始
        $model->when($condition['begin_time'] ?? '', function ($query) use ($condition) {
            $query->where('start_time', '>=', $condition['begin_time']);
        });
        //时间范围-结束
        $model->when($condition['end_time'] ?? '', function ($query) use (&$condition) {
            $query->where('start_time', '<=', date('Y-m-d 23:59:59', strtotime($condition['end_time'])));
            unset($condition['end_time']);
        });

        //watch_account_id_not过滤观看人id
        $model->when($condition['watch_account_id_notin'] ?? '', function ($query) use ($condition) {
            $query->whereNotIn('watch_account_id', $condition['watch_account_id_notin']);
        });

        $model = parent::buildCondition($model, $condition);

        return $model;
    }

    /**
     * 获取累计观众人数
     *
     * @param array $condition
     *
     * @return int
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 16:24:54
     *
     */
    public static function getUvCount(array $condition = []): int
    {
        return (int)(new self())->buildCondition(
            self::query(),
            $condition
        )->distinct()->count('watch_account_id');
    }

    /**
     * 累计观看次数
     *
     * @param array $condition
     *
     * @return int
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 15:38:52
     *
     */
    public static function getPvCount(array $condition = []): int
    {
        return (new self())->getCount($condition);
    }

    /**
     * 直播时长
     * @auther yaming.feng@vhall.com
     * @date 2021/3/10
     *
     * @param array $condition
     *
     * @return int
     */
    public static function getLiveDuration(array $condition = []): int
    {
        return (int)(new self())->buildCondition(self::query(), $condition)
            ->when($condition['account_id'] ?? 0, function ($query) use ($condition) {
                $query->where('watch_account_id', $condition['account_id']);
            })
            ->sum('duration');
    }

    /**
     * 累计观看次数列表
     *
     * @param array $condition
     *
     * @return LengthAwarePaginator|null
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 15:38:52
     *
     */
    public function getList(array $condition = [], array $with = [], $page = null, $columns = ['*'])
    {
        return $this->buildCondition($this->newQuery(), $condition)
            ->with($with)
            ->orderBy($this->getTable() . '.start_time')
            ->paginate($this->getPerPage(), $columns, 'page', $page);
    }

    public function createLiveAttends($ilId, $accountId, $data, $type)
    {
        $datetime               = date('Y-m-d H:i:s');
        $this->il_id            = $ilId;
        $this->account_id       = $accountId;
        $this->watch_account_id = $data['uid'];
        $this->start_time       = $data['start_time'];
        $this->end_time         = $data['end_time'];
        $this->duration         = $data['tt'];
        $this->terminal         = $data['pf'];
        $this->browser          = $data['browser'];
        if (!empty($data['viewer_country'])) {
            $this->country = $data['viewer_country'];
        }
        $this->province     = $data['viewer_province'];
        $this->type         = $type;
        $this->created_time = $data['created_time'];
        $this->updated_at   = $datetime;
        $this->created_at   = $datetime;
        $insert             = $this->save();
        if (!$insert) {
            return false;
        }

        return $this->toArray();
    }

    public function updateLiveAttends($ids, $data, $type)
    {
        $update                     = [];
        $update['watch_account_id'] = $data['uid'];
        $update['start_time']       = $data['start_time'];
        $update['end_time']         = $data['end_time'];
        $update['duration']         = $data['tt'];
        $update['terminal']         = $data['pf'];
        $update['browser']          = $data['browser'];
        if (!empty($data['viewer_country'])) {
            $update['country'] = $data['viewer_country'];
        }
        $update['province'] = $data['viewer_province'];
        $update['type']     = $type;

        $res = $this->whereIn('id', $ids)->update($update);

        if ($res === false) {
            return false;
        }

        return $res;
    }

    /**
     * 获取直播观看总时长
     *
     * @param array $condition
     *
     * @return int
     */
    public static function getTotalTime(array $condition = []): int
    {
        return (int)(new self())->buildCondition(self::query(), $condition)->sum('duration');
    }

    /**
     * 时间段内数量统计，id_count|duration_count|terminal_count|browser_count|province_count
     *
     * @param int    $account_id
     * @param int    $il_id
     * @param string $begin_time
     * @param string $end_time
     * @param string $format
     *
     * @return array
     * @author ensong.liu@vhall.com
     * @date   2018-11-27 11:04:08
     */
    public static function getCountListByCreatedTime(
        $account_id = 0,
        $il_id = 0,
        $begin_time = '',
        $end_time = '',
        $format = '%Y-%m-%d %H:%i'
    ) {
        $filed = implode(',', [
            'DATE_FORMAT(created_time,"' . $format . '") AS created_time',
            'COUNT(id) AS watch_count',
            'COUNT(DISTINCT watch_account_id) AS watch_account_count',
            'SUM(duration)/60 AS duration_count',
            'COUNT(terminal) AS terminal_count',
            'COUNT(browser) AS browser_count',
            'COUNT(province) AS province_count',
        ]);
        $group = DB::raw('DATE_FORMAT(created_time,"' . $format . '")');
        $query = self::query()->selectRaw($filed);
        if ($account_id) {
            $query->where('account_id', intval($account_id));
        }
        if ($il_id) {
            is_array($il_id) ? $query->whereIn('il_id', $il_id) : $query->where('il_id', $il_id);
        }
        if ($begin_time && $end_time) {
            $query->whereBetween('created_time', [$begin_time, $end_time . '  23:59:59']);
        }
        $collection = $query->groupBy($group)->get();
        $data       = $collection ? $collection->toArray() : [];

        return $data;
    }

    /**
     * 查询设备终端数据
     *
     * @param int    $accountId
     * @param int    $ilId
     * @param string $begintime
     * @param string $endtime
     *
     * @return array[]
     */
    public static function getTerminal($accountId = 0, $ilId = 0, $begintime = '', $endtime = '', $type = 0)
    {
        $model = self::query();

        if ($accountId) {
            $model->where('account_id', $accountId);
            $model->whereNotIn('watch_account_id', [$accountId]);
        }
        if ($ilId) {
            if (is_array($ilId)) {
                $model->whereIn('il_id', $ilId);
            } else {
                $model->where('il_id', $ilId);
            }
        }
        if ($type) {
            $model->where('type', $type);
        }

        if ($begintime && $endtime) {
            $model->whereBetween('start_time', [$begintime, $endtime . ' 23:59:59']);
        }

        $result = $model->selectRaw('count(*) c, terminal')
            ->groupBy(['terminal'])
            ->get()
            ->toArray();

        $map = ['pc' => 0, 'mobile' => 0];
        foreach ($result as $item) {
            if ($item['terminal'] == RoomAttendsConstant::TERMINAL_H5PC) {
                $map['pc'] = $item['c'];
            } else {
                $map['mobile'] += $item['c'];
            }
        }

        $data = [
            ['name' => 'PC端', 'value' => $map['pc']],
            ['name' => '移动端', 'value' => $map['mobile']]
        ];

        return $data;
    }

    /**
     * 查询地域分布
     *
     * @param int    $accountId
     * @param int    $ilId
     * @param string $begintime
     * @param string $endtime
     *
     * @return array
     */
    public function getProvince($accountId = 0, $ilId = 0, $begintime = '', $endtime = ''): array
    {
        $model = self::query();

        if ($accountId) {
            $model->where('account_id', $accountId);
        }
        if ($ilId) {
            if (is_array($ilId)) {
                $model->whereIn('il_id', $ilId);
            } else {
                $model->where('il_id', $ilId);
            }
        }
        if ($begintime && $endtime) {
            $model->whereBetween('created_time', [$begintime, $endtime . ' 23:59:59']);
        }

        $model->select(DB::raw('province as name, count(id) as value'))->groupBy('name');

        return $model->get()->toArray();
    }

    /**
     * 查询地域分布
     *
     * @param int    $accountId
     * @param int    $ilId
     * @param string $begintime
     * @param string $endtime
     *
     * @return array
     */
    public function getCountry($accountId = 0, $ilId = 0, $begintime = '', $endtime = '', $type = 0): array
    {
        $model = self::query();

        if ($accountId) {
            $model->where('account_id', $accountId);
            $model->whereNotIn('watch_account_id', [$accountId]);
        }
        if ($ilId) {
            if (is_array($ilId)) {
                $model->whereIn('il_id', $ilId);
            } else {
                $model->where('il_id', $ilId);
            }
        }
        if ($type) {
            $model->where('type', $type);
        }
        if ($begintime && $endtime) {
            $model->whereBetween('created_time', [$begintime, $endtime . ' 23:59:59']);
        }
        $model->select(DB::raw('country as name, count(id) as value'))->groupBy('name');
        $data = $model->get()->toArray();
        return $data;
    }

    /**
     * 通过国家获取省份
     *
     * @param        $country
     * @param int    $accountId
     * @param int    $ilId
     * @param string $begintime
     * @param string $endtime
     *
     * @return array
     */
    public function getProvinceByCountry(
        $country,
        $accountId = 0,
        $ilId = 0,
        $begintime = '',
        $endtime = '',
        $type = 0
    ): array {
        $model = self::query();

        if ($accountId) {
            $model->where('account_id', $accountId);
            $model->whereNotIn('watch_account_id', [$accountId]);
        }
        if ($ilId) {
            if (is_array($ilId)) {
                $model->whereIn('il_id', $ilId);
            } else {
                $model->where('il_id', $ilId);
            }
        }

        if ($country) {
            $model->where('country', $country);
        }

        if ($type) {
            $model->where('type', $type);
        }
        if ($begintime && $endtime) {
            $model->whereBetween('created_time', [$begintime, $endtime . ' 23:59:59']);
        }
        $model->select(DB::raw('province as name, count(id) as value'))->groupBy('name');
        $data = $model->get()->toArray();
        return $data;
    }

    /**
     * 数据同步
     *
     * @param $roomInfo
     * @param $item
     * @param $type
     *
     * @return array
     * @author fym
     * @since  2021/6/16
     */
    public function syncData($roomInfo, $item, $type)
    {
        $createCount = 0;
        $updateCount = 0;

        // 3. 检查数据是否存在 room_attends 表
        $condition    = [
            'il_id'            => $roomInfo['il_id'],
            'type'             => $type,
            'start_time'       => $item['start_time'],
            'watch_account_id' => $item['uid'],
            'terminal'         => $item['pf'],
            'browser'          => $item['browser'],
            'country'          => $item['viewer_country'],
            'province'         => $item['viewer_province'],
        ];
        $roomAttendId = $this->where($condition)->value('id');

        // 4. 检查记录是否存在，存在在更新，不存在则新增
        if ($roomAttendId) {
            $this->where('id', $roomAttendId)
                ->update([
                    'end_time'     => $item['end_time'],
                    'duration'     => $item['tt'],
                    'created_time' => $item['created_time'],
                    'updated_at'   => date('Y-m-d H:i:s')
                ]);

            $updateCount++;
        } else {
            $this->createLiveAttends(
                $roomInfo['il_id'], $roomInfo['account_id'], $item, $type
            );

            $createCount++;
        }

        return [
            'create_count' => $createCount,
            'update_count' => $updateCount
        ];
    }
}
