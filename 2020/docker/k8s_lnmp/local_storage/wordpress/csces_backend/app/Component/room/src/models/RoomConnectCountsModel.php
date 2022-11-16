<?php
/**
 * Created by PhpStorm.
 * User: liuxiangliang
 * Date: 2020/10/9
 * Time: 15:28
 */

namespace App\Component\room\src\models;

use Illuminate\Support\Facades\DB;
use vhallComponent\decouple\models\WebBaseModel;

class RoomConnectCountsModel extends WebBaseModel
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $table      = 'room_connect_counts';

    /**
     * @param $data
     * @return array|bool
     */
    public function createData($data)
    {
        $this->count = $data['count'];
        $this->il_id = $data['il_id'];
        $this->channel = $data['channel'];
        $this->account_id = $data['account_id'];
        $datetime = date('Y-m-d H:i:s');
        $this->updated_at = $datetime;
        $this->created_at = $datetime;
        $this->create_time = date('Y-m-d H:i');
        if (!$this->save()) {
            return false;
        }

        return $this->toArray();
    }

    /**
     * @param $ids
     * @param $data
     * @return bool|int
     */
    public function updateData($ids, $data)
    {
        $update = [];
        $update['count'] = $data['count'];
        $res = $this->whereIn('id', $ids)->update($update);

        if ($res === false) {
            return false;
        }
        return $res;
    }

    /**
     * @param int $account_id
     * @param int $il_id
     * @param string $begin_time
     * @param string $end_time
     * @param string $format
     * @param int $cache_time
     * @return array
     */
    public function getCountListByCreatedTime($account_id = 0, $il_id = 0, $begin_time = '', $end_time = '', $format = '%Y-%m-%d %H:%i', $cache_time = 300)
    {
        $filed = implode(',', [
            'DATE_FORMAT(create_time,"' . $format . '") AS created_time',
            'MAX(count) AS watch_count'
        ]);
        $group = DB::raw('DATE_FORMAT(create_time,"' . $format . '")');
        $query = self::query()->selectRaw($filed);
        if ($account_id) {
            $query->where('account_id', intval($account_id));
        }
        if ($il_id) {
            is_array($il_id) ? $query->whereIn('il_id', $il_id) : $query->where('il_id', $il_id);
        }
        if ($begin_time && $end_time) {
            $query->whereBetween('create_time', [$begin_time, $end_time . '  23:59:59']);
        }
        $collection = $query->groupBy($group)->get();
        $data = $collection ? $collection->toArray() : [];

        return $data;
    }

    /**
     *
     * @param array $condition
     * @return int
     */
    public function getUvMax(array $condition = [])
    {
        $filed = implode(',', [
            'MAX(count) AS watch_count',
            'create_time'
        ]);

        $query = self::query()->selectRaw($filed);
        if ($condition['account_id']) {
            $query->where('account_id', intval($condition['account_id']));
        }
        if ($condition['il_id']) {
            is_array($condition['il_id']) ? $query->whereIn('il_id', $condition['il_id']) : $query->where('il_id', $condition['il_id']);
        }
        if ($condition['begin_time'] && $condition['end_time']) {
            $query->whereBetween('create_time', [$condition['begin_time'], $condition['end_time'] . '  23:59:59']);
        }

        $collection = $query->get();
        $data = $collection ? $collection->toArray() : [];
        if ($data) {
            return $data[0]['watch_count'] ?? 0;
        }
        return 0;
    }
}
