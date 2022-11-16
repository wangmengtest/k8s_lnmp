<?php

return [
    'name' => 'broadcast',
    'snippets' => [
        [
            'homeDirectory' => './app/vendor/vhall-component',
            'codeContents' => [
                [
                    'parentDirectory' => '/room',
                    'content' => [
                        [
                            'target' => '/src/services/RoomService.php',
                            'addType' => 'code',
                            'block' => [
                                [
                                    'name' => 'broadcast-RoomService-getAttr-1',
                                    'content' => '
        //房间转播情况
        $rebroadcast = vss_model()->getRebroadCastModel()->getStartRebroadcastByRoomId($roomId);
        $rebroadcast_room_id = $rebroadcast->source_room_id;
        if ($rebroadcast_room_id) {
            $roomModel = vss_model()->getRoomsModel()->findByRoomId($rebroadcast_room_id);
        }
        if (!empty($roomModel)) {
            $rebroadcast_channel_id = $roomModel->channel_id;
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
