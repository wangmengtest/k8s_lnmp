<?php

return [
    'name'     => 'watchlimit',
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
                                    'name'    => 'watchlimit-inavService-get-1',
                                    'content' => '
        //观看限制
        $liveInfo["phone"]     = $accountInfo["phone"];
        $applyInfo             = vss_service()->getWatchlimitService()->getApplyorderby($ilId);
        $liveInfo["form_id"]   = $applyInfo["source_id"];
        $liveInfo["accs_type"] = $accountInfo["account_type"];
        $data["is_visitor"]    = $liveInfo["is_visitor"] = $accountInfo["account_type"] == AccountConstant::ACCOUNT_TYPE_VISITOR ? 1 : 0;
'
                                ],
                                [
                                    'name'    => 'watchlimit-inavService-get-2',
                                    'content' => '
           //观看权限判断
            if(empty($password)){
                vss_service()->getWatchlimitService()->watchdecide($data);
            }
'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'parentDirectory' => '/account',
                    'content'         => [
                        [
                            'target'  => '/src/services/AccountService.php',
                            'addType' => 'code',
                            'block'   => [
                                [
                                    'name'    => 'watchlimit-accountService-login-1',
                                    'content' => '
        //观看限制登录
        $ilId     = $params["il_id"] ?? "";
        $password = isset($params["password"]) ? $params["password"] :0;
        if($ilId > 0 ) {
             $loginwatch = vss_service()->getWatchlimitService()->getLoginWatch($ilId,$password,$phone);
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
