<?php

namespace App\Http\Modules\Health\Controllers;
use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;

class Health extends BaseController
{
    /**
     * 后端接口服务检测
     */
    public function checkAction(){
        return $this->success();
    }

    /**
     * redis服务检测
     */
    public function redisCheckAction(){
        if(!$this->checkHealthSign()){
            return $this->fail(ResponseCode::HEALTH_SIGN_FAILED);
        }
        try {
            $uniqueKey = 'ops_monitor_'.uniqid();
            $uniqueVal = uniqid();
            vss_redis()->set($uniqueKey, $uniqueVal, 30);
            $res = vss_redis()->get($uniqueKey);
            if (empty($res)) {
                return $this->fail(ResponseCode::REDIS_INSERT_FAILED);
            }
        } catch (\Throwable $e) {
            return $this->fail(ResponseCode::REDIS_DML_FAILED);
        }
        return $this->success();
    }

    /**
     * mysql服务检测
     */
    public function mysqlCheckAction(){
        if(!$this->checkHealthSign()){
            return $this->fail(ResponseCode::HEALTH_SIGN_FAILED);
        }
        try {
            $uniqueVal = uniqid();
            $info = vss_model()->getOpsMonitorModel()->create(['val' => $uniqueVal]);
            if (empty($info->id)) {
                return $this->fail(ResponseCode::MYSQL_INSERT_FAILED);
            }
            sleep(2);
            $res = vss_model()->getOpsMonitorModel()->find($info->id);
            if (empty($res->val)) {
                return $this->fail(ResponseCode::MYSQL_SELECT_FAILED);
            }
            vss_model()->getOpsMonitorModel()->delRow($info->id, true);
        } catch (\Throwable $e) {
            return $this->fail(ResponseCode::MYSQL_DML_FAILED);
        }
        return $this->success();
    }

    /**
     * 检查health_sign签名
     */
    private function checkHealthSign()
    {
        $params = self::getParam();
        if (self::healthSign($params, env('health_sign_key')) !== $params['health_sign']) {
            return false;
        }
        if (!isset($params['health_sign_time']) || (abs(time() - $params['health_sign_time']) > 60) || !is_numeric($params['health_sign_time'])) {
            return false;
        }
        return true;
    }

    /**
     * 获取health_sign签名字符串
     * MD5(health_sign_key+health_sign_time)
     */
    private static function healthSign(array $arr, $healthKey)
    {
        return md5($healthKey . $arr['health_sign_time']);
    }
}
