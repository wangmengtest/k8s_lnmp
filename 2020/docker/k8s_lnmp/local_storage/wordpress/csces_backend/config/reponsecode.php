<?php

/**
 * 响应码定义
 * 前缀匹配规则，从下向上匹配到第一个前缀相同的，所以规则越具体写在越下面
 */

return array_reverse([
    'comp.*'       => 10000,
    'empty.*'      => 20000,
    'auth.*'       => 30000,
    'auth.login.*' => 30002,  // 30002 30004 13002  admin 跳转到登录页的 code, console 是 401
    'type.*'       => 40000,
    'business.*'   => 60000,
    'business.hot' => 100001, // 特殊错误码，前端要用, 跳转到活动火爆的页面中
    'failed'       => 50000,
    'success'      => 200,
]);
