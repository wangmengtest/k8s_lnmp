<?php
/**
 * Created by PhpStorm.
 * User: liuxiangliang
 * Date: 2020/11/20
 * Time: 15:44
 */

namespace vhallComponent\diypage\controllers\console;

use vhallComponent\decouple\controllers\BaseController;

class DiypageController extends BaseController
{
    /**
     * 获取直播间自定义标签
     *
     */
    public function customTagAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'il_id' => 'required',
        ]);
        $ilId = $params['il_id'];
        $data = vss_service()->getDiypageService()->getCustomTag($ilId);
        $this->success($data);
    }

    /**
     * 添加/更新自定义标签
     *
     *
     * @author bingtian.yu@vhall.com
     * @date   2020-06-17 11:03:48
     */
    public function updateCustomTagAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'il_id'      => 'required',
            'custom_tag' => 'required',
        ]);

        $ilId      = $params['il_id'];
        $customTag = $params['custom_tag'];

        $extends = vss_service()->getDiypageService()->updateCustomTag($ilId, $customTag,
            $this->accountInfo['account_id']);

        $this->success($extends);
    }
}
