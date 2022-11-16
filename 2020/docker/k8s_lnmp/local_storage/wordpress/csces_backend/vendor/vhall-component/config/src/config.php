<?php

return [
    'name'     => 'config',
    'snippets' => [
        [
            'homeDirectory' => './app/vendor/vhall-component',
            'codeContents'  => [
                [
                    'parentDirectory' => '/room',
                    'content'         => [
                        [
                            'target'  => '/src/controllers/console/RoomController.php',
                            'addType' => 'code',
                            'block'   => [
                                [
                                    'name'    => 'config-RoomController-extend-1',
                                    'content' => '
        $columns = vss_service()->getConfigInfoService()->getRoomExtendColumn();
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
