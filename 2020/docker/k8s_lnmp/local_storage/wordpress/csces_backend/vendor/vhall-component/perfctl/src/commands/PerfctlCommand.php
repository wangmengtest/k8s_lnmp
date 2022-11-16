<?php

namespace vhallComponent\perfctl\commands;

use vhallComponent\decouple\commands\BaseCommand;
use vhallComponent\perfctl\constants\PerfctlConstants;

/**
 * 流量控制队列监听， 配合 Nginx + lua 脚本使用
 * 需要运用将该命令添加的进程管理工具中: supervisor
 *
 * Class PerfctlCommand
 * @package vhallComponent\perfctl\commands
 */
class PerfctlCommand extends BaseCommand
{
    public $name = 'perfctl:queue';

    public $description = '流量控制队列监听消费';

    /**
     * 命令执行入口
     * @auther yaming.feng@vhall.com
     * @date 2021/5/27
     * @return bool|mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handle()
    {
        $this->infoLog("开始执行");

        $ilidsKey = PerfctlConstants::PERFCTL_BEGINNING_ILINFO;
        $key      = PerfctlConstants::LOCK_QUEUE_CRON_EXEC;

        while (true) {
            $lock = vss_redis()->lock($key, 60);

            if (!$lock) {
                // 该逻辑走数据库，每分钟执行一次
                $this->infoLog("更新开播中的直播间");
                $this->updateRunningRoom($ilidsKey);
            } else {
                // 该逻辑走缓存，每秒执行一次
                $this->infoLog("检查排队队列");
                $this->checkQueue($ilidsKey);
            }

            sleep(1);
        }
    }

    /**
     * 更新开播中的直播间
     * @auther yaming.feng@vhall.com
     * @date 2021/5/27
     *
     * @param $ilidsKey
     *
     * @return bool
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function updateRunningRoom($ilidsKey)
    {
        try {
            $interactiveLiveList = vss_model()->getRoomsModel()->getAllOnlineChannelInfos();
            if (empty($interactiveLiveList)) {
                vss_redis()->del($ilidsKey);
                return true;
            }
            foreach ($interactiveLiveList as $key => $ilInfo) {
                vss_service()->getConnectctlService()->connectCtl($ilInfo);
            }
            vss_redis()->set($ilidsKey, $interactiveLiveList);
            return true;
        } catch (\Exception $e) {
            $this->exceptionLog($e);
        }

        return false;
    }

    /**
     * 检查用户排队队列，通知用户进入直播间
     * @auther yaming.feng@vhall.com
     * @date 2021/5/27
     */
    protected function checkQueue($ilidsKey)
    {
        //秒执行逻辑 全部走缓存
        $interactiveLiveList = vss_redis()->get($ilidsKey);
        foreach ($interactiveLiveList as $ilInfo) {
            $remainCount = vss_redis()->get(PerfctlConstants::CONNECT_COUNT_OF_ACCOUNT_BY_ILID . $ilInfo['il_id']);
            if ($remainCount <= 0) {
                continue;
            }
            vss_service()->getConnectctlService()->notifyQueueAccount(
                $ilInfo['il_id'],
                $ilInfo['room_id'],
                $remainCount
            );
        }
    }
}
