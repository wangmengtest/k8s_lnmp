<?php

namespace vhallComponent\room\services;

use App\Constants\ResponseCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use vhallComponent\room\constants\RoomConstant;
use Vss\Common\Services\WebBaseService;

/**
 * StatService
 *
 * @author   jin.yangjin@vhall.com
 * @uses     yangjin
 * @date     2020-08-18
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class StatService extends WebBaseService
{
    /**
     * 直播间数据统计
     *
     * @param $accountId
     * @param $ilId
     * @param $beginTime
     * @param $endTime
     * @param $status
     *
     * @return array|array[]
     *
     */
    public function live($accountId, $ilId, $beginTime, $endTime, $status)
    {
        $result = [];

        if ($status == RoomConstant::LIVE_STATUS_START) {  // 直播中数据统计
            $result = $this->liveData($accountId, $ilId, $beginTime, $endTime, 'admin');
        }

        # vhallEOF-record-statService-live-1-start
        
        if ($status == RoomConstant::LIVE_PLAY_BACK) { // 回访数据统计
            $result = vss_service()->getRecordService()->recordData($accountId, $ilId, $beginTime, $endTime, "admin");
        }

        # vhallEOF-record-statService-live-1-end

        if ($status == RoomConstant::LIVE_PLAY_ALL) { // 全部
            $result = $this->allData($accountId, $ilId, $beginTime, $endTime, 'admin');
        }

        return $result;
    }

    /**
     * @param $ilId
     * @param $beginTime
     * @param $endTime
     * @param $accountId
     *
     * @return array[]
     *
     * @author  jin.yang@vhall.com
     * @date    2020-08-18
     */
    public function allData($accountId, $ilId, $beginTime, $endTime, $source)
    {

        //1、筛选条件
        $condition = [
            'il_id'      => $ilId,
            'begin_time' => $beginTime,
            'end_time'   => $endTime,
        ];
        if ($accountId) {
            $condition['account_id'] = $accountId;
        }

        $data = [];

        //3、直播数据
        $modelRoomAttends      = vss_model()->getRoomAttendsModel();
        $liveCondition         = $condition;
        $liveCondition['type'] = $modelRoomAttends::TYPE_LIVE;
        //3.1、直播累计观众人数
        $uvStat          = vss_model()->getRoomAttendsModel()->getUvCount($condition);
        $data['live_uv'] = [
            'stat'   => $uvStat,
            'unit'   => '人',
            'export' => $ilId ? 'uv' : '', // 房间下可以导出
        ];

        //3.2、直播累计观看次数
        $pvStat          = vss_model()->getRoomAttendsModel()->getPvCount($condition);
        $data['live_pv'] = [
            'stat'   => $pvStat,
            'unit'   => '次',
            'export' => 'pv',
        ];

        //3.3、互动房间最高并发数
        $maxUvStat              = vss_model()->getRoomConnectCountsModel()->getUvMax($condition);
        $data['max_concurrent'] = [
            'stat'   => $maxUvStat,
            'unit'   => '人',
            'export' => '',
        ];

        // 3.4 直播观看时长
        $watchTime          = vss_model()->getRoomAttendsModel()->getTotalTime($condition);
        $data['watch_time'] = [
            'stat'   => ceil($watchTime / 60),
            'unit'   => '分钟',
            'export' => '',
        ];

        if (!$ilId) {

            //3.5、直播消耗流量
            $flowStat          = vss_model()->getRoomStatsModel()->getFolwSum($condition);
            $data['live_flow'] = [
                'stat'   => preg_replace('/[a-zA-Z]+/', '', \Helper::calc($flowStat)),
                'unit'   => preg_replace('/[0-9.]+/', '', \Helper::calc($flowStat)),
                'export' => '',
            ];
        }

        # vhallEOF-record-StatService-allData-1-start
        
        //2、点播数据
        //2.1、点播累计观众人数
        $RuvStat                 = vss_model()->getRecordAttendsModel()->getUvCount($condition);
        $data["live_uv"]["stat"] += $RuvStat;
        //2.2、点播累计观看次数
        $RpvStat                 = vss_model()->getRecordAttendsModel()->getPvCount($condition);
        $data["live_pv"]["stat"] += $RpvStat;

        if (!$ilId) {

            //2.5、点播消耗流量
            $recordFlowStat            = vss_model()->getRecordStatsModel()->getFolwSum($condition);
            $data["live_flow"]["stat"] = preg_replace("/[a-zA-Z]+/", "", \Helper::calc($flowStat + $recordFlowStat));
            $data["live_flow"]["unit"] = preg_replace("/[0-9.]+/", "", \Helper::calc($flowStat + $recordFlowStat));

            //2.6、回放存储空间
            $storageStat            = vss_model()->getRecordModel()->getStorageSum($condition);
            $data["record_storage"] = [
                "stat"   => preg_replace("/[a-zA-Z]+/", "", \Helper::calc($storageStat)),
                "unit"   => preg_replace("/[0-9.]+/", "", \Helper::calc($storageStat)),
                "export" => "",
            ];
        }

        // 2.7 观看时长 +=  点播观看时长
        $recordWatchTime            = vss_model()->getRecordAttendsModel()->getTotalTime($condition);
        $watchTime                  += $recordWatchTime;
        $data["watch_time"]["stat"] = ceil($watchTime / 60);

        # vhallEOF-record-StatService-allData-1-end

        // 3.6 人均观看时长
        $liveUv                 = $data['live_uv']['stat'];
        $data['avg_watch_time'] = [
            'stat'   => $liveUv > 0 ? ceil($watchTime / $data['live_uv']['stat'] / 60) : 0,
            'unit'   => '分钟',
            'export' => '',
        ];

        if (!$ilId) {

            // 3.7 人均观看次数
            $data['avg_live_pv'] = [
                'stat'   => $liveUv > 0 ? ceil($data['live_pv']['stat'] / $liveUv) : 0,
                'unit'   => '次',
                'export' => '',
            ];
        }

        return $data;
    }

    /**
     * 获取点播统计信息
     *
     * @param         $accountId
     * @param int     $ilId      互动房间ID值
     * @param string  $beginTime 开始时间
     * @param string  $endTime   截止时间
     *
     * @return array
     */

    /**
     * 直播汇总数据
     *
     * @param $accountId
     * @param $ilId
     * @param $beginTime
     * @param $endTime
     * @param $source /console /admin
     *
     * @return array
     */
    public function liveData($accountId, $ilId, $beginTime, $endTime, $source)
    {
        //筛选条件
        $condition = [
            'il_id'      => $ilId,
            'begin_time' => $beginTime,
            'end_time'   => $endTime,
        ];
        if ($accountId) {
            $condition['account_id'] = $accountId;
        }

        //累计观众人数
        $uvStat = vss_model()->getRoomAttendsModel()->getUvCount($condition);
        //累计观看次数
        $pvStat = vss_model()->getRoomAttendsModel()->getPvCount($condition);

        //最高并发数
        $maxUvStat = vss_model()->getRoomConnectCountsModel()->getUvMax($condition);

        // 直播时长
        $liveDuration = vss_model()->getRoomAttendsModel()->getLiveDuration($condition);

        //直播观看总时长
        $totalTime = vss_model()->getRoomAttendsModel()->getTotalTime($condition);

        //平均观看时长
        $avgWatchTime = $uvStat > 0 ? $totalTime / $uvStat : 0;

        //返回数据
        return [
            // 观看人数
            'live_uv'         => [
                'stat'   => $uvStat,
                'unit'   => '人',
                'export' => 'uv',
            ],
            // 观看次数
            'live_pv'         => [
                'stat'   => $pvStat,
                'unit'   => '次',
                'export' => 'pv',
            ],
            // 直播总时长
            'living_duration' => [
                'stat'   => ceil($liveDuration / 60),
                'unit'   => '分钟',
                'export' => '',
            ],
            // 人均观看时长
            'avg_watch_time'  => [
                'stat'   => ceil($avgWatchTime / 60),
                'unit'   => '分钟',
                'export' => '',
            ],
            // 观看时长
            'watch_time'      => [
                'stat'   => ceil($totalTime / 60), // 结果向上取整
                'unit'   => '分钟',
                'export' => '',
            ],
            // 最高并发
            'max_concurrent'  => [
                'stat'   => $maxUvStat,
                'unit'   => '人',
                'export' => '',
            ],
            // 人均观看次数
            'avg_live_pv'     => [
                'stat'   => $uvStat > 0 ? ceil($pvStat / $uvStat) : 0,
                'unit'   => '次',
                'export' => '',
            ],
        ];
    }

    /**
     * 统计-并发趋势列表
     *
     * @param $ilId
     * @param $beginTime
     * @param $endTime
     *
     * @param $accountId
     *
     * @return array
     * @author ensong.liu@vhall.com
     * @date   2019-05-22 19:53:34
     */
    public function liveConcurrent($accountId, $ilId, $beginTime, $endTime)
    {
        //并发趋势图
        $data           = [];
        $liveAttendList = vss_model()->getRoomConnectCountsModel()->getCountListByCreatedTime($accountId, $ilId,
            $beginTime, $endTime);

        foreach ($liveAttendList ?: [] as $key => $value) {
            $data[] = [
                'datetime'            => $value['created_time'],
                'watch_count'         => $value['watch_count'],
                'watch_account_count' => $value['watch_count'],
            ];
        }

        return $data;
    }

    /**
     * 统计-观看趋势列表
     *
     * @param $ilId
     * @param $beginTime
     * @param $endTime
     *
     * @param $accountId
     *
     * @return array
     * @author ensong.liu@vhall.com
     * @date   2019-05-22 19:53:32
     */
    public function liveAttend($accountId, $ilId, $beginTime, $endTime)
    {
        $data           = [];
        $liveAttendList = vss_model()->getRoomAttendsModel()->getCountListByCreatedTime(
            $accountId,
            $ilId,
            $beginTime,
            $endTime,
            '%Y-%m-%d %H:00'
        );

        foreach ($liveAttendList ?: [] as $key => $value) {
            $data[] = [
                'datetime'            => $value['created_time'],
                'duration_count'      => $value['duration_count'],
                'watch_count'         => $value['watch_count'],
                'watch_account_count' => $value['watch_account_count'],
            ];
        }

        return $data;
    }

    /**
     * 获取直播接回放 互动统计数据
     *
     * @param $accountId
     * @param $ilId
     * @param $beginTime
     * @param $endTime
     *
     * @return array
     */
    public function allState($accountId, $ilId, $beginTime, $endTime)
    {
        $liveAttendList = $this->roomState($accountId, $ilId, $beginTime, $endTime);
        $reAttendList   = $this->recordState($accountId, $ilId, $beginTime, $endTime);
        $data           = [];
        if ($liveAttendList && $reAttendList) {
            $s    = array_merge($liveAttendList, $reAttendList);
            $res  = $this->array2_key_sum($s, 'created_time', ['pv_num', 'uv_num', 'duration'], 3);
            $data = array_values($res);
        } elseif ($liveAttendList && !$reAttendList) {
            $data = $liveAttendList;
        } elseif ($reAttendList && !$liveAttendList) {
            $data = $reAttendList;
        }

        if ($data) {
            array_multisort(array_column($data, 'created_time'), SORT_ASC, $data);
        }
        return $data;
    }

    /**
     * 获取直播互动统计数据
     *
     * @param $accountId
     * @param $ilId
     * @param $beginTime
     * @param $endTime
     *
     * @return array
     */
    public function roomState($accountId, $ilId, $beginTime, $endTime)
    {
        return vss_model()->getRoomStatsModel()->getCountListByCreatedTime($accountId, $ilId, $beginTime, $endTime);
    }

    /**
     * 获取回放互动统计数据
     *
     * @param $accountId
     * @param $ilId
     * @param $beginTime
     * @param $endTime
     *
     * @return array
     */
    public function recordState($accountId, $ilId, $beginTime, $endTime)
    {
        $data = [];
        # vhallEOF-record-statService-recordStat-1-start
        
        $data = vss_model()->getRecordStatsModel()->reCountListByCreatedTime($accountId, $ilId, $beginTime, $endTime);

        # vhallEOF-record-statService-recordStat-1-end
        return $data;
    }

    /**
     * 统计-终端使用列表
     *
     * @param $ilId
     * @param $beginTime
     * @param $endTime
     *
     * @param $accountId
     *
     * @return array
     * @author ensong.liu@vhall.com
     * @date   2019-05-22 19:56:23
     */
    public function liveTerminal($accountId, $ilId, $beginTime, $endTime)
    {
        return vss_model()->getRoomAttendsModel()->getTerminal($accountId, $ilId, $beginTime, $endTime);
    }

    /**
     * 统计-终端使用列表
     *
     * @param $ilId
     * @param $beginTime
     * @param $endTime
     *
     * @param $accountId
     *
     * @return array
     * @author ensong.liu@vhall.com
     * @date   2019-05-22 19:56:23
     */
    public function recordTerminal($accountId, $ilId, $beginTime, $endTime)
    {
        $data = [
            ['name' => 'PC端', 'value' => 0],
            ['name' => '移动端', 'value' => 0]
        ];
        # vhallEOF-record-statService-recordTerminal-1-start
        
        $data = vss_model()->getRecordAttendsModel()->getTerminal($accountId, $ilId, $beginTime, $endTime);

        # vhallEOF-record-statService-recordTerminal-1-end
        return $data;
    }

    /**
     * 统计-终端使用列表 直播及回放
     *
     * @param $accountId
     * @param $ilId
     * @param $beginTime
     * @param $endTime
     *
     * @return array
     */
    public function allTerminal($accountId, $ilId, $beginTime, $endTime)
    {
        //直播
        $liveData = $this->liveTerminal($accountId, $ilId, $beginTime, $endTime);
        //回放
        $recordData = $this->recordTerminal($accountId, $ilId, $beginTime, $endTime);

        $s   = array_merge($liveData, $recordData);
        $res = $this->array2_key_sum($s, 'name', ['value'], 1);
        return array_values($res);
    }

    /**
     * 统计-地域分布列表
     *
     * @param $ilId
     * @param $beginTime
     * @param $endTime
     *
     * @param $accountId
     *
     * @return array
     * @author ensong.liu@vhall.com
     * @date   2019-05-22 19:57:37
     */
    public function liveRegion($params)
    {
        vss_validator($params, [
            'account_id' => 'required',
            'il_id'      => '',
            'begin_time' => 'required',
            'end_time'   => 'required',
            'ctry'       => '',
            'status'     => 'required',
        ]);
        $accountId = $params['account_id'];
        $ilId      = $params['il_id'];
        $status    = $params['status'] ?? 1;
        $country   = $params['ctry'];
        $beginTime = $params['begin_time'];
        $endTime   = $params['end_time'];

        $liveData = [];
        if ($status == RoomConstant::LIVE_PLAY_ALL || $status == RoomConstant::LIVE_STATUS_START) {
            $modelRoomAttends = vss_model()->getRoomAttendsModel();
            //地域分布图
            if ($country) {
                $liveData = $modelRoomAttends->getProvinceByCountry($country, $accountId, $ilId, $beginTime, $endTime);
            } else {
                $liveData = $modelRoomAttends->getCountry($accountId, $ilId, $beginTime, $endTime);
            }
        }

        $recordData = [];
        //回放数据
        # vhallEOF-record-statService-liveRegion-1-start
        
        if ($status == RoomConstant::LIVE_PLAY_ALL || $status == RoomConstant::LIVE_PLAY_BACK) {
            $modelRecordAttends = vss_model()->getRecordAttendsModel();
            //地域分布图
            if ($country) {
                $recordData = $modelRecordAttends->getProvinceByCountry($country, $accountId, $ilId, $beginTime, $endTime);
            } else {
                $recordData = $modelRecordAttends->getCountry($accountId, $ilId, $beginTime, $endTime);
            }
        }

        # vhallEOF-record-statService-liveRegion-1-end

        //数据合并
        $all  = array_merge($liveData, $recordData);
        $res  = $this->array2_key_sum($all, 'name', ['value'], 1);
        $data = array_values($res);

        return $data;
    }

    public function array2_key_sum($array, $mainKey, $otherKey, $num)
    {
        $item = [];
        foreach ($array as $k => $v) {
            if (!isset($item[$v[$mainKey]])) {
                $item[$v[$mainKey]] = $v;
            } else {
                for ($i = 0; $i < $num; $i++) {
                    $item[$v[$mainKey]][$otherKey[$i]] += $v[$otherKey[$i]];
                }
            }
        }
        return $item;
    }

    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/2/26
     *
     * @param $ilId
     * @param $accountId
     * @param $beginTime
     * @param $endTime
     * @param $type
     */
    public function createExportUv($ilId, $accountId, $beginTime, $endTime, $type)
    {
        if (empty($accountId)) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }

        $params = [
            'il_id'      => $ilId,
            'account_id' => $accountId,
            'start_time' => $beginTime,
            'end_time'   => $endTime,
            'type'       => $type,
        ];

        $typeMap  = [
            1 => 'All',
            2 => 'Live',
            3 => 'Record',
        ];
        $typeName = $typeMap[$type] ?? 'all';

        $insert = [
            'export'     => 'uv',
            'il_id'      => $ilId,
            'account_id' => $accountId,
            'file_name'  => $typeName . 'StatRoomsUvList' . date('YmdHis') . $accountId,
            'title'      => ['房间ID', '房间名称', '昵称', '账号', '类型'],
            'params'     => json_encode($params),
            'callback'   => 'stat:exportUvData',
        ];

        return vss_model()->getExportModel()->create($insert);
    }

    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/2/26
     */
    public function exportUvData($export, $filePath)
    {
        $params = json_decode($export['params'], true);
        $header = json_decode($export['title'], true);
        $file   = $filePath . $export['file_name'];

        // type 1 全部， 2 直播 3 回放
        $type      = $params['type'];
        $startDate = $params['start_time'];
        $endDate   = $params['end_time'] . ' 23:59:59';
        $ilId      = $params['il_id'] ?? 0;
        $accountId = $params['account_id'];

        $buildModelFunc = function (Model $model) use ($ilId, $accountId, $startDate, $endDate) {
            return $model->where('account_id', $accountId)
                ->whereBetween('start_time', [$startDate, $endDate])
                ->where('watch_account_id', '>', 0)
                ->when($ilId, function ($query) use ($ilId) {
                    $query->where('il_id', $ilId);
                });
        };

        $queryListFunc = function (Builder $model, $page, $pageSize) {
            return $model->groupBy(['watch_account_id'])
                ->orderByDesc('end_time')
                ->forPage($page, $pageSize)
                ->selectRaw('id, il_id, watch_account_id')
                ->get()
                ->toArray();
        };

        // 只导出房间下的UV
        $roomInfo = vss_model()->getRoomsModel()->getInfoByIlId($ilId);
        if (!$roomInfo) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }

        $exportProxyService = vss_service()->getExportProxyService()->init($file)->putRow($header);

        $exportDataFunc = function ($baseModel, $type = '直播') use ($queryListFunc, $exportProxyService, $roomInfo) {
            $page     = 1;
            $pageSize = 1000;
            while (true) {
                $list = $queryListFunc(clone $baseModel, $page++, $pageSize);
                if (!$list) {
                    break;
                }

                // 查询最早开始时间
                $watchAccountIds = array_column($list, 'watch_account_id');

                // 查询用户昵称和账号
                $accountMap = vss_model()->getAccountsModel()
                    ->whereIn('account_id', $watchAccountIds)
                    ->get(['account_id', 'username', 'nickname'])
                    ->toArray();
                $accountMap = array_column($accountMap, null, 'account_id');

                $exportData = [];
                foreach ($list as $item) {
                    $accountId    = $item['watch_account_id'];
                    $exportData[] = [
                        $roomInfo['il_id'],
                        $roomInfo['subject'],
                        $accountMap[$accountId]['nickname'],
                        $accountMap[$accountId]['username'],
                        $type,
                    ];
                }

                $exportProxyService->putRows($exportData);
            }
        };

        // 导出直播数据
        if ($type != RoomConstant::LIVE_PLAY_BACK) {
            $baseModel = $buildModelFunc(vss_model()->getRoomAttendsModel());
            $exportDataFunc($baseModel);
        }

        // 导出回放数据
        if ($type != RoomConstant::LIVE_STATUS_START and method_exists(vss_model(), 'getRecordAttendsModel')) {
            $baseModel = $buildModelFunc(vss_model()->getRecordAttendsModel());
            $exportDataFunc($baseModel, '回放');
        }

        $exportProxyService->close();
    }

    /**
     * pv导出配置
     *
     * @param     $accountId
     * @param     $beginTime
     * @param     $endTime
     *
     * @param int $status
     *
     * @param     $ilId
     *
     * @return mixed
     *
     * @author  jin.yang@vhall.com
     * @date    2020-10-27
     */
    public function exportPv($ilId, $accountId, $beginTime, $endTime, $status = 1)
    {
        $accountId = $accountId ?: 0;
        $ilId      = $ilId ?: 0;

        if (empty($ilId) && empty($accountId)) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }

        $params     = [
            'il_id'      => $ilId,
            'account_id' => $accountId,
            'begin_time' => $beginTime,
            'end_time'   => $endTime,
            'status'     => $status,
        ];
        $statusMap  = [
            1 => 'All',
            2 => 'Live',
            3 => 'Record',
        ];
        $statusName = $statusMap[$status] ?? 'all';

        $insert = [
            'export'     => 'pv',
            'il_id'      => $ilId,
            'account_id' => $accountId,
            'file_name'  => $statusName . 'StatRoomsPvList' . date('YmdHis') . $accountId,
            'title'      => ['房间ID', '房间名称', '昵称', '账号', '进入时间', '离开时间', '观看时长（分钟）', '观看终端', '地理位置', '类型'],
            'params'     => json_encode($params),
            'callback'   => 'stat:exportPvData',
        ];

        vss_logger()->info('stat_create:', $insert);

        return vss_model()->getExportModel()->create($insert);
    }

    /**
     * pv导出内容
     *
     * @param $filePath
     *
     * @param $export
     *
     * @return bool
     * @author  jin.yang@vhall.com
     * @date    2020-10-27
     */
    public function exportPvData($export, $filePath)
    {
        $params    = json_decode($export['params'], true);
        $header    = json_decode($export['title'], true);
        $file      = $filePath . $export['file_name'];
        $page      = 1;
        $status    = $params['status'];
        $redord    = $status == RoomConstant::LIVE_PLAY_BACK;
        $condition = [
            'il_id'      => $params['il_id'],
            'begin_time' => $params['begin_time'],
            'end_time'   => $params['end_time'],
        ];
        if ($params['account_id']) {
            $condition['account_id'] = $params['account_id'];
        }
        $liveCondition    = $condition;
        $modelRoomAttends = vss_model()->getRoomAttendsModel();
        $with             = ['watchAccount', 'rooms'];
        //是否引入回放组件
        $isRecordExists = method_exists(vss_model(), 'getRecordAttendsModel');

        //写入文件
        $exportProxyService = vss_service()->getExportProxyService()->init($file)->putRow($header);
        while (true) {
            $type = $redord ? '回放' : '直播';
            if ($status == RoomConstant::LIVE_STATUS_START) {
                $liveAttendList = $modelRoomAttends::getInstance()->setPerPage(1000)->getList(
                    $liveCondition,
                    $with,
                    $page
                );
            } elseif ($status == RoomConstant::LIVE_PLAY_BACK) {
                if (!$isRecordExists) {
                    break;
                }
                $modelRecordAttends = vss_model()->getRecordAttendsModel();
                $liveAttendList     = $modelRecordAttends::getInstance()->setPerPage(1000)->getList(
                    $condition,
                    $with,
                    $page
                );
            } else {
                //全部数据
                if ($redord) {
                    if (!$isRecordExists) {
                        break;
                    }
                    $modelRecordAttends = vss_model()->getRecordAttendsModel();
                    $liveAttendList     = $modelRecordAttends::getInstance()->setPerPage(1000)->getList(
                        $condition,
                        $with,
                        $page
                    );
                } else {
                    $liveAttendList = $modelRoomAttends::getInstance()->setPerPage(1000)->getList(
                        $liveCondition,
                        $with,
                        $page
                    );
                    if ($page >= $liveAttendList->lastPage()) {
                        $redord = true;
                        $page   = 0;
                    }
                }
            }

            if (!empty($liveAttendList->items())) {
                $exportData = [];
                foreach ($liveAttendList->items() as $liveAttendKey => $liveAttendItem) {
                    $exportData[] = [
                        'il_id'      => $liveAttendItem['il_id'] ?: '-',
                        'name'       => $liveAttendItem['rooms']['subject'] ?: ' -',
                        'nickname'   => $liveAttendItem['watchAccount']['nickname'] ?: '-',
                        'username'   => $liveAttendItem['watchAccount']['username'] ?: '-',
                        'start_time' => $liveAttendItem['start_time'] ?: '-',
                        'end_time'   => $liveAttendItem['end_time'] ?: '-',
                        'duration'   => ceil($liveAttendItem['duration'] / 60) ?? '-',
                        'terminal'   => $liveAttendItem['terminal'] == 7 ? 'PC端' : '移动端',
                        'province'   => $liveAttendItem['province'] ?: '-',
                        'type'       => $type,
                    ];
                }
                $exportProxyService->putRows($exportData);
            }

            //跳出while
            if ($page >= $liveAttendList->lastPage()) { //1页表示1W上限
                break;
            }

            //下一页
            $page++;
        }

        $exportProxyService->close();

        return true;
    }
}
