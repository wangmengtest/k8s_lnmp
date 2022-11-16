<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// 根路由，方便部署测试
Route::get('/', function () {
    return response()->json([
        'code' => 200,
        'msg'  => 'success',
        'data' => []
    ]);
});

// 执行定时任务
Route::any('/crontab/{action}', function ($action) {
    set_time_limit(0);
    return \Illuminate\Support\Facades\Artisan::call("crontab:$action");
});

Route::prefix('v2')->middleware(['v2'])
    ->any('/{controller}/{action}', function ($class, $action) {
        return route_handle('v2', $class, $action);
    });

Route::prefix('api')->middleware(['api'])->group(function () {
    Route::any('/{controller}/{action}', function ($class, $action) {
        return route_handle('api', $class, $action);
    });

    // TODO 兼容 v1 的路由，以后可以删除
    Route::any("/v1/{controller}/{action}", function ($class, $action) {
        return route_handle('api', $class, $action);
    });
});

Route::prefix('console')->middleware(['console'])->group(function () {

    // TODO room 组件中的这个方法迁移到了 record 组件中
    Route::any('/room/set-default-record', function () {
        return route_handle('console', 'record', 'set-default-record');
    });

    // TODO 从 record 组件 迁移到 common 组件中
    Route::any('/upload/images', function () {
        return route_handle('console', 'common', 'upload-image');
    });

    Route::any('/{controller}/{action}', function ($class, $action) {
        return route_handle('console', $class, $action);
    });
});

Route::prefix('admin')->middleware(['admin'])->group(function () {

    // TODO room 组件中的这个方法迁移到了 record 组件中
    Route::any('/room/set-default-record', function () {
        return route_handle('admin', 'record', 'set-default-record');
    });

    Route::any('/{controller}/{action}', function ($class, $action) {
        return route_handle('admin', $class, $action);
    });
});

Route::prefix('callback')->middleware(['callback'])
    ->any('/{controller}/{action}', function ($class, $action) {
        return route_handle('callback', $class, $action);
    });

Route::prefix('health')->group(function () {
    // TODO 从 health 组件 迁移到 health 组件中
    Route::any('check', function () {
        return route_handle('health', 'health', 'check');
    });

    Route::any('mysql-check', function () {
        return route_handle('health', 'health', 'mysql-check');
    });

    Route::any('redis-check', function () {
        return route_handle('health', 'health', 'redis-check');
    });
});
