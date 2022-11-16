<?php

namespace vhallComponent\publicforward\services;

use App\Constants\ResponseCode;
use Vss\Exceptions\PublicForwardException;
use Vss\Utils\HttpUtil;
use Vss\Common\Services\WebBaseService;
use vhallComponent\pay\services\PayService;

class PublicforwardService extends WebBaseService
{
    /**
     * 共享转发服务接口地址
     *
     * @var array|null|string
     */
    private $host = '';

    /**
     * 共享转发服务应用ID
     *
     * @var array|null|string
     */
    private $appId = '';

    /**
     * 共享转发服务应用秘钥
     *
     * @var array|null|string
     */
    private $secretKey = '';

    /**
     * PublicForwardServiceImpl constructor.
     */
    public function __construct()
    {
        //共享转发服务app_id 与 paas服务的app_id一致
        $this->host = vss_config('forward.host');
//        $this->appId = isset($_REQUEST['app_id']) ? $_REQUEST['app_id'] : TokenServiceImpl::getInstance()->getAppId();
        $this->appId     = $_REQUEST['app_id'] ?? vss_config('paas.apps.lite.appId');
        $this->secretKey = vss_paas_util()->getPaasAppSecretByAppId($this->appId);
    }

    /**
     * 发送共享转发请求
     *
     * @param string $uri
     * @param array  $params
     *
     * @return mixed
     */
    public function request(string $uri, array $params = [])
    {
        $url_info = explode('/', $uri);
        if (!in_array($url_info[2], ['redpacket', 'pay'])) {
            $params['bu'] = vss_config('paas.bu');
        }
        $params   = vss_paas_util()->generateParams($params, $this->appId, $this->secretKey);
        $response = HttpUtil::post($this->host . $uri, $params, null, 20);
        if ($response->getCode() != 200) {
            throw new PublicForwardException($response->getCode(), $response->getMessage());
        }
        $data = $response->getData();
        if ($data['code'] != 200) {
            throw new PublicForwardException($data['code'], $data['msg']);
        }
        return $data['data'];
    }

    //签到API****************************************************************//

    /**
     * 签到创建
     *
     * @param array $params
     *
     * @return mixed
     */
    public function signAdd(array $params)
    {
        return $this->request('/v1/sign/add', $params);
    }

    /**
     * 用户签到
     *
     * @param array $params
     *
     * @return mixed
     */
    public function signIn(array $params)
    {
        return $this->request('/v1/sign/in', $params);
    }

    /**
     * 获取签到记录列表
     *
     * @param array $params
     *
     * @return mixed
     */
    public function signRecordsGet(array $params)
    {
        return $this->request('/v1/sign/records-get', $params);
    }

    /**
     * 获取发起的签到列表
     *
     * @param array $params
     *
     * @return mixed
     */
    public function signGets(array $params)
    {
        return $this->request('/v1/sign/gets', $params);
    }

    //API****************************************************************//

    /**
     * 打赏
     *
     * @param array $params
     *
     * @return mixed
     */
    public function rewardGive(array $params)
    {
        $result = $this->request('/v1/reward/give', $params);
        //使用虚拟支付
        PayService::setTradeNoCache($result);
        return $result;
    }

    /**
     * 打赏排行榜
     *
     * @param array $params
     *
     * @return mixed
     */
    public function rewardListRank(array $params)
    {
        return $this->request('/v1/reward/list-rank', $params);
    }

    /**
     * 获取打赏排行信息
     *
     * @param array $params
     *
     * @return mixed
     */
    public function rewardRewarderRank(array $params)
    {
        return $this->request('/v1/reward/rewarder-rank', $params);
    }

    /**
     * 获取打赏记录
     *
     * @param array $params
     *
     * @return mixed
     */
    public function rewardRecordsGet(array $params)
    {
        return $this->request('/v1/reward/records-get', $params);
    }

    /**
     * 设置打赏支付状态
     *
     * @param array $params
     *
     * @return mixed
     */
    public function rewardPayStatusSet(array $params)
    {
        return $this->request('/v1/reward/pay-status-set', $params);
    }

    //支付API****************************************************************//

    /**
     * 支付
     *
     * @param array $params
     *
     * @return mixed
     */
    public function payGetPayment(array $params)
    {
        return $this->request('/v1/pay/get-payment', $params);
    }

    //红包API****************************************************************//

    /**
     * 创建红包
     *
     * @param array $params
     *
     * @return mixed
     */
    public function redPacketCreate(array $params)
    {
        $result = $this->request('/v1/red-packet/create', $params);
        //使用虚拟支付
        PayService::setTradeNoCache($result);
        return $result;
    }

    /**
     * 设置红包支付状态
     *
     * @param array $params
     *
     * @return mixed
     */
    public function redPacketPayStatusSet(array $params)
    {
        return $this->request('/v1/red-packet/pay-status-set', $params);
    }

    /**
     * 获取红包列表
     *
     * @param array $params
     *
     * @return mixed
     */
    public function redPacketGetList(array $params)
    {
        return $this->request('/v1/red-packet/get-list', $params);
    }

    /**
     * 获取红包我的领取详情
     *
     * @param array $params
     *
     * @return mixed
     */
    public function redPacketGetRecord(array $params)
    {
        return $this->request('/v1/red-packet/get-record', $params);
    }

    /**
     * 获取红包信息
     *
     * @param array $params
     *
     * @return mixed
     */
    public function redPacketGetInfo(array $params)
    {
        return $this->request('/v1/red-packet/get-info', $params);
    }

