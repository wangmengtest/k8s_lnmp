<?php

namespace vhallComponent\chat\controllers\console;

use vhallComponent\decouple\controllers\BaseController;

class ChatController extends BaseController
{
    /**
     * 创建消息异步导出
     *
     * @author  jin.yang@vhall.com
     * @date    2020-10-27
     */
    public function exportAction()
    {
        //参数列表
        $ilId         = $this->getParam('il_id');
        $beginTime    = $this->getParam('begin_time', '2020-01-01');
        $endTime      = $this->getParam('end_time', date('Y-m-d'));
        $filterStatus = $this->getParam('filter_status', 0);
        $accountId    = $this->accountInfo['account_id'];
        $fileName     = 'StatRoomsMessageList' . date('YmdHis') . $accountId;

        vss_service()->getChatService()->exportMessage(
            $ilId,
            $accountId,
            $fileName,
            $beginTime,
            $endTime,
            $filterStatus
        );
        $this->success();
    }

    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/3/9
     */
    public function listAction()
    {
        $list   = [];
        $result = vss_service()->getChatService()->chatList($this->getParam());

        if (isset($result['list'])) {
            foreach ($result['list'] as $item) {
                $message = vss_service()->getChatService()->formatMessage($item);

                if (isset($item['image_urls']) && $item['image_urls']) {
                    $message .= implode(' ', $item['image_urls']);
                }

                $list[] = [
                    'account_id' => $item['third_party_user_id'],
                    'username'   => $item['nick_name'],
                    'created_at' => $item['date_time'],
                    'content'    => $message
                ];
            }
        }

        $result['list'] = $list;
        $this->success($result);
    }
}
