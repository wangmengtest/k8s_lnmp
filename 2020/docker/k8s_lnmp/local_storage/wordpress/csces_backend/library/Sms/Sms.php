<?php
/**
 *+----------------------------------------------------------------------
 * @file Sms.php
 * @date 2019/3/31 19:37
 *+----------------------------------------------------------------------
 */

namespace Sms;

/**
 *+----------------------------------------------------------------------
 * Class Sms
 *+----------------------------------------------------------------------
 *
 * @package App\components
 * @author  ensong.liu@vhall.com
 * @date    2019-03-31 19:53:56
 * @version v1.0.0
 *+----------------------------------------------------------------------s
 */
class Sms
{
    /**
     * 短信策略
     *
     * @var string
     */
    public $strategy = 'Mwgate';

    /**
     * 短信策略配置
     *
     * @var string key:value,key2:value2,...
     */
    public $config = [];

    /**
     * 策略访问方法
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     * @author ensong.liu@vhall.com
     * @date   2019-03-31 19:52:02
     *
     */
    public function __call($name, $arguments)
    {
        $this->initConfig();
        $strategyClass = sprintf('\Sms\%sStrategy', ucfirst(strtolower($this->strategy)));
        $exist         = class_exists($strategyClass);
        if (!$exist) {
            $strategyClass = sprintf('vhallComponent\sms\strategys\%sStrategy', ucfirst(strtolower($this->strategy)));
        }
        if (!$exist) {
            $strategyClass = sprintf('App\Component\sms\src\strategys\%sStrategy', ucfirst(strtolower($this->strategy)));
        }
        $sms = new \Sms(new $strategyClass(), $this->config);

        return call_user_func_array([$sms, $name], $arguments);
    }

    /**
     *初始化配置
     */
    private function initConfig()
    {
        $this->config = vss_config('sms');
        if($this->config['strategy']){
            $this->strategy = $this->config['strategy'];
        }
    }
}
