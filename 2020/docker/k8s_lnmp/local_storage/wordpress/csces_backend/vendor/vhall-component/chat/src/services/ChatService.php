<?php

namespace vhallComponent\chat\services;

use App\Constants\ResponseCode;
use Exception;
use vhallComponent\chat\constants\console\ChatConstant;
use vhallComponent\export\models\ExportModel;
use Vss\Common\Services\WebBaseService;
use Vss\Exceptions\JsonResponseException;

class ChatService extends WebBaseService
{
    /**
     * 上传图片
     *
     * @param $params
     *
     * @return array
     * @throws JsonResponseException
     */
    public function upload($params)
    {
        try {
            $file = isset($_FILES['file']) ? 'file' : ($params['file'] ?? '');
            $file = vss_service()->getUploadService()->uploadImg($file, false, $params['ext'] ?? '');
        } catch (Exception $e) {
            vss_logger()->error('ChatService:upload error', [
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
            $this->fail(ResponseCode::BUSINESS_UPLOAD_FAILED);
        }

        return [
            'url' => $file,
        ];
    }

    /**
     * 历史信息
     *
     * @param $params
     *
     * @return array
     * @throws Exception
     */
    public function lists($params)
    {
        vss_validator($params, [
            'room_id'   => 'required',
            'curr_page' => 'filled',
            'page_size' => 'filled',
        ]);
        $page           = $params['curr_page'];
        $pageSize       = $params['page_size'] ?? 20;
        $orderBy        = $params['order_by'] ?? 'desc';
        $params['type'] = $params['type'] ?? 'all';
        $roomInfo       = vss_model()->getRoomsModel()->findByRoomId($params['room_id'])->toArray();//获取活动信息
        if (empty($roomInfo)) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }
        $params['channel']   = $roomInfo['channel_id'];
        $params['target_id'] = $roomInfo['channel_id'];

        $request = [
            'channel_id'    => $roomInfo['channel_id'],
            'msg_type'      => $params['type'],
            'page_size'     => $pageSize,
            'order_by'      => $orderBy,
            'filter_status' => 0,
            'start_time'    => !empty($params['start_date']) ? $params['start_date'] : $roomInfo['created_at'],
        ];

        $page && $request['curr_page'] = $page;
        $chatList    = vss_service()->getPaasChannelService()->getMessageLists($request);
        $userListArr = [];
        if (!empty($chatList['list'])) {
            $lists = vss_model()->getRoomJoinsModel()
                ->where('room_id', $roomInfo['room_id'])
                ->whereIn('account_id', array_column($chatList['list'], 'third_party_user_id'))
                ->get()->toArray();
            foreach ($lists as $list) {
                $userListArr[$list['account_id']] = $list;
            }
        }
        $hourMark = '00';
        $chatArr  = [];
        foreach (array_reverse($chatList['list'] ?? []) as $chat) {
            $user = $userListArr[$chat['third_party_user_id']];

            $chArr                         = [];
            $chArr['data']['type']         = $chat['type'];
            $chArr['data']['text_content'] = $chat['data'];
            $chArr['date_time']            = $chat['date_time'];
            $chArr['sender_id']            = $chat['third_party_user_id'];
            $chArr['nickname']             = $user['nickname'] ?? '';
            $chArr['avatar']               = $user['avatar'] ?? '';
            $chArr['role_name']            = $user['role_name'] ?? '2';
            if (!empty($chat['image_urls'])) {
                $chArr['data']['image_urls'] = $chat['image_urls'];
            }

            $chArr['msg_id']     = $chat['msg_id'];
            $chArr['context']    = $chat['context'];
            $chArr['channel_id'] = $roomInfo['channel_id'];
            $hour                = date('H', strtotime($chat['date_time']));
            if ($hourMark != $hour) {
                $hourMark          = $hour;
                $chArr['showTime'] = $this->formatDateForChat(strtotime($chat['date_time']));
            } else {
                $chArr['showTime'] = '';
            }
            array_push($chatArr, $chArr);
        }

        $data               = [];
        $data['curr_page']  = $chatList['page_num'];
        $data['total_page'] = $chatList['page_all'];
        $data['list']       = $chatArr;
        return $data;
    }

    public function formatDateForChat($timestamp)
    {
        $date          = date('Y-m-d', $timestamp);
        $nowDate       = date('Y-m-d');
        $yesterdayDate = date('Y-m-d', strtotime('-1 day'));
        $weekDate      = date('Y-m-d', strtotime('-1 week'));
        $weekArr       = ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'];
        if ($date == $nowDate) {
            $hour = date('H', $timestamp);
            if ($hour >= 0 && $hour < 6) {
                $mm = '凌晨';
            } elseif ($hour >= 6 && $hour < 12) {
                $mm = '早上';
            } elseif ($hour >= 12 && $hour < 18) {
                $mm = '下午';
            } elseif ($hour >= 18 && $hour < 24) {
                $mm = '晚上';
            } else {
                $mm = '';
            }
            $dateDiff = $mm . ' ' . date('H', $timestamp);
        } elseif ($date == $yesterdayDate) {
            $dateDiff = '昨天 ' . date('H', $timestamp);
        } elseif ($date < $yesterdayDate && $date > $weekDate) {
            $week     = date('w', $timestamp);
            $dateDiff = $weekArr[$week] . ' ' . date('H', $timestamp);
        } else {
            $dateDiff = date('Y-m-d H', $timestamp);
        }

        return $dateDiff . ':00';
    }

    public function customSend($params)
    {
        vss_validator($params, [
            'channel_id' => 'required',
            'body'       => 'required',
            'client'     => 'required|in:' . ChatConstant::CLIENTS
        ]);
        return vss_service()->getPaasChannelService()->sendMessageByChannel(
            $params['channel_id'],
            $params['body'],
            $params['third_party_user_id'],
            'service_custom',
            $params['client']
        );
    }

    /**
     * 公告列表
     *
     * @param $params
     *
     * @return array
     * @throws Exception
     */
    public function noticeLists($params)
    {
        vss_validator($params, [
            'room_id'   => 'required',
            'curr_page' => 'filled',
            'page_size' => 'filled',
        ]);
        $currPage = $params['curr_page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;
        $orderBy  = $params['order_by'] ?? 'desc';
        $whereArr = [
            'room_id' => $params['room_id'],
            'type'    => 0,
        ];
        if (isset($params['start_time'])) {
            $whereArr['created_at'] = $params['start_time'];
        }
        $notices           = vss_model()->getNoticeModel()->where($whereArr);
        $allCount          = $notices->count();
        $arr['total']      = $allCount;
        $arr['total_page'] = ceil($allCount / $pageSize);
        $arr['curr_page']  = $currPage;
        $offset            = ($currPage == 1) ? 0 : $pageSize * ($currPage - 1);
        $lists             = $notices->offset($offset)
            ->limit($pageSize)
            ->orderBy('created_at', $orderBy)
            ->get()->toArray();
        $arr['lists']      = $lists;
        return $arr;
    }

    /**
     * 获取待审核消息列表
     *
     * @param $params
     *
     * @return mixed
     * @throws Exception
     */
    public function auditLists($params)
    {
        vss_validator($params, [
            'room_id'             => 'required',
            'channel_id'          => 'required',
            'third_party_user_id' => 'required',
            'client'              => 'required|in:' . ChatConstant::CLIENTS,
        ]);
        $auditList = vss_service()->getPaasChannelService()->auditLists($params);
        if (empty($auditList)) {
            return $auditList;
        }

        //添加消息发送人禁言和踢出状态
        $senderIdArr = [];
        foreach ($auditList as $value) {
            $value                            = json_decode($value, true);
            $senderIdArr[$value['sender_id']] = $value['sender_id'];
        }
        $list = vss_model()->getRoomJoinsModel()->listByRoomIdAccountIds(
            $params['room_id'],
            $senderIdArr,
            ['account_id', 'is_banned', 'is_kicked'],
            'account_id'
        );

        foreach ($auditList as $key => $value) {
            $value              = json_decode($value, true);
            $value['is_banned'] = (string)$list[$value['sender_id']]['is_banned'] ?? '0';
            $value['is_kicked'] = (string)$list[$value['sender_id']]['is_kicked'] ?? '0';
            $auditList[$key]    = json_encode($value);
        }
        return $auditList;
    }

    /**
     * 获取历史消息数据
     *
     * @param $params
     *
     * @return mixed
     */
    public function messageLists($params)
    {
        vss_validator($params, [
            'channel_id'          => 'required',
            'third_party_user_id' => 'required',
            'client'              => 'required|in:' . ChatConstant::CLIENTS,
            'start_time'          => 'required',
            'msg_type'            => 'filled',
            'curr_page'           => 'filled',
            'page_size'           => 'filled',
        ]);
        $params['curr_page'] = $params['curr_page'] ?? 1;
        $params['page_size'] = $params['page_size'] ?? 200;
        $params['msg_type']  = $params['msg_type'] ?? 'all';
        return vss_service()->getPaasChannelService()->getMessageLists($params);
    }

    /**
     * 设置审核开关接口
     *
     * @param $params
     *
     * @return mixed
     */
    public function setChannelSwitch($params)
    {
        vss_validator($params, [
            'channel_id'          => 'required',
            'third_party_user_id' => 'required',
            'client'              => 'required|in:' . ChatConstant::CLIENTS,
            'switch'              => 'required|in:1,2',
        ]);
        return vss_service()->getPaasChannelService()->setChannelSwitch($params);
    }

    /**
     * 获取审核开关状态接口
     *
     * @param $params
     *
     * @return mixed
     */
    public function getChannelSwitch($params)
    {
        vss_validator($params, [
            'channel_id'          => 'required',
            'third_party_user_id' => 'required',
            'client'              => 'required|in:' . ChatConstant::CLIENTS,
        ]);
        return vss_service()->getPaasChannelService()->getChannelSwitch($params);
    }

    /**
     * 设置是否自动处理聊天数据接口（switch开启能发,不能收,会转到审核频道）
     *
     * @param $params
     *
     * @return mixed
     */
    public function setChannelSwitchOptions($params)
    {
        vss_validator($params, [
            'channel_id'          => 'required',
            'third_party_user_id' => 'required',
            'client'              => 'required|in:' . ChatConstant::CLIENTS,
            'switch'              => 'required|in:1,2',
            'switch_options'      => 'required|in:1,2',
        ]);
        return vss_service()->getPaasChannelService()->setChannelSwitchOptions($params);
    }

    /**
     * 审核消息操作
     *
     * @param $params
     *
     * @return mixed
     */
    public function applyMessageSend($params)
    {
        vss_validator($params, [
            'channel_id'          => 'required',
            'third_party_user_id' => 'required',
            'client'              => 'required|in:' . ChatConstant::CLIENTS,
            'msg_id'              => 'required',
            'status'              => 'required|in:1,2',
        ]);
        return vss_service()->getPaasChannelService()->applyMessageSend($params);
    }

    /**
     * 导出消息配置
     *
     * @param int    $accountId
     * @param string $fileName
     * @param string $beginTime
     * @param string $endTime
     * @param int    $ilId
     * @param int    $filterStatus
     *
     * @return ExportModel
     *
     * @author  jin.yang@vhall.com
     * @date    2020-10-23
     */
    public function exportMessage($ilId, $accountId, $fileName, $beginTime, $endTime, $filterStatus)
    {
        $accountId = $accountId ?: 0;
        $liveInfo  = vss_service()->getRoomService()->getInfoByIlId($ilId);
        if (!$accountId && empty($liveInfo)) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }

        $params = [
            'account_id'    => $accountId,
            'il_id'         => $ilId,
            'start_time'    => $beginTime,
            'end_time'      => $endTime,
            'filter_status' => $filterStatus, // 0 审核通过， 1 审核未通过
        ];

        $insert = [
            'export'     => 'message',
            'il_id'      => $ilId,
            'account_id' => $accountId,
            'file_name'  => $fileName,
            'title'      => ['房间ID', '房间名称', '昵称', '用户id', '时间', '消息内容'],
            'params'     => json_encode($params),
            'callback'   => 'chat:getMessageExportData',
        ];

        return vss_model()->getExportModel()->create($insert);
    }

    /**
     * 导出消息内容
     *
     * @param array  $export
     * @param string $filePath
     *
     * @return bool
     * @author  jin.yang@vhall.com
     * @date    2020-10-22
     */
    public function getMessageExportData($export, $filePath)
    {
        $params = json_decode($export['params'], true);
        $header = json_decode($export['title'], true);
        $file   = $filePath . $export['file_name'];

        if (!$params['account_id'] && !$params['il_id']) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }

        //写入文件
        $exportProxyService = vss_service()->getExportProxyService()->init($file)->putRow($header);

        //直播间列表
        $interactiveLivesList = vss_model()->getRoomsModel()->getListByAccountIdAndIlId(
            $params['account_id'],
            $params['il_id']
        );

        $interactiveLives = $interactiveLivesList[0] ?? [];
        if (!$interactiveLives) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }

