<?php

return [
    'name'     => 'tag',
    'snippets' => [
        [
            'codeContents' => [
                [
                    'parentDirectory' => '/room',
                    'content'         => [
                        [
                            'target'  => '/src/services/RoomService.php',
                            'addType' => 'code',
                            'block'   => [
                                [
                                    'name'    => 'tag-inavService-get-1',
                                    'content' => '
            $tags = vss_service()->getTagService()->tagsInfo($topocs);
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
