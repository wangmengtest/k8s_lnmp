<?php

return [
    'name'     => 'invitecard',
    'snippets' => [
        [
            'homeDirectory' => './app/vendor/vhall-component',
            'codeContents'  => [
                [
                    'parentDirectory' => '/room',
                    'content'         => [
                        [
                            'target'  => '/src/services/InavService.php',
                            'addType' => 'code',
                            'block'   => [
                                [
                                    'name'    => 'invitecard-InavService-get-1',
                                    'content' => '
        //邀请卡状态
        $inviteStatus = vss_service()->getInviteCardService()->getStatus($liveInfo["room_id"]);
        $data["invite_status"] = $inviteStatus;
'
                                ]
                            ]
                        ],
                        [
                            'target'  => '/src/services/RoomService.php',
                            'addType' => 'code',
                            'block'   => [
                                [
                                    'name'    => 'invitecard-RoomService-getAttr-1',
                                    'content' => '
        $data["is_invitecard"] = vss_service()->getInviteCardService()->getStatus($roomId);
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
