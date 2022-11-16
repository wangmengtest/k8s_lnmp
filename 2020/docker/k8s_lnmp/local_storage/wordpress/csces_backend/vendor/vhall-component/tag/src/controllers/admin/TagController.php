<?php

namespace vhallComponent\tag\controllers\admin;

use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;
use Illuminate\Support\Arr;

/**
 * TagController extends BaseController
 *
 * @uses     yangjin
 * @date     2020-08-12
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class TagController extends BaseController
{
    /**
     *保存
     *
     *
     */
    public function saveAction()
    {
        $params = $this->getParam();
        $rule   = [
            'name' => 'required',
            'type' => 'required',
        ];
        $data   = vss_validator($params, $rule);
        $result = vss_service()->getTagService()->save($data);

        $this->success($result);
    }

    /**
     * 列表
     *
     *
     * @author   ming.wang@vhall.com
     * @uses     wangming
     */
    public function listAction()
    {
        $params    = $this->getParam();
        $rule      = [
            'type'      => 'required',
            'curr_page' => '',
            'page_size' => '',
            'name'      => '',
        ];
        $validated = vss_validator($params, $rule);

        $result = vss_service()->getTagService()->list($params);
        $this->success($result);
    }

    /**
     * 编辑
     *
     *
     */
    public function editAction()
    {
        $params = $this->getParam();
        $rule   = [
            'tag_id' => 'required',
            'type'   => '',
            'name'   => '',
        ];

        $data   = vss_validator($params, $rule);
        $result = vss_service()->getTagService()->update($data);
        $this->success($result);
    }

    /**
     * 删除
     *
     *
     */
    public function deleteAction()
    {
        $params    = $this->getParam();
        $validated = vss_validator($params, [
            'tag_ids' => 'required',
        ]);

        $result = vss_service()->getTagService()->delete($params);
        $this->success($result);
    }

    /**
     * 详情
     *
     *
     */
    public function infoAction()
    {
        $params    = $this->getParam();
        $validated = vss_validator($params, [
            'tag_ids' => 'required',
        ]);

        $result = vss_service()->getTagService()->getInfo($params['tag_ids']);
        $this->success($result);
    }

    public function orderAction()
    {
        $params['tag_ids'] = $this->getParam('tag_ids', 0);
        $params['rank']    = $this->getParam('rank', 0);

        $result = vss_service()->getTagService()->rank($params);
        if (empty($result)) {
            $this->fail(ResponseCode::EMPTY_INFO);
        }

        $this->success($result);
    }
}