        $page          = 1;
        $messageParams = [
            'channel_id'    => $interactiveLives['channel_id'],
            'curr_page'     => $page,
            'page_size'     => $params['page_size'] ?? 1000,
            'start_time'    => $params['start_time'],
            'end_time'      => $params['end_time'],
            'audit_status'  => 'all',
            'msg_type'      => 'text,image',
            'filter_status' => $params['filter_status'],  // 0 查询未被阻止的内容， 1 查询被阻止的内容
            'order_by'      => 'asc'
        ];

        while (true) {
            $messageParams['curr_page'] = $page++;
            $result                     = vss_service()->getPaasChannelService()->getMessageLists($messageParams);
            $exportData                 = [];

            if (isset($result['list'])) {
                foreach ($result['list'] as $item) {
                    $message = $this->formatMessage($item);

                    if (isset($item['image_urls']) && $item['image_urls']) {
                        $message .= implode(' ', $item['image_urls']);
                    }

                    $exportData[] = [
                        'il_id'      => $interactiveLives['il_id'] ?? '-',
                        'il_name'    => $interactiveLives['name'] ?? '-',
                        'nick_name'  => $item['nick_name'] ?? '-',
                        'account_id' => $item['third_party_user_id'] ?? '-',
                        'date_time'  => $item['date_time'] ?? '-',
                        'data'       => $message,
                    ];
                }
            }

            if (!$exportData) {
                break;
            }
            $exportProxyService->putRows($exportData);
        }