    /**
     * 抢红包
     *
     * @param array $params
     *
     * @return mixed
     */
    public function redPacketGet(array $params)
    {
        return $this->request('/v1/red-packet/get', $params);
    }

    /**
     * 获取抢红包记录列表
     *
     * @param array $params
     *
     * @return mixed
     */
    public function redPacketGetRecords(array $params)
    {
        return $this->request('/v1/red-packet/get-records', $params);
    }

    /**
     * 根据来源ID结束红包并退还未领取的红包
     *
     * @param array $params
     *
     * @return mixed
     */
    public function redPacketOverBySourceId(array $params)
    {
        return $this->request('/v1/red-packet/over-by-source-id', $params);
    }

    //抽奖API****************************************************************//

    /**
     * 创建抽奖
     *
     * @param array $params
     *
     * @return mixed
     */
    public function lotteryAdd(array $params)
    {
        return $this->request('/v1/lottery/add', $params);
    }

    /**
     * 结束抽奖
     *
     * @param array $params
     *
     * @return mixed
     */
    public function lotteryEnd(array $params)
    {
        return $this->request('/v1/lottery/end', $params);
    }

    /**
     * 领奖信息更新
     *
     * @param array $params
     *
     * @return mixed
     */
    public function lotteryAward(array $params)
    {
        return $this->request('/v1/lottery/award', $params);
    }

    /**
     * 获取抽奖列表
     *
     * @param array $params
     *
     * @return mixed
     */
    public function lotteryGets(array $params)
    {
        return $this->request('/v1/lottery/gets', $params);
    }

    /**
     * 获取抽奖中奖人名单
     *
     * @param array $params
     *
     * @return mixed
     */
    public function lotteryUsersGet(array $params)
    {
        return $this->request('/v1/lottery/users-get', $params);
    }

    //礼品API****************************************************************//

    /**
     * 礼品活动创建
     *
     * @param array $params
     *
     * @return mixed
     */
    public function giftAdd(array $params)
    {
        return $this->request('/v1/gift/add', $params);
    }

    /**
     * 礼品活动删除
     *
     * @param array $params
     *
     * @return mixed
     */
    public function giftDelete(array $params)
    {
        return $this->request('/v1/gift/delete', $params);
    }

    /**
     * 礼品活动编辑
     *
     * @param array $params
     *
     * @return mixed
     */
    public function giftEdit(array $params)
    {
        return $this->request('/v1/gift/edit', $params);
    }

    /**
     * 礼品列表
     *
     * @param array $params
     *
     * @return mixed
     */
    public function giftList(array $params)
    {
        return $this->request('/v1/gift/list', $params);
    }

    /**
     * 礼品使用列表
     *
     * @param array $params
     *
     * @return mixed
     */
    public function giftUsedList(array $params)
    {
        return $this->request('/v1/gift/used-list', $params);
    }

    /**
     * 设置默认
     *
     * @param array $params
     *
     * @return mixed
     */
    public function giftMappingSave(array $params)
    {
        return $this->request('/v1/gift/mapping-save', $params);
    }

    /**
     * 更新支付状态
     *
     * @param array $params
     *
     * @return mixed
     */
    public function giftPayStatusSet(array $params)
    {
        return $this->request('/v1/gift/pay-status-set', $params);
    }

    /**
     * 送礼物API接口
     *
     * @param array $params
     *
     * @return mixed
     */
    public function giftSend(array $params)
    {
        $result = $this->request('/v1/gift/send', $params);

        //使用虚拟支付
        $result['numbers'] = $result['numbers'] ?? $params['numbers'];
        PayService::setTradeNoCache($result);
        return $result;
    }

    /**
     * 问答创建
     *
     * @param array $params
     *
     * @return mixed
     */
    public function questionCreate(array $params)
    {
        return $this->request('/v1/question/create', $params);
    }

    /**
     * 获取问答信息
     *
     * @param array $params
     *
     * @return mixed
     */
    public function questionGet(array $params)
    {
        return $this->request('/v1/question/get', $params);
    }

    /**
     * 发布/取消发布问答
     *
     * @param array $params
     *
     * @return mixed
     */
    public function questionPublish(array $params)
    {
        return $this->request('/v1/question/publish', $params);
    }

    /**
     * 回答
     *
     * @param array $params
     *
     * @return mixed
     */
    public function answerCreate(array $params)
    {
        return $this->request('/v1/answer/create', $params);
    }

    /**
     * 处理问答
     *
     * @param array $params
     *
     * @return mixed
     */
    public function questionDeal(array $params)
    {
        return $this->request('/v1/question/deal', $params);
    }

    /**
     * 回复处理
     *
     * @param array $params
     *
     * @return mixed
     */
    public function answerDeal(array $params)
    {
        return $this->request('/v1/answer/deal', $params);
    }

    /**
     * 提问列表
     *
     * @param array $params
     *
     * @return mixed
     */
    public function questionLists(array $params)
    {
        $version = $params['version'] ?? 'v1'; // v1, v2
        return $this->request('/' . $version . '/question/lists', $params);
    }

    /**
     * 同问问题
     * @auther yaming.feng@vhall.com
     * @date   2020/12/28
     *
     * @params array $params
     *
     * @return array
     */
    public function questionAlsoAsk(array $params)
    {
        return $this->request('/v1/question/same', $params);
    }

    /**
     * 获取用户同问的问题 ID列表
     * @auther yaming.feng@vhall.com
     * @date 2021/1/18
     *
     * @param array $params
     *
     * @return array
     */
    public function userAlsoAskQuestion(array $params)
    {
        return $this->request('/v1/question/get-same-by-user', $params);
    }
}
