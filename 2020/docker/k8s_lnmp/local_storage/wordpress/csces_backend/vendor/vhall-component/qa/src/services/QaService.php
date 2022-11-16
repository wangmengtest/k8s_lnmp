<?php


namespace vhallComponent\qa\services;

use App\Constants\ResponseCode;
use Illuminate\Support\Arr;
use vhallComponent\qa\constants\QaCachePrefixConstant;
use vhallComponent\qa\constants\QaConstant;
use vhallComponent\room\constants\CachePrefixConstant;
use Vss\Common\Services\WebBaseService;
use vhallComponent\export\models\ExportModel;

class QaService extends WebBaseService
{
    /**
     * 创建
     * http://wiki.vhallops.com/pages/viewpage.action?pageId=78446888
     * @param $params
     * @return mixed
     *
     */
    public function create($params)
    {
        vss_validator($params, [
            'app_id'  => 'required',
            'content' => 'required',
            'room_id' => 'required',
            'is_show' => '',
        ]);
        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $ext       = json_encode(['role_name' => $join_user->role_name]);
        $postData  = [
            'app_id'     => $params['app_id'],
            'content'    => $params['content'],
            'user_id'    => $join_user->account_id,
            'nick_name'  => $join_user->nickname,
            'avatar'     => $join_user->avatar,
            'ext'        => $ext,
            'webinar_id' => $params['room_id'],

            'is_show' => $params['is_show'] ?? 0,
        ];
        $res       = vss_service()->getPublicForwardService()->questionCreate($postData);

        vss_service()->getPaasChannelService()->sendMessage($params['room_id'], [
            'type'    => 'question_answer_create',
            'content' => [
                'id'           => $res['id'],
                'content'      => $params['content'],
                'room_join_id' => $join_user->account_id,
                'nick_name'    => $join_user->nickname,
                'avatar'       => $join_user->avatar,
                'role_name'    => $join_user->role_name,
                'is_show'      => $params['is_show'] ?? 0,
                'created_at'   => date('Y-m-d H:i:s')
            ]
        ]);
        return $res;
    }

    /**
     * 问题 公开/私密 状态设置
     * @param $params
     *
     */
    public function show($params)
    {
        vss_validator($params, [
            'app_id'  => 'required',
            'room_id' => 'required',
            'ques_id' => 'required',
            'is_show' => '',
        ]);

        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        if ($join_user->role_name == 2) {
            $this->fail(ResponseCode::BUSINESS_INVALID_USER);
        }

        $postData = [
            'app_id'     => $params['app_id'],
            'id'         => $params['ques_id'],
            'webinar_id' => $params['room_id'],
            'is_show'    => $params['is_show'] ?? 0,
        ];
        $res      = vss_service()->getPublicForwardService()->questionPublish($postData);
        $postData = [
            'app_id'     => $params['app_id'],
            'id'         => $params['ques_id'],
            'webinar_id' => $params['room_id'],
        ];
        $qaData   = vss_service()->getPublicForwardService()->questionGet($postData);
        $qaData['ext'] && $ext = json_decode($qaData['ext'], true);
        $qaData['role_name'] = $ext['role_name'] ? $ext['role_name'] : '';
        $qaData['answers'] && array_walk($qaData['answers'], function (&$value) {
            $value['ext'] && $ext = json_decode($value['ext'], true);
            $value['role_name'] = $ext['role_name'] ? $ext['role_name'] : '';
        });
        vss_logger()->info('question_show_status', $qaData);
        vss_service()->getPaasChannelService()->sendMessage($params['room_id'], [
            'type'    => 'question_show_status',
            'content' => $qaData
        ]);

    }

