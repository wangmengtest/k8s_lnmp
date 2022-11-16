<?php
/**
 *+----------------------------------------------------------------------
 * @file RecordAttends.php
 * @date 2019/2/18 11:32
 *+----------------------------------------------------------------------
 */

namespace vhallComponent\record\models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use vhallComponent\account\models\AccountsModel;
use vhallComponent\record\constants\RecordConstant;
use vhallComponent\room\models\RoomsModel;
use vhallComponent\decouple\models\WebBaseModel;
use Vss\Exceptions\ValidationException;

/**
 *+----------------------------------------------------------------------
 * Class RecordAttends
 *+----------------------------------------------------------------------
 *
 * @package App\Models
 * @property int    $id
 * @property int    $il_id            互动直播id
 * @property int    $account_id       用户id
 * @property string $record_id        回放id
 * @property int    $watch_account_id 观众用户id
 * @property string $start_time       进入时间
 * @property string $end_time         离开时间
 * @property int    $duration         观看时长/秒
 * @property string $terminal         终端
 * @property string $browser          浏览器
 * @property string $country          国家
 * @property string $province         地域
 * @property string $created_time     统计时间
 * @property string $updated_at
 * @property string $created_at       创建时间
 * @property string $deleted_at
 *
 * @author  ensong.liu@vhall.com
 * @date
 * @see
 * @link
 * @version
 *+----------------------------------------------------------------------
 */
class RecordAttendsModel extends WebBaseModel
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $table = 'record_attends';

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
     * 模型关联-房间
     *
     * @return BelongsTo
     * @author ensong.liu@vhall.com
     * @date
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
            $query->where('created_time', '>=', $condition['begin_time']);
        });
        //时间范围-结束
        $model->when($condition['end_time'] ?? '', function ($query) use (&$condition) {
            $query->where('created_time', '<=', date('Y-m-d 23:59:59', strtotime($condition['end_time'])));
            unset($condition['end_time']);
        });

        $model = parent::buildCondition($model, $condition);

        return $model;
    }

    /**
     * @param int    $ilId
     * @param int    $accountId
     * @param string $recordId
     * @param array  $data
     *
     * @return array|bool
     * @author ensong.liu@vhall.com
     * @date
     *
     */
    public function createRecordAttends(int $ilId, int $accountId, string $recordId, array $data)
    {
        $datetime               = date('Y-m-d H:i:s');
        $this->il_id            = $ilId;
        $this->account_id       = $accountId;
        $this->record_id        = $recordId;
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
        $this->created_time = $data['created_time'];
        $this->updated_at   = $datetime;
        $this->created_at   = $datetime;
        if (!$this->save()) {
            return false;
        }

        return $this->toArray();
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
        return (int)(new self())->buildCondition(self::query(), $condition)
            ->distInct('watch_account_id')
            ->count('watch_account_id');
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
     * 获取回放总时长
     *
     * @param array $condition
     *
     * @return int
     */
    public function getTotalTime(array $condition = []): int
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
     *
     */
    public static function getCountListByCreatedTime(
        $account_id = 0,
        $il_id = 0,
        $begin_time = '',
        $end_time = '',
        $format = '%Y-%m-%d %H:%i',
        $cache_time = 300
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
    public static function getTerminal($accountId = 0, $ilId = 0, $begintime = '', $endtime = '')
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
            $model->whereBetween('start_time', [$begintime, $endtime . ' 23:59:59']);
        }

        $result = $model = $model->selectRaw('count(*) c, terminal')
            ->groupBy(['terminal'])
            ->get()
            ->toArray();

        $map = ['pc' => 0, 'mobile' => 0];
        foreach ($result as $item) {
            if ($item['terminal'] == RecordConstant::TERMINAL_H5PC) {
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
    public static function getProvince($accountId = 0, $ilId = 0, $begintime = '', $endtime = ''): array
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
            $model->whereBetween('created_time', [$begintime, $endtime]);
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
    public static function getCountry($accountId = 0, $ilId = 0, $begintime = '', $endtime = ''): array
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

        $model->select(DB::raw('country as name, count(id) as value'))->groupBy('name');

        return $model->get()->toArray();
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
    public static function getProvinceByCountry(
        $country,
        $accountId = 0,
        $ilId = 0,
        $begintime = '',
        $endtime = ''
    ): array {
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

        if ($country) {
            $model->where('country', $country);
        }

        if ($begintime && $endtime) {
            $model->whereBetween('created_time', [$begintime, $endtime . ' 23:59:59']);
        }

        $model->select(DB::raw('province as name, count(id) as value'))->groupBy('name');

        return $model->get()->toArray();
    }

    /**
     * 批量修改
     *
     * @param $ids
     * @param $data
     *
     * @return bool
     */
    public function updateRecordAttends($ids, $data)
    {
        $update               = [];
        $update['start_time'] = $data['start_time'];
        $update['end_time']   = $data['end_time'];
        $update['duration']   = $data['tt'];
        $update['updated_at'] = date('Y-m-d H:i:s');
        $res                  = $this->whereIn('id', $ids)->update($update);

        if ($res === false) {
            return false;
        }
        return $res;
    }

    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/3/11
     *
     * @param array          $condition
     * @param array          $with
     * @param null           $page
     * @param array|string[] $columns
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getList(array $condition = [], array $with = [], $page = null, $columns = ['*'])
    {
        return $this->buildCondition($this->newQuery(), $condition)
            ->with($with)
            ->orderBy($this->getTable() . '.start_time')
            ->paginate($this->getPerPage(), $columns, 'page', $page);
    }

    /**
     * 数据同步
     *
     * @param int   $ilId      房间 Id
     * @param int   $accountId 用户 ID
     * @param array $item      要同步的数据
     *
     * @return array|bool|int
     * @since  2021/6/17
     * @author fym
     */
    public function syncData(array $item, int $ilId, int $accountId)
    {
        $condition = [
            'start_time'       => $item['start_time'],
            'watch_account_id' => $item['uid'],
            'terminal'         => $item['pf'],
            'browser'          => $item['browser'],
            'country'          => $item['viewer_country'],
            'province'         => $item['viewer_province'],
            'record_id'        => $item['record_id'],
        ];

        // 查询是否存在
        $recordAttendsId = vss_model()->getRecordAttendsModel()->where($condition)->value('id');

        // 存在则修改，不存在则新增
        if ($recordAttendsId) {
            $datetime = date('Y-m-d H:i:s');
            return vss_model()->getRecordAttendsModel()->where('id', $recordAttendsId)
                ->update([
                    'end_time'   => $item['end_time'],
                    'duration'   => $item['tt'],
                    'created_at' => $datetime,
                    'updated_at' => $datetime
                ]);
        }

        return vss_model()->getRecordAttendsModel()->createRecordAttends(
            $ilId,
            $accountId,
            $item['record_id'],
            $item
        );
    }
}
