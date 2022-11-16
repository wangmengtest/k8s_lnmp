<?php

return [
    'name'     => 'anchormanage',
    'snippets' => [
        [
            'codeContents' => [
                [
                    'parentDirectory' => '/room',
                    'content'         => [
                        [
                            'target'  => '/src/controllers/console/RoomController.php',
                            'addType' => 'code',
                            'block'   => [
                                [
                                    'name'    => 'anchormanage-roomController-get-1',
                                    'content' => '
            //主播关联
            $anchorId = $this->getParam("anchor_id", "");
            if ($anchorId) {
                vss_service()->getAnchorManageService()->linkAnchorRoom($anchorId, $roomInfo["il_id"]);
            }
'
                                ],
                            ]
                        ]
                    ]
                ],
                [
                    'parentDirectory' => '/room',
                    'content'         => [
                        [
                            'target'  => '/src/controllers/console/RoomController.php',
                            'addType' => 'code',
                            'block'   => [
                                [
                                    'name'    => 'anchormanage-roomController-get-2',
                                    'content' => '
        //获取主播id
        $anchor = vss_service()->getAnchorManageService()->getAnchorIdByIlId($params["il_id"]);
        if ($anchor) {
            $data["anchor_id"] = $anchor->anchor_id;
        }
'
                                ],
                            ]
                        ]
                    ]
                ],
                [
                    'parentDirectory' => '/room',
                    'content'         => [
                        [
                            'target'  => '/src/controllers/console/RoomController.php',
                            'addType' => 'code',
                            'block'   => [
                                [
                                    'name'    => 'anchormanage-roomController-get-3',
                                    'content' => '
            //修改/取消关联主播
            if ($roomInfo["status"] == 0) {
                $anchorId = $this->getParam("anchor_id", "");
                if ($anchorId) {
                    vss_service()->getAnchorManageService()->modifyLink($anchorId, $params["il_id"]);
                } else {
                    vss_service()->getAnchorManageService()->deleteLink($params["il_id"]);
                }
            }
'
                                ],
                            ]
                        ]
                    ]
                ],
                [
                    'parentDirectory' => '/room',
                    'content'         => [
                        [
                            'target'  => '/src/services/RoomService.php',
                            'addType' => 'code',
                            'block'   => [
                                [
                                    'name'    => 'anchormanage-roomService-get-1',
                                    'content' => '
                //取消关联主播
                vss_service()->getAnchorManageService()->deleteLink($ilId);
'
                                ],
                            ]
                        ]
                    ]
                ],
            ]
        ]
    ]
];