    /**
     *  回答
     * http://wiki.vhallops.com/pages/viewpage.action?pageId=78446967
     * @param $params
     * @return mixed
     *
     */
    public function answer($params)
    {
        vss_validator($params, [
            'app_id'  => 'required',
            'room_id' => 'required',
            'ques_id' => 'required',
            'type'    => '',
            'is_open' => '',
        ]);
        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $ext       = json_encode(['role_name' => $join_user->role_name]);
        $postData  = [
            'app_id'     => $params['app_id'],
            'content'    => $params['content'],
            'join_id'    => $join_user->account_id,
            'webinar_id' => $params['room_id'],
            'nick_name'  => $join_user->nickname,
            'role_name'  => $join_user->role_name,
            'avatar'     => $join_user->avatar,
            'ext'        => $ext,
            'is_open'    => $params['is_open'] ?? 1,
            'type'       => $params['type'] ?? 0,
            'ques_id'    => $params['ques_id']
        ];
        $res       = vss_service()->getPublicForwardService()->answerCreate($postData);
        vss_service()->getPaasChannelService()->sendMessage($params['room_id'], [
            'type'    => 'question_answer_submit',
            'content' => [
                'id'           => $res['id'],
                'content'      => $params['content'],
                'room_join_id' => $join_user->account_id,
                'nick_name'    => $join_user->nickname,
                'avatar'       => $join_user->avatar,
                'role_name'    => $join_user->role_name,
                'is_open'      => $params['is_open'] ?? 1,
                'type'         => $params['type'] ?? 0,
                'ques_id'      => $params['ques_id'],
                'created_at'   => date('Y-m-d H:i:s')
            ]
        ]);
        return $res;
    }

    /**
     * 处理问答
     * http://wiki.vhallops.com/pages/viewpage.action?pageId=78446961
     * @param $params
     * @return mixed
     *
     */
    public function deal($params)
    {
        vss_validator($params, [
            'app_id'  => 'required',
            'room_id' => 'required',
            'id'      => 'required',
            'status'  => '',
        ]);
        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $postData  = [
            'app_id'     => $params['app_id'],
            'webinar_id' => $params['room_id'],
            'operator'   => $join_user->nickname,
            'status'     => $params['status'] ?? 0,
            'id'         => $params['id'],
        ];
        vss_service()->getPaasChannelService()->sendMessage($params['room_id'], [
            'type'    => 'question_answer_deal',
            'content' => [
                'operator'     => $join_user->nickname,
                'status'       => $params['status'] ?? 0,
                'id'           => $params['id'],
                'room_join_id' => $join_user->account_id,
                'avatar'       => $join_user->avatar,
                'role_name'    => $join_user->role_name,
                'created_at'   => date('Y-m-d H:i:s')
            ]

        ]);
        return vss_service()->getPublicForwardService()->questionDeal($postData);
    }

    /**
     * 处理回复
     * http://wiki.vhallops.com/pages/viewpage.action?pageId=78446981
     * @param $params
     * @return mixed
     *
     */
    public function dealAnswer($params)
    {
        vss_validator($params, [
            'app_id'     => 'required',
            'room_id'    => 'required',
            'id'         => 'required',
            'is_backout' => '',
        ]);
        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $postData  = [
            'app_id'     => $params['app_id'],
            'webinar_id' => $params['room_id'],
            'operator'   => $join_user->nickname,
            'is_backout' => $params['is_backout'] ?? 0,
            'id'         => $params['id']

        ];

        $quesInfo = vss_service()->getPublicForwardService()->answerDeal($postData);

        vss_service()->getPaasChannelService()->sendMessage($params['room_id'], [
            'type'    => 'question_answer_deal_answer',
            'content' => [
                'operator'   => $join_user->nickname,
                'is_backout' => $params['is_backout'] ?? 0,
                'id'         => $params['id'],
                'ques_id'    => $quesInfo['ques_id']
            ]

        ]);
        return $quesInfo;
    }

