<?php

/**
 * 响应码定义
 * 前缀匹配规则，从下向上匹配到第一个前缀相同的，所以规则越具体写在越下面
 */

return array_reverse([
    'comp.*'                   => 10000,
    'empty.*'                  => 20000,
    'auth.*'                   => 30000,
    'auth.login.*'             => 30002,  // 30002 30004 13002  admin 跳转到登录页的 code, console 是 401
    'auth.login.token.expire'  => 40001,  // token expire console 是 40001
    'auth.login.already'       => 40001,  // auth.login.already 是 40001
    'auth.login.account.disable.verifytoken' => 40001,  // auth.login.account.disable.verifytoken 是 40001
    'auth.role.password.error' => 11404,  // 角色口令错误码, 前端有特殊处理
    'type.*'                   => 40000,
    'business.*'               => 60000,
    'business.hot'             => 100001, // 特殊错误码，前端要用, 跳转到活动火爆的页面中
    'comp.red.packet.expire'   => 110022, // 红包过期，前端有使用
    'failed'                   => 50000,
    'success'                  => 200,
    'mysql.insert.failed'      => 711,
    'mysql.select.failed'      => 712,
    'mysql.dml.failed'         => 710,
    'redis.dml.failed'         => 700,
    'redis.insert.failed'      => 701,
    'health.sign.failed'       => 720,
]);
