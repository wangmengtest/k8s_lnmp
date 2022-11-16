<?php

namespace vhallComponent\paas\services;

use Exception;
use Vss\Exceptions\PaasException;
use Vss\Utils\HttpUtil;
use GuzzleHttp\Client;
use function GuzzleHttp\Promise\unwrap;
use GuzzleHttp\RequestOptions;
use Vss\Common\Services\WebBaseService;

class PaasService extends WebBaseService
{
    /**
     * PAAS服务接口地址
     *
     * @var array|null|string
     */
    private $host = '';

    /**
     * PAAS服务应用ID
     *
     * @var array|null|string
     */
    private $appId = '';

    /**
     * PAAS服务应用秘钥
     *
     * @var array|null|string
     */
    private $secretKey = '';

    /**
     * PaasServiceImpl constructor.
     */
    public function __construct()
    {
        $this->host      = vss_config('paas.host');
        $this->appId     = $_REQUEST['app_id'] ?? vss_service()->getTokenService()->getAppId();
        $this->secretKey = vss_paas_util()->getPaasAppSecretByAppId($this->appId);
    }

    /**
     * 发送PAAS请求
     *
     * @param string $uri
     * @param array  $params
     *
     * @return mixed
     */
    public function request(string $uri, array $params = [])
    {
        $params   = vss_paas_util()->generateParams($params, $this->appId, $this->secretKey);
        $response = HttpUtil::post($this->host . $uri, $params, null, 20);
        if ($response->getCode() != 200) {
            vss_logger()->error(
                '[paas service] response error',
                ['url' => $uri, 'code' => $response->getCode(), 'msg' => $response->getMessage()]
            );
            throw new PaasException($response->getCode(), $response->getMessage());
        }
        $data = $response->getData();
        if ($data['code'] != 200) {
            vss_logger()->error(
                '[paas service] response data error',
                ['url' => $uri, 'data' => $data, 'params' => $params]
            );
            throw new PaasException($data['code'], $data['msg']);
        }

        return $data['data'];
    }

    /**
     *
     * @param      $url
     * @param null $data
     * @param int  $timeOut
     *
     * @return bool|string
     *
     */
    private function CurlRequest($url, $data = null, $timeOut = 90)
    {
        $url  = $this->host . $url;
        $data = vss_paas_util()->generateParams($data, $this->appId, $this->secretKey);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeOut);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $time       = microtime(true);
        $resultData = curl_exec($curl);

        if (($time = microtime(true) - $time) > 1) {
            vss_logger()->error("[paas service] Slow curl \"POST {$url}\".", ['time' => number_format($time, 2)]);
        }
        if ($errno = curl_errno($curl)) {
            $err = curl_error($curl);
            vss_logger()->error(
                "[paas service] Curl failed \"POST {$url}\", " . $err,
                ['time' => number_format($time, 2)]
            );
            curl_close($curl);
            throw new PaasException($errno, $err);
        }
        curl_close($curl);
        $result = json_decode($resultData, true);
        if ($result['code'] == 200) {
            return $result['data'];
        }

