<?php

namespace vhallComponent\pendant\services;

use App\Constants\ResponseCode;
use vhallComponent\pendant\jobs\SaveOperateStatsJob;
use Vss\Queue\Redis as Queue;
use vhallComponent\pendant\constants\PendantConstant;
use vhallComponent\room\constants\CachePrefixConstant;
use App\Traits\ModelTrait;
use App\Traits\ServiceTrait;
use Vss\Common\Services\WebBaseService;

/**
 * Class PendantService
 *
 * @package  vhallComponent\pendant\src\services
 * @date     2021/3/18
 * @author   jun.ou@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class PendantService extends WebBaseService
{


    /**
     * @param string $fileName
     *
     * @return mixed|string|void
     *
     *
     * @date     2021/2/25
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    protected function getImg($fileName = 'image')
    {
        $params = [
            'tag'        => '1',
            'file_name'  => $fileName,
            'path'       => '',
            'force_name' => ''
        ];

        return vss_service()->getUploadService()->create($params);
    }

    /**
     * @param $params
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     *
     * @date     2021/3/19
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function getList($params)
    {
        vss_validator($params, [
            'account_id' => '',
            'type'       => '',
            'keyworkd'   => '',
            'page'       => '',
            'page_size'  => '',
        ]);

        $page      = $params['page'] ?? 1;
        $pageSize  = $params['page_size'] ?? 10;
        $condition = [
            'account_id' => $params['account_id'],
            'type'       => $params['type'],
            'status'     => PendantConstant::STATUS_ON,
            'keyword'    => $params['keyword'] ?? ''
        ];

        return vss_model()->getPendantModel()->getPageList($condition, $page, $pageSize);
    }

    /**
     * @param $params
     *
     * @return false|\vhallComponent\pendant\models\PendantModel
     *
     *
     * @date     2021/3/19
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function create($params)
    {
        $params['pic']  = $this->getImg('pic');
        $params['icon'] = $this->getImg('icon');

        vss_validator($params, [
            'name'        => '',
            'type'        => '',
            'pic'         => 'required',
            'pendant_url' => '',
            'account_id'  => '',
        ]);

        return vss_model()->getPendantModel()->create($params);
    }

    /**
     * @param $params
     *
     * @return bool
     *
     *
     * @date     2021/3/19
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function update($params)
    {
        $params['pic']  = $this->getImg('pic') ?? '';
        $params['icon'] = $this->getImg('icon') ?? '';

        vss_validator($params, [
            'id'          => '',
            'name'        => '',
            'pic'         => '',
            'pendant_url' => '',
            'account_id'  => '',
            'changeimg'   => '',
        ]);

        $info = vss_model()->getPendantModel()->getRowById($params['id'], $params['account_id']);
        if (empty($info)) {
            return true;
        }

        $attr = [
            'name'        => $params['name'],
            'pic'         => $params['pic'],
            'icon'        => $params['icon'] ?? '',
            'pendant_url' => $params['pendant_url'],
        ];
        if (empty($params['pic'])) {
            unset($attr['pic']);
        }

        if ($params['type'] == PendantConstant::TYPE_FIXED && empty($params['icon'])) {
            unset($attr['icon']);
        }

        if ($info->update($attr) === false) {
            return true;
        }

        return vss_model()->getPendantModel()->setDefaultFixed($params['account_id']);

    }

    /**
     * @param $params
     *
     * @return bool
     *
     *
     * @date     2021/3/19
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function setDefaultFixed($params)
    {
        vss_validator($params, [
            'id'         => '',
            'account_id' => '',
            'is_default' => '',
        ]);

        $params['is_default'] = $params['is_default'] ?? PendantConstant::STATUS_ON;
        $pendantModel         = vss_model()->getPendantModel();
        $info                 = $pendantModel->getRowById($params['id'], $params['account_id']);
        if (empty($info) || $info->is_default == $params['is_default']) {
            return true;
        }

        if ($info->type != PendantConstant::TYPE_FIXED) {
            return false;
        }

        $pendantModel->getConnection()->beginTransaction();
        try {
            if ($params['is_default'] == PendantConstant::STATUS_ON) {
                $condition = [
                    'status'     => PendantConstant::STATUS_ON,
                    'type'       => PendantConstant::TYPE_FIXED,
                    'account_id' => $params['account_id']
                ];
                $pendantModel->updateALl($condition, ['is_default' => PendantConstant::STATUS_OFF]);
            }

            $info->update(['is_default' => $params['is_default']]);

            $pendantModel->setDefaultFixed($params['account_id']);

            $pendantModel->getConnection()->commit();
        } catch (\Exception $e) {
            $pendantModel->getConnection()->rollBack();

            $this->fail(ResponseCode::COMP_PENDANT_SET_FIXED_FAILED);
        }

        return true;
    }

    /**
     * @param $params
     *
     * @return bool
     *
     *
     * @date     2021/3/22
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function delete($params)
    {
        vss_validator($params, [
            'id'         => '',
            'account_id' => '',
        ]);

        $ids = explode(',', $params['id']);

        vss_model()->getPendantModel()->deleteRow($ids, $params['account_id']);

        vss_model()->getPendantModel()->setDefaultFixed($params['account_id']);

        return true;
    }

    /**
     * @param $params
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     *
     * @date     2021/3/19
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function getStatsList($params)
    {
        vss_validator($params, [
            'il_id'      => '',
            'type'       => '',
            'begin_time' => '',
            'end_time'   => '',
            'page'       => '',
            'page_size'  => '',
        ]);
        $page     = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 10;

        $condition = [
            'il_id'        => $params['il_id'],
            'pendant_type' => $params['type'],
            'begin_time'   => $params['begin_time'] ?? '',
            'end_time'     => $params['begin_time'] ?? '',
        ];

        return vss_model()->getPendantStatsModel()->getPageList($condition, $page, $pageSize);
    }

    /**
     * @param $params
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     *
     * @date     2021/3/19
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function getPushList($params)
    {
        vss_validator($params, [
            'master_id' => '',
            'keyworkd'  => '',
            'page'      => '',
            'page_size' => '',
        ]);

        $page     = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 5;

        $condition = [
            'account_id' => $params['master_id'],
            'type'       => PendantConstant::TYPE_PUSH,
            'status'     => PendantConstant::STATUS_ON,
            'keyword'    => $params['keyword'] ?? ''
        ];

        $list     = vss_model()->getPendantModel()->getPageList($condition, $page, $pageSize);
        $list     = $list->toArray();
        $pushList = [];
        foreach ($list['data'] as $row) {
            $row['duration'] = 60;
            $pushList[]      = $row;
        }

        $list['data'] = $pushList;

        return $list;
    }

    /**
     * @param $params
     *
     * @return bool
     *
     *
     * @date     2021/3/22
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function pushScreen($params)
    {
        vss_validator($params, [
            'il_id'         => '',
            'pendant_id'    => '',
            'channel_id'    => '',
            'screen_second' => '',
        ]);

        $pendant = vss_model()->getPendantModel()->getRowById($params['pendant_id']);
        if (empty($pendant)) {
            return true;
        }

        $statsModel = vss_model()->getPendantStatsModel();
        $statsModel->getConnection()->beginTransaction();
        try {
            $data = ['screen_second' => $params['screen_second'], 'pendant_type' => $pendant['type']];
            $statsModel->updateStats($params['il_id'], $params['pendant_id'], $data);

            //发送消息
            $body = [
                'type'          => 'pendant_push_screen',        // 消息类型：3001-推屏，2000-点赞，1010-禁言，1011-取消禁言，3000-暂停，3010-播放中，3011-停止播放，1000-增加为用户数，1012-新增边拍边买商品
                'id'            => $pendant['id'],               //挂件id
                'pendant_type'  => $pendant['type'],             //挂件类型
                'name'          => $pendant['name'],             //挂件名称
                'pendant_url'   => $pendant['pendant_url'],      //挂件详情地址
                'pic'           => $pendant['pic'],              //挂件图片
                'icon'          => $pendant['icon'],             //挂件图标
                'screen_second' => $params['screen_second'],     //推屏时间
            ];

            vss_service()->getPaasChannelService()->sendMessageByChannel($params['channel_id'], $body, null, 'service_custom');

            $statsModel->getConnection()->commit();
        } catch (\Exception $e) {
            $statsModel->getConnection()->rollBack();

            $this->fail(ResponseCode::COMP_PENDANT_PUSH_FAILED);
        }

        return true;
    }

    /**
     * @param $params
     *
     * @return array
     *
     *
     * @date     2021/3/22
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function getDefaultFixed($params)
    {
        vss_validator($params, [
            'master_id' => '',
        ]);

        return vss_model()->getPendantModel()->getDefaultFixed($params['master_id']);
    }

    /**
     * @param $params
     *
     * @return bool
     *
     *
     * @date     2021/3/22
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function click($params)
    {
        vss_validator($params, [
            'il_id'      => '',
            'pendant_id' => '',
            'account_id' => '',
        ]);

        $params = [
            'il_id'      => $params['il_id'],
            'account_id' => $params['account_id'],
            'pendant_id' => $params['pendant_id'],
            'type'       => PendantConstant::OPERATE_TYPE_CLICK,
            'date'       => date('Y-m-d'),
        ];

        //写入记录
        vss_model()->getPendantOperateRecordModel()->create($params);

        vss_queue()->push(new SaveOperateStatsJob($params));

        return true;
    }

    /**
     * @return bool
     *
     * @date     2021/3/24
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function saveOperateStats($params)
    {
        $ilId      = $params['il_id'];
        $pendantId = $params['pendant_id'];
        $date      = $params['date'];

        $pendantModel = vss_model()->getPendantModel();
        $statsModel   = vss_model()->getPendantStatsModel();
        $recordModel  = vss_model()->getPendantOperateRecordModel();

        $pendant = $pendantModel->getRowById($pendantId);
        if (empty($pendant)) {
            return true;
        }

        $pv     = $recordModel->getCount(['il_id' => $ilId, 'pendant_id' => $pendantId, 'date' => $date]);
        $uv     = $recordModel->getUv($ilId, $pendantId, $date);
        $params = [
            'pv_num'       => $pv,
            'uv_num'       => $uv,
            'pendant_type' => $pendant->type,
            'date'         => $date
        ];

        return $statsModel->updateStats($ilId, $pendantId, $params);
    }
}