    /**
     * 列表
     * http://wiki.vhallops.com/pages/viewpage.action?pageId=78446931
     * @param $params
     * @return mixed
     *
     */
    public function lists($params)
    {
        vss_validator($params, [
            'app_id'     => 'required',
            'room_id'    => 'required',
            'is_show'    => '',
            'role'       => '',
            'start_time' => '',
            'end_time'   => '',
            'last_qid'   => '', // 滚动分页 v2 的参数
            'curr_page'  => '', // 当前页， v1 的参数
            'page_size'  => '',
            'sort'       => 'in:asc,desc',   // v2 参数
            'version'    => 'in:v1,v2'       // 版本兼容参数
        ]);
        $lastQid   = $params['last_qid'] ?? 0;
        $currPage  = $params['curr_page'] ?? 0;
        $pageSize  = $params['page_size'] ?? 20;
        $startTime = $params['start_time'] ?? '2019-12-29 00:00:00';
        $endTime   = $params['end_time'];
        $sort      = $params['sort'] ?? 'desc'; // asc 大于 last_qid, desc 小于 last_qid
        $postData  = [
            'app_id'     => $params['app_id'],
            'webinar_id' => $params['room_id'],
            'start_time' => $startTime,
            'limit'      => $pageSize, // v2 参数
            'page_size'  => $pageSize, // v1 参数
            'sort'       => $sort,
            // 使用向共享服务接口的版本， app 端使用的 v1 版本， 主持端使用的 v2 版本
            'version'    => $params['version'] ?? 'v1',
        ];
        $currPage && $postData['curr_page'] = $currPage;
        $lastQid && $postData['last_qid'] = $lastQid;
        $endTime && $postData['end_time'] = $endTime;
        //观众 显示所有已发布及该用户下所有未发布已发布问答
        $account_id = vss_service()->getTokenService()->getAccountId();
        if ($params['role'] == 2) {
            $postData['show_type'] = $account_id;
            vss_logger()->info('qa_list_role', ['data' => $postData]);
        }
        if (isset($params['status'])) {
            $postData['status'] = $params['status'];
        }
        if (isset($params['is_show'])) {
            $postData['is_show'] = $params['is_show'];
        }

        $data = vss_service()->getPublicForwardService()->questionLists($postData);

        // 获取用户同问问题
        $alsoAskQuestionIds = vss_service()->getPublicForwardService()->userAlsoAskQuestion([
            'app_id'     => $params['app_id'],
            'webinar_id' => $params['room_id'],
            'user_id'    => $account_id,
        ]);

        foreach ($data['lists'] as &$item) {
            // 当前用户对问题是否发起同问
            $item['is_same'] = intval(in_array($item['id'], $alsoAskQuestionIds));
        }

        return $data;
    }

    /**
     * 设置问答状态
     *
     * @param $room_id
     * @param $status
     *
     */
    public function setQa($room_id, $status)
    {
        vss_redis()->hset(
            CachePrefixConstant::INTERACT_TOOL . $room_id,
            QaCachePrefixConstant::QA_OPEN,
            (int)$status
        );
        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($room_id);
        $nickname  = empty($join_user->nickname) ? '主办方' : $join_user->nickname;
        if ($status) {
            vss_service()->getPaasChannelService()->sendMessage($room_id, [
                'type'      => 'question_answer_open', //开启问答
                'nick_name' => $nickname

            ]);
        } else {
            vss_service()->getPaasChannelService()->sendMessage($room_id, [
                'type'      => 'question_answer_close', //关闭问答
                'nick_name' => $nickname
            ]);
        }
    }

    /**
     * 获取问答状态
     *
     * @param $room_id
     *
     * @return int
     */
    public function getQaStatus($room_id)
    {
        return (int)vss_redis()->hget(
            CachePrefixConstant::INTERACT_TOOL . $room_id,
            QaCachePrefixConstant::QA_OPEN
        );
    }

    /**
     * 房间问题数统计
     * @auther yaming.feng@vhall.com
     * @date 2020/12/25
     * @param int $ilId
     * @param string $startTime
     * @param string $endTime
     * @return array
     *
     */
    public function questionStat($ilId, $startTime, $endTime)
    {
        $roomInfo = vss_service()->getRoomService()->getInfoByIlId($ilId);
        if (!$roomInfo) {
            return [
                'total' => 0,
            ];
        }

        $startTime = date('Y-m-d', strtotime($startTime)) . ' 00:00:00';
        $endTime   = date('Y-m-d', strtotime($endTime)) . ' 23:59:59';

        $params = [
            'app_id'     => vss_service()->getTokenService()->getAppId(),
            'room_id'    => $roomInfo['room_id'],
            'start_time' => $startTime,
            'end_time'   => $endTime,
            'curr_page'  => 1,
            'page_size'  => 1,
        ];

        $res = $this->lists($params);
        return [
            'total' => Arr::get($res, 'total', 0),
        ];
    }

