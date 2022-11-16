<?php

return [
    'name'     => 'perfctl',
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
                                    'name'    => 'perfctl-inavService-get-1',
                                    'content' => '
            //并发控制
            if($role == AccountConstant::TYPE_WATCH){
                $liveInfo = is_array($liveInfo)?$liveInfo:$liveInfo->toArray();
                $connectData = vss_service()->getConnectctlService()->connectCtl($liveInfo,$accountId);
                $data["room_max_count"] = $connectData["room_max_count"];
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
