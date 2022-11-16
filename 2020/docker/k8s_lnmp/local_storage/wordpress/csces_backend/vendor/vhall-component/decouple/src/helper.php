<?php

use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use vhallComponent\decouple\proxy\DBProxy;
use vhallComponent\decouple\proxy\LogProxy;
use vhallComponent\decouple\proxy\QueueProxy;
use vhallComponent\decouple\proxy\RedisProxy;
use Vss\Utils\DataStructUtil;
use Vss\Utils\ModelUtil;
use Vss\Utils\PaasSignUtil;
use Vss\Utils\ServiceUtil;

/**
 * 获取类的对象
 * @auther yaming.feng@vhall.com
 * @date 2021/5/6
 *
 * @param       $abstract
 * @param array $parameters
 *
 * @return mixed
 * @throws BindingResolutionException
 */
function vss_make($abstract, array $parameters = [])
{
    return app()->make($abstract, $parameters);
}

/**
 * 获取配置
 * @auther yaming.feng@vhall.com
 * @date 2021/4/13
 *
 * @param null   $key
 * @param null   $defaultVal
 * @param string $prefix 前缀即配置文件
 *
 * @return Repository|Application|mixed
 */
function vss_config($key = null, $defaultVal = null, string $prefix = 'vhall')
{
    return config($prefix . '.' . $key, $defaultVal);
}

/**
 * 参数校验
 * @auther yaming.feng@vhall.com
 * @date 2021/4/20
 *
 * @param       $data
 * @param       $rules
 * @param array $messages
 * @param array $customAttributes
 *
 * @return array
 * @throws ValidationException
 */
function vss_validator($data, $rules, array $messages = [], array $customAttributes = []): array
{
    return validator($data ?? [], $rules, $messages, $customAttributes)->validate();
}

/**
 * @auther yaming.feng@vhall.com
 * @date 2021/4/22
 *
 * @param null $key
 * @param null $default
 *
 * @return array|Application|Request|string|null
 */
function vss_request($key = null, $default = null)
{
    return request($key, $default);
}

/**
 * 获取 model
 * @auther yaming.feng@vhall.com
 * @date 2021/4/20
 * @return ModelUtil
 */
function vss_model(): ModelUtil
{
    return ModelUtil::getInstance();
}

/**
 * 获取 service
 * @auther yaming.feng@vhall.com
 * @date 2021/4/20
 * @return ServiceUtil
 */
function vss_service(): ServiceUtil
{
    return ServiceUtil::getInstance();
}

/**
 * 获取 redis 对象
 * @auther yaming.feng@vhall.com
 * @date 2021/4/20
 * @return RedisProxy
 */
function vss_redis(): RedisProxy
{
    return RedisProxy::getInstance();
}

/**
 * 获取 logger 对象
 * @auther yaming.feng@vhall.com
 * @date 2021/4/20
 *
 * @param string $channel
 *
 * @return LogProxy
 */
function vss_logger(string $channel = 'papertrail'): LogProxy
{
    return LogProxy::getInstance($channel);
}

/**
 * 获取队列对象
 * @auther yaming.feng@vhall.com
 * @date 2021/5/10
 *
 * @param string $channel
 *
 * @return mixed|QueueProxy
 */
function vss_queue(string $channel = 'default')
{
    // 组件队列 channel 自动适配， 调用该方法的文件
    $file = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'] ?? '';

    $regs = [
        '/.*vhall-component\/(.*)\/src.*/',
        '/.*app\/Component\/(.*)\/src.*/'
    ];

    foreach ($regs as $reg) {
        preg_match($reg, $file, $match);
        $componentName = $match[1] ?? null;
        if ($componentName) {
            $channel = vss_config('channel.' . $componentName, $channel, 'queue');
            break;
        }
    }

    return QueueProxy::getInstance($channel);
}

/**
 * @auther yaming.feng@vhall.com
 * @date 2021/4/26
 * @return PaasSignUtil
 */
function vss_paas_util(): PaasSignUtil
{
    return PaasSignUtil::getInstance();
}

/**
 * @return DBProxy
 * @since  2021/7/7
 * @author yaming.feng@vhall.com
 */
function vss_db(): DBProxy
{
    return DBProxy::getInstance();
}