        throw new PaasException($result['code'], $result['msg']);
    }

    /**
     * 创建房间
     *
     * @return mixed
     * @throws \Throwable
     */
    public function createRoom()
    {
        $client   = new Client(['base_uri' => $this->host]);
        $param    = [
            RequestOptions::FORM_PARAMS => vss_paas_util()->generateParams([], $this->appId, $this->secretKey),
        ];
        $promises = [
            'live_room'         => $client->postAsync('/api/v2/room/create', $param),
            'hd_room'           => $client->postAsync('/api/v2/inav/create', $param),
            'channel_room'      => $client->postAsync('/api/v2/channel/create', $param),
            'nify_channel_room' => $client->postAsync('/api/v2/channel/create', $param),
        ];
        $results  = unwrap($promises);

        foreach ($results as & $result) {
            $result = json_decode($result->getBody()->getContents(), true);
            $result = !empty($result['data']) ? $result['data'] : false;
        }

        $liveRoom        = $results['live_room']['room_id'] ?? false;
        $hdRoom          = $results['hd_room']['inav_id'] ?? false;
        $channelRoom     = $results['channel_room']['channel_id'] ?? false;
        $nifyChannelRoom = $results['nify_channel_room']['channel_id'] ?? false;
        // 其中一个请求失败，则返回错误提示
        if (!$liveRoom || !$hdRoom || !$channelRoom || !$nifyChannelRoom) {
            throw new PaasException();
        }

        return [
            'live_room'         => $liveRoom,
            'hd_room'           => $hdRoom,
            'channel_room'      => $channelRoom,
            'nify_channel_room' => $nifyChannelRoom,
        ];
    }

    //基础API****************************************************************//

    /**
     * 生成 access_token
     *
     * @param array $params
     *
     * @return mixed
     */
    public function baseCreateAccessToken(array $params)
    {
        $result = $this->request('/api/v2/base/create-v2-access-token', $params);

        return $result['access_token'];
    }

    /**
     * 生成paas请求url
     *
     * @param array $params
     * @param       $uri
     *
     * @return string
     * @author  jin.yang@vhall.com
     * @date    2020-10-20
     */
    public function buildPaasRequestUrl(array $params, $uri)
    {
        $params = vss_paas_util()->generateParams($params, $this->appId, $this->secretKey);
        $url    = vss_config('paas.host') . $uri;

        return $url . '?' . http_build_query($params);
    }

    /**
     * 销毁 access_token
     *
     * @param string $accessToken
     *
     * @return mixed
     */
    public function baseDestroyAccessToken(string $accessToken)
    {
        return $this->request('/api/v2/base/destroy-access-token', ['access_token' => $accessToken]);
    }

    //直播API****************************************************************//

    /**
     * 直播API-删除直播房间
     *
     * @param string $roomId
     *
     * @return mixed
     */
    public function roomDelete(string $roomId)
    {
        return $this->request('/api/v2/room/delete', ['room_id' => $roomId]);
    }

    //互动API****************************************************************//

    /**
     * 互动API-删除互动房间
     *
     * @param string $inavId
     *
     * @return mixed
     */
    public function inavDelete(string $inavId)
    {
        return $this->request('/api/v2/inav/delete', ['inav_id' => $inavId]);
    }

    /**
     * 互动API-获取互动房间人员列表
     *
     * @param $inavId
     *
     * @return mixed
     */
    public function inavUserList($inavId)
    {
        return $this->request('/api/v2/inav/inav-user-list', ['inav_id' => $inavId]);
    }

    //消息API****************************************************************//

    /**
     * 消息API-删除频道
     *
     * @param string $channelId
     *
     * @return mixed
     */
    public function channelDelete(string $channelId)
    {
        return $this->request('/api/v2/channel/delete', ['channel_id' => $channelId]);
    }

    /**
     * 获取聊天频道在线用户列表
     *
     * @param $channelId
     * @param $pos
     * @param $limit
     *
     * @return mixed
     */
    public function getUserIdList($channelId, $page, $pagesize)
    {
        $params = [
            'channel_id' => $channelId,
            'curr_page'  => $page,
            'page_size'  => $pagesize
        ];
        return $this->request('/api/v2/channel/get-userid-list', $params);
    }

    /**
     * 获取推流信息
     *
     * @param $roomId
     * @param $expireTime
     *
     * @return mixed
     */
    public function getPushInfo($roomId, $expireTime)
    {
        return $this->request('/api/v2/room/get-push-info', ['room_id' => $roomId, 'expire_time' => $expireTime]);
    }

    /**
     * 获取直播流的流信息
     *
     * @param $roomId
     *
     * @return mixed
     */
    public function getStreamMsg($roomId)
    {
        return $this->request('/api/v2/room/get-stream-msg', ['room_id' => $roomId]);
    }

    /**
     * 文档消息转发
     *
     * @param $channelId
     * @param $targetChannel
     *
     * @return mixed
     */
    public function setTargetChannel($channelId, $targetChannel)
    {
        return $this->request(
            '/api/v2/channel/set-target-channel',
            ['channel_id' => $channelId, 'target_channel' => $targetChannel]
        );
    }

    /**
     * 重置文档消息转发
     *
     * @param $channelId
     *
     * @return mixed
     */
    public function resetTargetChannel($channelId)
    {
        return $this->request('/api/v2/channel/reset-target-channel', ['channel_id' => $channelId]);
    }

    /**
     * 点播列表
     * @see http://www.vhallyun.com/docs/show/481
     *
     * @param $data
     *
     * @return mixed
     */
    public function getRecordList($data)
    {
        $data['action'] = 'GetVodList';

        return $this->request('/api/v2/vod', $data);
    }

    /**
     * 获取点播列表全部信息
     *
     * @param $param
     *
     * @return array
     */
    public function getAllRecordList($param)
    {
        $recordList        = [];
        $page              = 0;
        $data['room_id']   = !empty($param['room_id']) ? $param['room_id'] : '';
        $data['page_num']  = $page;
        $data['page_size'] = 100;
        if (!empty($param['starttime'])) {
            $data['starttime'] = $param['starttime'];
        }
        if (!empty($param['endtime'])) {
            $data['endtime'] = $param['endtime'];
        }
        if (isset($param['source']) && $param['source'] != '') {
            $data['source'] = $param['source'];
        }
        if (!empty($param['status'])) {
            $data['status'] = $param['status'];
        }
        $data['sortby'] = !empty($param['sortby']) ? $param['sortby'] : 'created_at:desc';

        while (true) {
            $data['page_num'] = $page;
            $result           = $this->getRecordList($data);
            $recordList       = array_merge($recordList, $result['list']);
            $page++;
            if ($page >= $result['page_all']) {
                break;
            }
        }
        return $recordList;
    }

    /**
     * 点播重命名
     *
     * @param $data
     *
     * @return mixed
     */
    public function changeName($data)
    {
        $data['action'] = 'UpdateVodInfo';

        return $this->request('/api/v2/vod', $data);
    }

    /**
     * 点播下载
     *
     * @param $data
     *
     * @return mixed
     */
    public function download($data)
    {
        return $this->request(
            '/api/v2/vod',
            [
                'action' => 'SubmitMediaPackageTasks',
                'app_id' => $data['app_id'],
                'vod_id' => $data['vod_id'],
            ]
        );
    }

    /**
     * 点播详情
     *
     * @param $data
     *
     * @return mixed
     */
    public function recordInfo($data)
    {
        $data['action'] = 'GetVodInfo';

        return $this->request('/api/v2/vod', $data);
    }

    /**
     * 点播删除
     *
     * @param $data
     *
     * @return mixed
     */
    public function recordDel($data)
    {
        $data['action'] = 'SubmitDeleteVodTasks';

        return $this->request('/api/v2/vod', $data);
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function downQuality($data)
    {
        $data['action'] = 'SubmitMediaPackageByQualityTasks';

        return $this->request('/api/v2/vod', $data);
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function videoDel($data)
    {
        $data['action'] = 'SubmitDeleteVideoTasks';
        $result         = $this->request('/api/v2/vod', $data);
        if ($result && !empty($result['task_id'])) {
            return $result;
        }

        return [];
    }

    /**
     * Notes: 点播转直播
     * Author: michael
     * Date: 2019/10/12
     * Time: 19:49
     *
     * @param $data
     *
     * @return mixed
     */
    public function dibblingVod($data)
    {
        $data = $this->request('/api/v2/vod', $data);
        if ($data['code'] == 12010) {
            throw new PaasException(12010, '该任务已存在');
        }
        if ($data['code'] == 12004) {
            throw new PaasException(12004, '找不到视频信息');
        }

        return $data;
    }

    /**
     *
     * @param $config_id
     *
     * @return mixed
     * @throws Exception
     */
    public function delStream($config_id)
    {
        $data['config_id'] = $config_id;
        $result            = $this->request('/api/v2/room/delete-live-pull-stream-config', $data);
        if ($result) {
            return $result;
        }
    }

    /**
     * 点播详情
     *
     * @param $data
     *
     * @return mixed
     */
    public function submitCreateRecordTask($data)
    {
        $data['action'] = 'SubmitCreateRecordTasks';

        return $this->request('/api/v2/vod', $data);
    }

    /**
     * 提交剪辑任务
     *
     * @param $data
     *
     * @return mixed
     */
    public function submitVideoEditTasks($data)
    {
        $data['action'] = 'SubmitVideoEditTasks';

        return $this->request('/api/v2/vod', $data);
    }

    public function getFormInfo($questionId)
    {
        return $this->request('/api/v2/form/get', ['id' => $questionId]);
    }

    public function createForm($params)
    {
        return $this->request('/api/v2/form/create', $params);
    }

    /**
     * 获取点播观看数据
     *
     * @see http://www.vhallyun.com/docs/show/1346
     *
     * @param string $record_id
     * @param string $begintime
     * @param string $endtime
     * @param int    $pos
     * @param int    $limit
     *
     * @return bool|mixed
     */
    public function getRecordUseInfo($record_id, $begintime, $endtime, $pos = 0, $limit = 1000, $type = 2)
    {
        $address = '/api/v2/das/get-vod-record-use-info';
        $param   = [
            'record_id'  => $record_id,
            'pos'        => $pos,
            'limit'      => $limit,
            'start_time' => $begintime,
            'end_time'   => $endtime,
            'type'       => $type,
        ];
        return $this->request($address, $param);
    }

    /**
     * 获取点播增量观看数据
     * @see    http://www.vhallyun.com/docs/show/1347
     * @author fym
     * @since  2021/6/16
     *
     * @param string $startTime 格式 Y-m-d H:i:s
     * @param string $endTime
     * @param int    $pos
     * @param int    $limit
     * @param int    $type      1 小时粒度， 2 分钟粒度
     */
    public function getRecordUserInfoBatch($startTime, $endTime, $pos = 0, $limit = 1000, $type = 1)
    {
        $address = '/api/v2/das/get-vod-records-use-info';
        $param   = [
            'pos'        => $pos,
            'limit'      => $limit,
            'start_time' => $startTime,
            'end_time'   => $endTime,
            'type'       => $type,
        ];

        return $this->request($address, $param);
    }

    /**
     * 批量获取房间流状态信息
     *
     * @see http://www.vhallyun.com/docs/show/1506.html
     *
     * @param $roomIds
     *
     * @return mixed
     * @throws Exception
     */
    public function getStreamStatus($roomIds)
    {
        $address = '/api/v2/room/get-stream-status';
        $param   = [
            'room_ids' => $roomIds,
        ];

        return $this->request($address, $param);
    }

    /**
     * 获取点播文件和不同清晰度属性信息 无v2接口
     *
     * @param $record_id
     *
     * @return mixed
     */
    public function getRecordInfo($record_id)
    {
        $address = '/api/v1/record/get-record-info';
        $param   = [
            'record_id' => $record_id,
        ];
        $result  = $this->request($address, $param);

        return $result;
    }

    /**
     * 获取互动流量数据
     *
     * @see http://www.vhallyun.com/docs/show/1339
     *
     * @param        $inavId
     * @param        $starttime
     * @param string $endtime
     * @param int    $pos
     * @param int    $limit
     *
     * @return bool|mixed
     */
    public function getInavRoomData($inavId, $starttime, $endtime, $pos, $limit = 1000)
    {
        $address = '/api/v2/das/get-inav-room-data';
        $param   = [
            'inav_id'    => $inavId,
            'pos'        => $pos,
            'limit'      => $limit,
            'start_time' => $starttime,
            'end_time'   => $endtime,
        ];

        try {
            return $this->request($address, $param);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 获取直播属性信息
     *
     * @see http://www.vhallyun.com/docs/show/1354
     *
     * @param string $roomId
     * @param string $starttime
     * @param string $endtime
     * @param int    $pos
     * @param int    $limit
     *
     * @return bool|mixed
     */
    public function getRoomUseInfo($roomId, $starttime, $endtime, $pos, $limit = 1000)
    {
        $address = '/api/v2/das/get-lives-room-use-info';
        $param   = [
            'room_id'    => $roomId,
            'pos'        => $pos,
            'limit'      => $limit,
            'start_time' => $starttime,
            'end_time'   => $endtime,
        ];

        try {
            return $this->request($address, $param);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 获取直播访问记录
     *
     * @see http://www.vhallyun.com/docs/show/1355
     *
     * @param string $roomId
     * @param string $starttime
     * @param string $endtime
     * @param int    $pos
     * @param int    $limit
     *
     * @return bool|mixed
     */
    public function getRoomJoinInfo($roomId, $starttime, $endtime, $pos = 0, $limit = 1000)
    {
        $address = '/api/v2/das/get-lives-room-join-info';
        $param   = [
            'room_id'    => $roomId,
            'pos'        => $pos,
            'limit'      => $limit,
            'start_time' => $starttime,
            'end_time'   => $endtime,
        ];

        try {
            $result = $this->request($address, $param);
        } catch (\Exception $e) {
            $result = ['code' => $e->getCode(), 'message' => $e->getMessage()];
        }

        return $result;
    }

    /**
     * 获取直播访问记录增量接口
     *
     * @see    http://www.vhallyun.com/docs/show/1356
     *
     * @param string $startTime
     * @param string $endTime
     * @param int    $pos
     * @param int    $limit
     * @param array  $roomIds 房间ID，最大100个, 可以为空， 为空则根据 APPID 查询所有的符合条件的数据
     *
     * @since  2021/6/15
     * @author fym
     */
    public function getRoomJoinInfoBatch($startTime, $endTime, $pos = 0, $limit = 1000, $roomIds = [])
    {
        $address = '/api/v2/das/get-lives-room-join-info-batch';
        $param   = [
            'pos'        => $pos,
            'limit'      => $limit,
            'start_time' => $startTime,
            'end_time'   => $endTime,
        ];

        if ($roomIds) {
            $param['room_ids'] = implode(',', $roomIds);
        }

        try {
            $result = $this->request($address, $param);
        } catch (\Exception $e) {
            $result = [];
        }

        return $result;
    }

    /**
     *
     * 获取房间访问记录
     *
     * @see http://www.vhallyun.com/docs/show/1341
     *
     * @param     $inavId
     * @param     $starttime
     * @param     $endtime
     * @param int $pos
     * @param int $limit
     *
     * @return mixed
     */
    public function getInavAccessData($inavId, $starttime, $endtime, $pos, $limit = 1000)
    {
        $address = '/api/v2/das/get-inav-access-data';
        $param   = [
            'inav_id'    => $inavId,
            'pos'        => $pos,
            'limit'      => $limit,
            'start_time' => date('Y/m/d', strtotime($starttime)),
            'end_time'   => date('Y/m/d', strtotime($endtime)),
        ];

        return $this->request($address, $param);
    }

    /**
     * 获取房间访问记录增量接口
     * @see    http://www.vhallyun.com/docs/show/1342
     * @author fym
     * @since  2021/6/15
     *
     * @param string $startTime
     * @param string $endTime
     * @param int    $pos
     * @param int    $limit
     * @param array  $inavIds 互动房间id,最大100, 为空查询所有
     *
     * @return mixed
     * @throws PaasException
     */
    public function getInavAccessDataBatch($startTime, $endTime, $pos, $limit = 1000, array $inavIds = [])
    {
        $address = '/api/v2/das/get-inav-access-data-batch';
        $param   = [
            'pos'        => $pos,
            'limit'      => $limit,
            'start_time' => $startTime,
            'end_time'   => $endTime,
        ];

        if ($inavIds) {
            $param['inav_ids'] = implode(',', $inavIds);
        }

        try {
            return $this->request($address, $param);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 拉流
     *
     * @param $data
     *
     * @return mixed
     * @throws \Exception
     */
    public function createLiveStream($data)
    {
        $address = '/api/v2/room/create-live-pull-stream-config';
        $param   = [
            'room_id'        => $data['room_id'],
            'source_type'    => $data['source_type'],
            'source_url'     => $data['source_url'],
            'source_room_id' => $data['source_room_id'],
            'start_time'     => date('Y-m-d H:i:s', strtotime('+3second')),
            'end_time'       => date('Y-m-d H:i:s', strtotime('+1hour')),
        ];

        return $this->request($address, $param);
    }

    /**
     * 获取问卷提交答案
     *
     * @see    http://wiki.vhallops.com/pages/viewpage.action?pageId=29720621
     * @author ensong.liu@vhall.com
     * @date   2018-11-27 21:39:04
     *
     * @param $naire_id
     *
     * @return mixed
     */
    public function getAnswerListAll($naire_id)
    {
        $address = '/api/v2/answer/list-all';
        $param   = [
            'id' => $naire_id,
        ];
        $result  = $this->request($address, $param);

        return $result;
    }

    /**
     * 获取单个答卷详情
     *
     * @see    http://wiki.vhallops.com/pages/viewpage.action?pageId=29720688
     * @author jianling.tan@vhall.com
     *
     * @param $id
     *
     * @return mixed
     */
    public function getAnswerDetail($questionId, $answerId)
    {

        //1、地址
        $address = '/api/v2/answer/get';

        //2、参数信息
        $params = [
            'id'        => $questionId,
            'answer_id' => $answerId,
        ];

        //3、获取数据
        try {
            $result = $this->request($address, $params);
            if (!isset($result['code']) && $result) {
                return $result;
            }
        } catch (\Exception $e) {
            return ['code' => 20001, 'msg' => '请求服务：' . $e->getMessage()];
        }

        return $result;
    }

    /**
     * 获取点播访问记录
     *
     * @see    http://www.vhallyun.com/docs/show/599
     *
     * @param string $record_id
     * @param string $begintime
     * @param string $endtime
     * @param int    $pos
     * @param int    $limit
     *
     * @return mixed
     * @author ensong.liu@vhall.com
     * @date   2019-02-18 11:43:39
     *
     */
    public function getRecordJoinInfo(
        string $record_id,
        string $begintime,
        string $endtime,
        int $pos = 0,
        int $limit = 1000
    ) {
        $address = '/api/v2/vod';
        $param   = [
            'action'     => 'GetRecordJoinInfo',
            'record_id'  => $record_id,
            'pos'        => $pos,
            'limit'      => $limit,
            'start_time' => $begintime,
            'end_time'   => $endtime,
        ];
        $result  = $this->request($address, $param);

        return $result;
    }

    /**
     * 获取增量点播访问记录
     *
     * @see    http://www.vhallyun.com/docs/show/1350
     *
     * @param string $startTime
     * @param string $endTime
     * @param int    $pos
     * @param int    $limit
     * @param array  $recordIds 点播 ID, 为空获取全部， 最大 100 个
     *
     * @since  2021/6/15
     * @author fym
     */
    public function getRecordJonInfoBatch($startTime, $endTime, $pos, $limit = 1000, array $recordIds = [])
    {
        $address = '/api/v2/das/get-vod-record-join-info-batch';
        $param   = [
            'pos'        => $pos,
            'limit'      => $limit,
            'start_time' => $startTime,
            'end_time'   => $endTime,
        ];

        if ($recordIds) {
            $param['record_ids'] = implode(',', $recordIds);
        }

        return $this->request($address, $param);
    }

    /*********************************document*****************************************/

    /**
     * 创建文档
     *
     * @param $file
     *
     * @return mixed
     */
    public function createDocument($file)
    {
        $params = [
            'document' => $file,
            'bu'       => vss_config('paas.bu'),
        ];
        return $this->CurlRequest('/api/v2/document/create', $params);
    }

    /**
     * 获取文档信息
     *
     * @param $documentId
     *
     * @return mixed
     */
    public function getDocumentInfo($documentId)
    {
        $params = [
            'document_id' => $documentId,
        ];
        return $this->request('/api/v2/document/get-info', $params);
    }

    /**
     * 获取当前文档信息
     *
     * @param $documentId
     *
     * @return mixed
     */
    public function getDocumentWatchInfo($channelId)
    {
        $params = [
            'channel_id' => $channelId,
        ];
        return $this->request('/api/v2/document/get-watch-info', $params);
    }

    /**
     * 删除文档
     *
     * @param $documentId
     *
     * @return mixed
     */
    public function deleteDocument($documentId)
    {
        $params = [
            'document_id' => $documentId,
        ];
        return $this->request('/api/v2/document/delete', $params);
    }
}
