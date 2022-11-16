<?php

namespace Vss\Common\Controllers;

use Exception;
use vhallComponent\decouple\controllers\BaseController;
use Vss\Exceptions\CallbackException;
use Vss\Exceptions\ValidationException;

class CallbackBaseController extends BaseController
{

    /**
     * 回调参数
     *
     * @var array
     */
    protected $params = [];

    /**
     * 初始化
     */
    public function __construct()
    {
        parent::__construct();
        $this->params = $this->getParam();
    }

    /**
     * 回调接口统一入口
     *
     * @return void
     * @throws CallbackException
     */
    final public function indexAction()
    {
        try {
            $callback = [$this, $this->getEventMethodName()];
            if (!is_callable($callback)) {
                throw new ValidationException('event callback not exist.');
            }
            call_user_func_array($callback, []); // 方法存在，调用此方法
        } catch (Exception $e) {
            throw new CallbackException($e->getMessage(), $e->getCode(), $e);
        }

        $this->exit("success");
    }

    /**
     * 获取事件方法名
     *
     * @return string
     */
    protected function getEventMethodName(): string
    {
        $event = preg_replace(['/\//', '/-/'], ' ', $this->params['event']);
        return sprintf('event%s', str_replace(' ', '', ucwords($event)));
    }


    /**
     * 输出
     *
     * @param string $str
     *
     * @throws CallbackException
     */
    protected function exit(string $str = "")
    {
        if ($str == 'success') {
            throw new CallbackException($str, 200);
        }
        throw new CallbackException($str);
    }
}
