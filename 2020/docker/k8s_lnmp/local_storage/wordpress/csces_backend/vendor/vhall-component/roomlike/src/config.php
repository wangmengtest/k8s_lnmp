<?php

return [
    'name'     => 'roomlike',
    'snippets' => [
        [
            'codeContents' => [
                [
                    'parentDirectory' => '/room',
                    'content'         => [
                        [
                            'target'  => '/src/controllers/v2/InavController.php',
                            'addType' => 'code',
                            'block'   => [
                                [
                                    'name'    => 'roomlike-userstatus',
                                    'content' => '
        $data["is_like"] = vss_model()->getRoomLikeModel()->where([
            "room_id"    => $params["room_id"],
            "account_id" => $params["account_id"]
        ])->count();
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
