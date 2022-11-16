<?php
/**
 * Created by PhpStorm.
 * User: gaoningning
 * Date: 2018/10/24
 * Time: 15:46
 */

namespace App\Component\common\src\services;

use App\Models\RecommendCardModel;
use Vss\Common\Services\WebBaseService;
use App\Component\common\src\constants\KeyPrefix;
use vhallComponent\recommendCard\constants\KeyPrefixConstant;

class ReportServices extends WebBaseService
{
    /**
     * 浏览、点击卡片行为上报
     *
     * @param RecommendCardModel $recommendCard
     * @param int                $status     0浏览 1点击
     * @param array              $customData 自定义数据
     *
     * @throws
     */
    public function visitRecommendCardBehavior($recommendCard, $status, $customData = [])
    {
        $time = vss_redis()->get(KeyPrefixConstant::RECOMMEND_CARD_PUSH . $recommendCard['recommend_card_id']);
        if ($time) {
            $commonParams = $this->getBehaviorCommonParams();
            $params       = [
                'k'     => 602016,
                'id'    => $commonParams['id'],
                's'     => $commonParams['s'],
                'token' => base64_encode(json_encode(array_merge([
                    'business_uid'        => $recommendCard['business_user_id'],
                    'activity_id'         => $recommendCard['room_id'],
                    'market_tools_id'     => $recommendCard['recommend_card_id'],
                    'behavior'            => 15,
                    'market_tools_status' => $status,
                    'event'               => $time >= time() - 300 ? date('Y-m-d H:i:s', $time) : '',
                    'pf'                  => $commonParams['pf'],
                    'bu'                  => $commonParams['bu'],
                ], $customData)))
            ];
            $this->behaviorReport($params);
        }
    }

    /**
     * @return array
     */
    public function getBehaviorCommonParams()
    {
        $unique = ceil(microtime(true) * 1000) . mt_rand(1000, 9999);
        return [
            'bu' => 5,
            's'  => $unique,
            'id' => $unique,
            'pf' => DeviceUtils::isMobile() ? 3 : 7
        ];
    }

    /**
     * 行为上报
     *
     * @param $msg
     */
    public function behaviorReport($msg)
    {
        try {
            $mq = new MqUtils();
            $mq->push(KeyPrefix::QUEUE . 'behavior', json_encode($msg));
            vss_logger()->info('queue-info-behavior',
                ['type' => $msg['k'], 'data' => $msg + ['origin' => json_decode(base64_decode($msg['token']), true)]]);
        } catch (\Exception $e) {
            vss_logger()->error('queue-error-behavior',
                ['type' => $msg['k'], 'data' => $msg, 'error' => $e->getMessage()]);
        }
    }

    /**
     * 大数据上报
     *
     * @param $data
     * @param $uri
     */
    public function syncReport($data, $uri)
    {
        try {
            $mq = new MqUtils();
            $mq->push(KeyPrefix::QUEUE . 'sync', json_encode([
                'uri'  => $uri,
                'data' => $data
            ]));
            vss_logger()->info('queue-info-sync', ['type' => $uri, 'data' => $data]);
        } catch (\Exception $e) {
            vss_logger()->error('queue-error-sync', ['type' => $uri, 'data' => $data, 'error' => $e->getMessage()]);
        }
    }

    /**
     * B端用户中心上报
     *
     * @param $data
     * @param $uri
     */
    public function businessReport($data, $uri)
    {
        $data['bu'] = vss_config('bu');
        try {
            $mq = new MqUtils();
            $mq->push(KeyPrefix::QUEUE . 'bc', json_encode([
                'uri'  => $uri,
                'data' => $data
            ]));
            vss_logger()->info('queue-info-bc', ['type' => $uri, 'data' => $data]);
        } catch (\Exception $e) {
            vss_logger()->error('queue-error-bc', ['type' => $uri, 'data' => $data, 'error' => $e->getMessage()]);
        }
    }

    /**
     * 数据中心上报
     *
     * @param $data
     * @param $uri
     */
    public function dcReport($data, $uri)
    {
        $data['bu'] = vss_config('bu');
        try {
            $mq = new MqUtils();
            $mq->push(KeyPrefix::QUEUE . 'dc', json_encode([
                'uri'  => $uri,
                'data' => $data
            ]));
            vss_logger()->info('queue-info-dc', ['type' => $uri, 'data' => $data]);
        } catch (\Exception $e) {
            vss_logger()->error('queue-error-dc', ['type' => $uri, 'data' => $data, 'error' => $e->getMessage()]);
        }
    }
}