/**
 * 数据结构校验
 *
 * @param array $data   要返回的数据
 * @param array $struct 返回数据的结构
 * @param bool  $strict 是否是严格模式， 严格模式下，缺失字段，并且没有设置默认值，会抛出错误
 *
 * @return array
 * @since  2021/7/13
 *
 * @author yaming.feng@vhall.com
 */
function vss_data_struct(array $data, array $struct, bool $strict = true): array
{
    return (new DataStructUtil($struct, $strict))->cast($data);
}

/**
 * 组件目录
 * @auther yaming.feng@vhall.com
 * @date 2021/5/6
 *
 * @param string $path
 *
 * @return string
 */
function component_path(string $path = ''): string
{
    return base_path('vendor/vhall-component/' . $path);
}

/**
 * 组件目录列表
 * @return array
 * @since  2021/7/7
 * @author fym
 */
function component_paths(): array
{
    return [
        component_path(),
        app_path('Component')
    ];
}

/**
 * 获取语言包
 *
 * @param string $lang
 *
 * @return array
 * @author fym
 * @since  2021/7/23
 */
function lang_code($lang = "zh"): array
{
    $path = component_path("language-pack/$lang/code.json");
    if (is_file($path)) {
        return json_decode(file_get_contents($path) ?? "[]", true);
    }
    return [];
}

/**
 * 生成随机字符串
 * @auther yaming.feng@vhall.com
 * @date 2021/5/21
 *
 * @param int $length
 *
 * @return string
 */
function str_random(int $length = 16): string
{
    return Str::random($length);
}

/**
 * 删除 pageList 对象中不需要的属性
 * @auther yaming.feng@vhall.com
 * @date 2021/4/12
 *
 * @param $data
 *
 * @return mixed
 */
function trim_page_list($data)
{
    if ($data instanceof LengthAwarePaginator) {
        $data = json_decode(json_encode($data), true);
    }

    if (!$data || !is_array($data) || !isset($data['current_page'])) {
        return $data;
    }

    $trimFields = [
        'first_page_url',
        'last_page_url',
        'next_page_url',
        'prev_page_url',
        'last_page',
        'to',
        'from',
        'path',
        'links'
    ];

    $keys = array_keys($data);
    foreach ($trimFields as $field) {
        if (in_array($field, $keys)) {
            unset($data[$field]);
        }
    }

    if (isset($data['per_page'])) {
        $data['per_page'] = intval($data['per_page']);
    }

    return $data;
}

/**
 * 路由处理
 * @auther yaming.feng@vhall.com
 * @date 2021/4/20
 *
 * @param $module
 * @param $class
 * @param $action
 *
 * @return mixed
 * @throws BindingResolutionException
 */
function route_handle($module, $class, $action)
{
    $module     = Str::ucfirst($module);
    $class      = Str::ucfirst(Str::camel($class));
    $action     = Str::camel($action . 'Action');
    $controller = "\\App\\Http\\Modules\\$module\\Controllers\\$class";

    request()->route()
        ->setUri("$module/$class/$action")
        ->uses([
            $controller,
            $action
        ]);

    $ctrl = app()->make($controller);
    return app()->call([$ctrl, $action]);
}

/**
 * 检查当前请求是否和路径匹配
 * @auther yaming.feng@vhall.com
 * @date 2021/4/22
 *
 * @param Request                  $request
 * @param                          $paths
 *
 * @return bool
 */
function is_matching_path(Request $request, $paths): bool
{
    foreach ($paths as $path) {
        if ($path !== '/') {
            $path = trim($path, '/');
        }

        if ($request->fullUrlIs($path) || $request->is($path)) {
            return true;
        }
    }

    return false;
}

/**
 * 获取当前的 controllerName
 * @auther yaming.feng@vhall.com
 * @date 2021/4/25
 * @return false|string
 */
function get_controller_name()
{
    $paths = explode('/', vss_request()->getRequestUri());
    return $paths[count($paths) - 2];
}

/**
 * @return string
 * @since  2021/7/13
 * @author yaming.feng@vhall.com
 */
function get_module_name(): string
{
    return explode('/', vss_request()->getRequestUri())[1];
}

/**
 * @return string
 * @since  2021/7/13
 * @author yaming.feng@vhall.com
 */
function get_action_name(): string
{
    $paths          = explode('/', vss_request()->getRequestUri());
    $actionAndQuery = $paths[count($paths) - 1];
    $name           = explode('?', $actionAndQuery)[0];
    return Str::camel($name);
}
