<?php

return [
    'name'     => 'redpacket',
    'snippets' => [
        [
            'codeContents' => [
                [
                    'parentDirectory' => '/room',
                    'content'         => [
                        [
                            'target'  => '/src/models/RoomsModel.php',
                            'addType' => 'code',
                            'block'   => [
                                [
                                    'name'    => 'redpacket-RoomsModel-boot-1',
                                    'content' => '
                        $redpacketService = vss_service()->getRedpacketService();
                        $redpacketService->overBySourceId(["app_id" => $data->app_id, "source_id" => $data->room_id]);
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
