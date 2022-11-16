<?php

return [
    "bu"          => 5,
    "debug"       => env('APP_DEBUG', false),
    "saas"        => [
        "domain" => null
    ],

    /**
     * paas 相关配置
     */
    "paas"        => [
        "host"     => "https://api.vhallyun.com",
        "bu"       => 1,
        "callback" => "http://open.vhallyun.domain/internal/document/update-by-document",
        "apps"     => [
            "lite" => [
                "appId"     => env('APP_ID'),
                "appSecret" => env('APP_SECRET'),
            ]
        ],
    ],

    /**
     * paas 接口域名
     */
    "domain"      => [
        "services" => [
            "message"       => "http://dev-msg-zhike.vhall.domain",
            "sso"           => "http://t-sso.e.vhall.com",
            "document"      => "http://services.vhallyun.com/api_document_master",
            "stream"        => "http://relay01.vhallalibj.com:80",
            "im"            => "http://services.vhallyun.com/api_im_master",
            "recommendCard" => "http://test-card.vhall.domain"
        ]
    ],

    /**
     * 文档 CND 域名
     */
    "documentUrl" => "https://cnstatic01.e.vhall.com/document",

    /**
     * 上传配置
     */
    "upload"      => [
        "upload" => env('UPLOAD_DOMAIN', 'http://d.csces-upload.com'),
        "download" => env('DOWNLOAD_DOMAIN', 'http://d.csces-files.com')
    ],
    "pay"         => [
        "baseUrl" => "https://test-pay.vhall.com/v1/",
        "fakePay" => true
    ],
    "forward"     => [
        "host" => "https://vps-new.vhallyun.com"
    ],

    "thirdStreamUrl" => "http://relay.vhallservice.com:5000",
    "application"    => [
        "static" => [
            "headPortrait" => [
                "default" => "//t-static01-open.e.vhall.com/static/v1/img/vop/tipsface.png"
            ]
        ],
        "url"    => env('APP_URL', "https://t-project-api.vhallyun.com"),
        "host"   => env('APP_HOST', "https://t-project.vhallyun.com")
    ],

    /**
     * 短信发送相关的配置
     */
    /*"sms"            => [
        "strategy" => "Mwgate",
        "userId"   => "J71027",
        "password" => "263201"
    ],*/
    "sms"            => [
        "strategy" => "Yc",
        "userId"   => "J71027",
        "password" => "263201"
    ],

    /**
     * 导出配置
     */
    "export" => [
        'default_driver' => 'csv'  // csv, excel
    ],
];
