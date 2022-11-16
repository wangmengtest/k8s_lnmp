<?php

namespace vhallComponent\decouple\controllers;

use Illuminate\Support\Facades\Request;
use Vss\Traits\ResponseTrait;
use Illuminate\Routing\Controller as LaravelController;

class BaseController extends LaravelController
{
    use ResponseTrait;

    protected $startTime;

    /**
     * 用户账户信息
     * 在 Api、Console 等模块下有值
     * @var mixed
     */
    protected $accountInfo;

    /**
     * 管理员账户信息
     * 该属性在 admin 模块下才有值
     * @var mixed
     */
    protected $admin;

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->startTime = self::getMicroTime();

        $this->accountInfo = vss_request()->get('vss_account_info');
        $this->admin       = vss_request()->get('vss_admin');

        vss_request()->offsetUnset('vss_account_info');
        vss_request()->offsetUnset('vss_admin');

        $this->init();
    }

    /**
     * 兼容子类的 init 函数，使之自动执行
     * @auther yaming.feng@vhall.com
     * @date 2021/5/20
     */
    public function init()
    {
    }

    /**
     * 返回当前模块名
     *
     * @access public
     * @return string
     */
    public function getModule()
    {
        return get_module_name();
    }

    /**
     * 返回当前控制器名
     *
     * @access public
     * @return string
     */
    public function getController()
    {
        return get_controller_name();
    }

    /**
     * 返回当前动作名
     *
     * @access public
     * @return string
     */
    public function getActionName()
    {
        $action = vss_request()->route()->getActionMethod();
        return str_replace('Action', '', $action);
    }

    /**
     * 获取POST或者GET数据，优先获取POST数据
     * @access public
     * @param string $key
     * @param string $default
     * @return mixed
     */
    public function getParam($key = null, $default = '', $csrfCheck = false)
    {
        // 开启csrf 输入检测，检测来源
        if ($csrfCheck) {
            if (env('APP_ENV') =='local') {
                $referStand = "";
            }

            if (env('APP_ENV') == 'test') {
                $referStand = "t-csces.vhallyun.com";
            }

            if (env('APP_ENV') == 'prod') {
                $referStand = "live.cscecsteel.com";
            }

            $refer = Request::header('referer');

            if ($referStand && (!$refer || !strstr($refer, $referStand))) {
                return [];
            }
        }

        if (is_null($key)) {
            return vss_request()->all();
        }
        return vss_request()->get($key, $default);
    }

    /**
     * 获取 POSt 请求参数
     * @auther yaming.feng@vhall.com
     * @date 2021/4/25
     * @param null $key
     * @param string $default
     * @return array|string|null
     */
    public function getPost($key = null, $default = '')
    {
        return vss_request()->post($key, $default);
    }

    /**
     * 获取 Get 请求参数
     * @auther yaming.feng@vhall.com
     * @date 2021/4/25
     * @param null $key
     * @param string $default
     * @return array|string|null
     */
    public function getQuery($key = null, $default = '')
    {
        return vss_request()->query($key, $default);
    }

    /**
     * 请求发放: GET,POST,HEAD,PUT,CLI
     * @access public
     * @return bool
     */
    public function getMethod()
    {
        return vss_request()->getMethod();
    }

    /**
     * 是否PUT操作
     * @access public
     * @return bool
     */
    public function isPut()
    {
        return vss_request()->isMethod('PUT');
    }

    /**
     * 是否DELETE
     * @access public
     * @return bool
     */
    public function isDelete()
    {
        return vss_request()->isMethod('DELETE');
    }

    /**
     * 是否GET
     * @access public
     * @return bool
     */
    public function isGet()
    {
        return vss_request()->isMethod('GET');
    }

    /**
     * 是否POST
     * @access public
     * @return bool
     */
    public function isPost()
    {
        return vss_request()->isMethod('POST');
    }

    /**
     * 是否AJAX
     * @access public
     * @return bool
     */
    public function isAjax()
    {
        return vss_request()->ajax();
    }

    /**
     * 是否CLI模式
     * @access public
     * @return bool
     */
    public function isCli()
    {
        return app()->runningInConsole();
    }

    /**
     * 获取当前系统的微秒数
     * @return float $microTime
     */
    public function getMicroTime()
    {
        list($us, $sec) = explode(' ', microtime());
        return ((float)$us + (float)$sec);
    }

    /**
     * 格式化获取系统当前时间
     * @param int $time
     * @param string $format
     * @return string $dateTime
     */
    public function getDateTime($time = 0, $format = 'Y-m-d H:i:s')
    {
        if ($time == 0) {
            $time = time();
        }
        return date($format, $time);
    }

    /**
     * 返回PHP-FPM网关接管请求后PHP开始执行应用程序的时间，精确到微秒
     * @return float $startTime 返回PHP-FPM网关接管请求后PHP开始执行应用程序的时间，精确到微秒
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * 返回PHP-FPM网关接管请求后PHP执行应用程序业务消耗的时长，精确到毫秒
     * @return float $executeTime 返回PHP-FPM网关接管后PHP执行应用程序业务消耗的时长，精确到毫秒
     */
    public function getExecuteTime()
    {
        return round(self::getMicroTime() - $this->startTime, 4);
    }
}