    /**
     *
     * 创建导出记录
     *
     * @auther yaming.feng@vhall.com
     * @date 2020/12/28
     * @param string $fileName
     * @param string $appId
     * @param int $ilId
     * @param string $startTime
     * @param string $endTime
     * @return ExportModel
     *
     */
    public function createExport($fileName, $appId, $ilId, $accountId, $startTime, $endTime)
    {
        $liveInfo = vss_service()->getRoomService()->getInfoByIlId($ilId);
        if (empty($liveInfo)) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }

        $startTime = date('Y-m-d', strtotime($startTime)) . ' 00:00:00';
        $endTime   = date('Y-m-d', strtotime($endTime)) . ' 23:59:59';

        $params = [
            'app_id'     => $appId,
            'room_id'    => $liveInfo['room_id'],
            'start_time' => $startTime,
            'end_time'   => $endTime,
        ];

        $insert = [
            'export'     => QaConstant::EXPORT_QA,
            'il_id'      => $ilId,
            'account_id' => $accountId,
            'source_id'  => '',
            'file_name'  => $fileName,
            'title'      => ['类型', '账号', '昵称', '角色', '问答内容', '私密', '状态', '同问人数', '更新时间'],
            'params'     => json_encode($params),
            'callback'   => 'qa:getQaExportData'
        ];

