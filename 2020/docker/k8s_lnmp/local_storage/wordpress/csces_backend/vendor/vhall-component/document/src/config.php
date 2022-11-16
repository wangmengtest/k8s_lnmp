<?php

return [
    'name'     => 'document',
    'snippets' => [
        [
            'codeContents' => [
                [
                    'parentDirectory' => '/room',
                    'content'         => [
                        [
                            'target'  => '/src/services/InavService.php',
                            'addType' => 'code',
                            'block'   => [
                                [
                                    'name'    => 'document-inavService-get-1',
                                    'content' => '
                $data["document_exists"] = vss_model()->getDocumentStatusModel()->findExistsByRecordId($liveInfo["record_id"],$ilId);
'
                                ]
                            ]
                        ],
                        [
                            'target'  => '/src/controllers/admin/StatController.php',
                            'addType' => 'code',
                            'block'   => [
                                [
                                    'name'    => 'document-adminStat-index-1',
                                    'content' => '
        $documentModel = vss_model()->getRoomDocumentsModel();
        $documentStat = [
            "total" => $documentModel->getCount(),
            "day"   => $documentModel->getCount($conditionDay),
            "week"  => $documentModel->getCount($conditionWeek),
            "month" => $documentModel->getCount($conditionMonth),
            "year"  => $documentModel->getCount($conditionYear),
        ];
        $data["document_stat"] = $documentStat;
'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'parentDirectory' => '/record',
                    'content'         => [
                        [
                            'target'  => '/src/models/RecordModel.php',
                            'addType' => 'code',
                            'block'   => [
                                [
                                    'name'    => 'document-RecordModel-getDocumentExistAttribute-1',
                                    'content' => '
        $isExist = vss_model()->getDocumentStatusModel()->findExistsByRecordId($this->record_id, $this->il_id);
'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'parentDirectory' => '/broadcast',
                    'content'         => [
                        [
                            'target'  => '/src/services/RebroadcastService.php',
                            'addType' => 'code',
                            'block'   => [
                                [
                                    'name'    => 'document-rebroadcastService-preview-1',
                                    'content' => '
        try {
            $watch_info = vss_service()->getDocumentService()->watchInfo([
                "app_id"  => $sourceInfo->app_id,
                "channel" => $sourceInfo->channel_id
            ]);
        } catch (\Exception $e) {
            $watch_info["list"] = [];
        }
'
                                ],
                                [
                                    'name'    => 'document-rebroadcastService-1',
                                    'content' => '
        try {
            $watch_info = vss_service()->getDocumentService()->watchInfo([
                "app_id"  => $sourceInfo->app_id,
                "channel" => $sourceInfo->channel_id
            ]);
        } catch (\Exception $e) {
            $watch_info["list"] = [];
        }

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
