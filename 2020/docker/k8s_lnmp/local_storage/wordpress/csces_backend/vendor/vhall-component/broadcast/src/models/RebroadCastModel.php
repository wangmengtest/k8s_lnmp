<?php

namespace vhallComponent\broadcast\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * RebroadcastModel
 *
 * @property int $id
 * @property string  $room_id        房间id
 * @property string  $source_room_id 被转播房间id
 * @property bool $status         当前状态1转播中0结束
 * @property string  $start_time     开始时间
 * @property string  $end_time       结束时间
 * @property string  $created_at
 * @property string  $updated_at
 * @property string  $deleted_at
 *
 * @uses     yangjin
 * @date     2020-07-15
 * @author   jin.yang@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class RebroadCastModel extends WebBaseModel
{
    protected $table      = 'rebroadcast';

    protected $attributes = [
        'room_id'        => '',
        'source_room_id' => '',
        'status'         => '0',
        'start_time'     => '1970-01-01 00:00:00',
        'end_time'       => '1970-01-01 00:00:00',
        'created_at'     => '1970-01-01 00:00:00',
        'updated_at'     => '1970-01-01 00:00:00',

    ];

    /**
     * @param     $roomId
     * @param     $sourceId
     * @param int $status
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|RebroadCastModel|null
     * @author  jin.yang@vhall.com
     * @date    2020-10-30
     */
    public function setRebroadcast($roomId, $sourceId, $status = 0)
    {
        $now    = date('Y-m-d H:i:s');
        $stream = $this->where(['room_id' => $roomId, 'status' => 1])->first();
        if ($stream) {
            $stream->update(['status' => 0, 'end_time' => $now]);
        }
        if ($status) {
            $createArr = [
                'room_id'        => $roomId,
                'source_room_id' => $sourceId,
                'status'         => 1,
                'start_time'     => $now,
            ];
            $stream    = $this->create($createArr);
        }

        return $stream;
    }

    /**
     * 获取房间转播源
     *
     * @param $roomId
     *
     * @return mixed|string
     */
    public function getStartRebroadcastByRoomId($roomId)
    {
        $attributes = $this->getCache('StartInfoByRoomId', $roomId, function () use ($roomId) {
            $model = $this->where(['room_id' => $roomId, 'status' => 1])->first();

            return $model ? $model->getAttributes() : null;
        });

        return empty($attributes) ? null : $this->newInstance($attributes, true)->syncOriginal();
    }
}
