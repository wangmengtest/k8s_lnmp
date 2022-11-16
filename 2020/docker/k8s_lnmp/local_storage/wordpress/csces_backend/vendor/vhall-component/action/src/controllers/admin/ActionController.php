<?php

namespace vhallComponent\action\controllers\admin;

use vhallComponent\decouple\controllers\BaseController;
use Illuminate\Support\Arr;

/**
 * ActionController extends BaseController
 *
 * @uses     yangjin
 * @date     2020-07-31
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class ActionController extends BaseController
{
    /**
     * 操作列表
     *
     * @uses     wangming
     * @author   ming.wang@vhall.com
     */
    public function listAction()
    {
        $data = $this->getParam();

        $tree = (bool)$data['tree'];
        $this->success(vss_service()->getActionService()->getList($tree));
    }

    /**
     * 操作-添加
     *
     * @return void
     *
     * @request string  controller_name 控制器名称xxxxxController
     * @request string  action_name     操作名称xxxxAction
     * @request int     pid             父id
     * @request string  desc            描述信息
     *
     */
    public function addAction()
    {
        $params = $this->getParam();
        $rule   = [
            'controller_name' => 'required',
            'action_name'     => 'required',
            'pid'             => 'required',
            'desc'            => 'required',
        ];

        $data   = vss_validator($params, $rule);
        $result = vss_service()->getActionService()->create($data);

        $this->success($result ?? []);
    }

    /**
     * 操作-删除
     *
     * @return void
     *
     * @request int action_id 操作ID
     *
     */
    public function deleteAction()
    {
        $params    = $this->getParam();
        $validator = vss_validator($params, [
            'action_ids' => 'required',
        ]);

        $actionIdList = explode(',', $params['action_ids']);
        $result       = vss_service()->getActionService()->delete($actionIdList);

        //返回数据
        $this->success($result);
    }

    /**
     * 操作-编辑
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 17:05:16
     * @method  POST
     * @request int     action_id       操作ID
     * @request string  controller_name 控制器名称xxxxxController
     * @request string  action_name     操作名称xxxxAction
     * @request int     pid             父id
     * @request string  desc            描述信息
     */
    public function editAction()
    {
        $params = $this->getParam();
        $rule   = [
            'action_id'       => 'required',
            'controller_name' => 'required',
            'action_name'     => 'required',
            'pid'             => 'required',
            'desc'            => 'required',
        ];
        $data   = vss_validator($params, $rule);

        $result = vss_service()->getActionService()->update($data);

        //返回数据
        $this->success($result);
    }

    /**
     * 操作-列表生成器
     *
     * @return void
     * @author ensong.liu@vhall.com
     * @date   2019-01-29 17:10:55
     * @method GET|POST
     */
    public function generateAction()
    {
        $dir               = dirname(__FILE__);
        $directoryIterator = new \DirectoryIterator($dir);
        $result            = vss_service()->getActionService()->generate($directoryIterator);

        $this->success($result);
    }
}