        return vss_model()->getExportModel()->create($insert);
    }

    /**
     * @auther yaming.feng@vhall.com
     * @date 2020/12/29
     * @param $export
     * @param $filePath
     * @return bool
     */
    public function getQaExportData($export, $filePath)
    {
        $params = json_decode($export['params'], true);
        $header = json_decode($export['title'], true);
        $file   = $filePath . $export['file_name'];

        // 根据 id 做分页查询
        $lastQid             = 0;
        $exportData          = [];
        $params['page_size'] = 1000;
        $params['sort']      = 'desc';
        $params['version']   = 'v2';

        $exportProxyService = vss_service()->getExportProxyService()->init($file)->putRow($header);
        while (true) {
            $params['last_qid'] = $lastQid;

            $data = $this->lists($params);
            if ($data['lists']) {
                $userMap = $this->getAccountMap($data['lists'], $params['room_id']);
                foreach ($data['lists'] as $row) {
                    $row['status']    = Arr::get(QaConstant::QUESTION_STATUS_MAP, $row['status'], '-');
                    $row['user_name'] = Arr::get($userMap[$row['user_id']] ?? [], 'username');
                    $this->addExportDataRow($exportData, '问题', $row);
                    foreach ($row['answers'] as $answer) {
                        // 撤回的回复不显示
                        if ($answer['is_backout'] != 0) {
                            continue;
                        }
                        //6、问答
                        $answer['status']    = '-';
                        $answer['user_name'] = Arr::get($userMap[$answer['join_id']] ?? [], 'username');
                        $answer['is_open']   = !boolval($answer['is_open']); // 回复里面的取值和问题里的取值正好相反
                        $this->addExportDataRow($exportData, '文字回复', $answer);
                    }
                }

                $exportProxyService->putRows($exportData);
                $exportData = [];

                $lastQid = end($data['lists'])['id'];
            } else {
                break;
            }
        }

        $exportProxyService->close();

        //修改导出表状态
        vss_model()->getExportModel()->getInstance()->where('id', $export['id'])->update(['status' => 3]);

        return true;
    }

    /**
     * 问答组件同问功能开关
     *
     * @auther yaming.feng@vhall.com
     * @date 2020/12/25
     * @param $roomId
     * @param $status
     *
     */
    public function switchQaAlsoAsk($roomId, $status)
    {
        vss_redis()->hset(
            CachePrefixConstant::INTERACT_TOOL . $roomId,
            QaCachePrefixConstant::QA_ALSO_ASK_OPEN,
            (int)$status
        );

        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($roomId);
        $nickname  = empty($join_user->nickname) ? '主办方' : $join_user->nickname;
        if ($status) {
            vss_service()->getPaasChannelService()->sendMessage($roomId, [
                'type'      => 'qa_also_ask_open', //开启问答组件同问功能
                'nick_name' => $nickname

            ]);
        } else {
            vss_service()->getPaasChannelService()->sendMessage($roomId, [
                'type'      => 'qa_also_ask_close', //关闭问答组件同问功能
                'nick_name' => $nickname
            ]);
        }
    }

    /**
     * 获取问答组件同问功能开关状态
     *
     * @auther yaming.feng@vhall.com
     * @date 2020/12/25
     * @param $roomId
     * @return int
     */
    public function getQaAlsoAskStatus($roomId)
    {
        return (int)vss_redis()->hget(
            CachePrefixConstant::INTERACT_TOOL . $roomId,
            QaCachePrefixConstant::QA_ALSO_ASK_OPEN
        );
    }

    /**
     * 客户端同问/取消同问
     * @auther yaming.feng@vhall.com
     * @date 2020/12/25
     *
     */
    public function toggleAlsoAsk($params)
    {
        vss_validator($params, [
            'app_id'  => 'required',
            'room_id' => 'required',
            'ques_id' => 'required',
            'type'    => 'required', // 是否同问， 0是， 1取消
        ]);

        $roomId   = $params['room_id'];
        $joinUser = vss_service()->getTokenService()->getCurrentJoinUser($roomId);

        $postData = [
            'app_id'     => $params['app_id'],
            'id'         => $params['ques_id'],
            'type'       => $params['type'],
            'webinar_id' => $roomId,
            'user_id'    => $joinUser->account_id
        ];

        $res = vss_service()->getPublicForwardService()->questionAlsoAsk($postData);
        vss_service()->getPaasChannelService()->sendMessage($roomId, [
            'type'    => 'qa_also_ask_count',
            'content' => [
                'id'         => $res['id'],                               // 问题ID
                'user_id'    => $postData['user_id'],                     // 用户 id
                'type'       => $postData['type'],                        // 是否同问 0是 1 取消
                'content'    => $res['content'],
                'is_open'    => Arr::get($res, 'is_show', 1),    // 是否空开 0公开 1私密
                'same_count' => Arr::get($res, 'same_count', 0), // 同问数
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
        return $res;
    }

    /**
     * @auther yaming.feng@vhall.com
     * @date 2020/12/28
     * @param array $exportData 要导出的数据
     * @param string $type 数据类型
     * @param array $row
     */
    public function addExportDataRow(&$exportData, $type, $row)
    {
        $ext = json_decode($row['ext'], true);
        // 用户账号既不是手机号也不是邮箱， 则该账号为游客账号
        if (strlen($row['user_name']) != 11 && is_numeric($row['user_name'])) {
            $row['user_name'] = '游客';
        }

        //设置Excel行数据
        $exportData[] = [
            $type, $row['user_name'], $row['nick_name'], QaConstant::ROLE_NAME_MAP[$ext['role_name']],
            $row['content'], $row['is_open'] ? '私密' : '公开',
            $row['status'], Arr::get($row, 'same_count', '-'),
            $row['created_at']
        ];
    }

    /**
     * @auther yaming.feng@vhall.com
     * @date 2020/12/30
     * @param $qaList
     * @param $roomId
     * @return array
     */
    public function getAccountMap($qaList, $roomId)
    {
        $userIds = [];
        foreach ($qaList as $item) {
            $userIds[] = $item['user_id'];
            foreach ($item['answers'] as $answer) {
                $userIds[] = $answer['join_id'];
            }
        }

        $userMap = [];
        $userIds = array_unique($userIds);
        if ($userIds) {
            $userMap = vss_model()->getRoomJoinsModel()->listByRoomIdAccountIds(
                $roomId,
                $userIds,
                ['account_id', 'username'],
                'account_id'
            );
        }

        return $userMap;
    }
}