        $exportProxyService->close();

        return true;
    }

    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/3/9
     *
     * @param $params
     *
     * @return array|mixed
     * @throws Exception
     */
    public function chatList($params)
    {
        vss_validator($params, [
            'il_id'         => 'required',
            'page'          => 'filled',
            'page_size'     => 'filled',
            'order_by'      => '',
            'type'          => '',
            'begin_time'    => '',
            'end_time'      => '',
            'filter_status' => '',
        ]);
        $ilId         = $params['il_id'];
        $page         = $params['page'] ?? 1;
        $pageSize     = $params['page_size'] ?? 20;
        $orderBy      = $params['order_by'] ?? 'asc';  // 排序方式，asc按照时间升序，desc按照时间倒序，默认desc
        $type         = $params['type'] ?? 'text,image'; // 要查询消息类型，all 为所有
        $startDate    = $params['begin_time'];
        $endDate      = $params['end_time'];
        $filterStatus = $params['filter_status'] ?? 0;  // 过滤类型 , 0 可查看（ 默认），1 不可查看
        $roomInfo     = vss_model()->getRoomsModel()->getInfoByIlId($ilId);//获取活动信息
        if (empty($roomInfo)) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }

        $request = [
            'channel_id'    => $roomInfo['channel_id'],
            'curr_page'     => $page,
            'page_size'     => $pageSize,
            'msg_type'      => $type,
            'order_by'      => $orderBy,
            'filter_status' => $filterStatus,
            'audit_status'  => 'all',
            'start_time'    => $startDate ?: (string)$roomInfo['created_at'],
        ];

        if ($endDate) {
            $request['end_time'] = $endDate;
        }

        return vss_service()->getPaasChannelService()->getMessageLists($request);
    }

    /**
     * @auther yaming.feng@vhall.com
     * @date 2021/3/15
     *
     * @param array $item paas 接口查询出的数据项
     *
     * @return string
     */
    public function formatMessage($item)
    {
        $message = $item['data'];
        if (strpos($message, '***') === 0) {
            $message = '@' . substr($message, 3);
        } elseif ($item['context']['replyMsg'] ?? false) {
            $message = '回复' . $item['context']['replyMsg']['nickName'] . ' ' . $message;
        }
        return $message;
    }
}
