<?php

return [
    'name'     => 'record',
    'snippets' => [
        [
            'codeContents' => [
                [
                    'parentDirectory' => '/room',
                    'content'         => [
                        [
                            'target'  => '/src/services/StatService.php',
                            'addType' => 'code',
                            'block'   => [
                                [
                                    'name'    => 'record-StatService-allData-1',
                                    'content' => '
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
'
                                ],
                                [
                                    'name'    => 'record-statService-live-1',
                                    'content' => '
        if ($status == RoomConstant::LIVE_PLAY_BACK) { // 回访数据统计
            $result = vss_service()->getRecordService()->recordData($accountId, $ilId, $beginTime, $endTime, "admin");
        }
'
                                ],
                                [
                                    'name'    => 'record-statService-recordStat-1',
                                    'content' => '
        $data = vss_model()->getRecordStatsModel()->reCountListByCreatedTime($accountId, $ilId, $beginTime, $endTime);
'
                                ],
                                [
                                    'name'    => 'record-statService-recordTerminal-1',
                                    'content' => '
        $data = vss_model()->getRecordAttendsModel()->getTerminal($accountId, $ilId, $beginTime, $endTime);
'
                                ],
                                [
                                    'name'    => 'record-statService-liveRegion-1',
                                    'content' => '
        if ($status == RoomConstant::LIVE_PLAY_ALL || $status == RoomConstant::LIVE_PLAY_BACK) {
            $modelRecordAttends = vss_model()->getRecordAttendsModel();
            //地域分布图
            if ($country) {
                $recordData = $modelRecordAttends->getProvinceByCountry($country, $accountId, $ilId, $beginTime, $endTime);
            } else {
                $recordData = $modelRecordAttends->getCountry($accountId, $ilId, $beginTime, $endTime);
            }
        }
'
                                ]
                            ]
                        ],
                        [
                            'target'  => '/src/controllers/callback/LivesController.php',
                            'addType' => 'code',
                            'block'   => [
                                [
                                    'name'    => 'record-liveCallback-syncLiveInfo-1',
                                    'content' => '
                vss_service()->getRecordService()->mergeRecord([
                    "stream_id"  => $interactiveLiveInfo->room_id,
                    "start_time" => $interactiveLiveInfo->begin_live_time,
                    "end_time"   => $streamStatusInfo["end_time"],
                    "il_id"      => $interactiveLiveInfo->il_id,
                    "account_id" => $interactiveLiveInfo->account_id,
                    "source"     => 0
                ]);
'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'parentDirectory' => '/document',
                    'content'         => [
                        [
                            'target'  => '/src/models/DocumentStatusModel.php',
                            'addType' => 'code',
                            'block'   => [
                                [
                                    'name'    => 'record-DocumentStatusModel-findExistsByRecordId-1',
                                    'content' => '
            $recodeInfo = vss_model()->getRecordModel()->getInfoByVodId($recodeId);
'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];
