<?php

namespace vhallComponent\menu\controllers\admin;

use vhallComponent\decouple\controllers\BaseController;

/**
 * MenuController extends BaseController
 *
 * @uses     yangjin
 * @date     2020-09-02
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class MenuController extends BaseController
{
    /**
     * 菜单-列表
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 17:08:34
     * @method  GET
     * @request int tree 树结构展示，可选值0|1
     */
    public function listAction()
    {
        $params = $this->getParam();
        //参数列表
        vss_validator($params, [
            'tree' => '',
        ]);
        $tree      = (bool)$params['tree'];

        $menuList = vss_service()->getMenuService()->getList($tree);

        //返回数据
        $this->success($menuList);
    }

    /**
     * 菜单-添加
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 17:08:34
     * @request string  name    菜单名称
     * @request string  url     菜单地址，不包含host
     * @request int     pid     菜单父级菜单ID，默认0
     * @request int     sort    排序，默认0，越大越靠后
     */
    public function addAction()
    {
        $params = $this->getParam();
        //参数列表
        vss_validator($params, [
            'name' => 'required',
            'url'  => 'required',
            'pid'  => 'required',
            'sort' => 'required',
        ]);
        //参数列表
        $name = $params['name'];
        $url  = $params['url'];
        $pid  = (int)$params['pid'];
        $sort = $params['sort'];

        $data = vss_service()->getMenuService()->add($name, $url, $pid, $sort);
        $this->success($data);
    }

    /**
     * 菜单-删除
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 17:08:34
     * @request int menu_id 菜单ID
     */
    public function deleteAction()
    {
        $params = $this->getParam();
        //参数列表
        vss_validator($params, [
            'menu_ids' => 'required',
        ]);
        //参数列表
        $menuIds = $params['menu_ids'];

        $data = vss_service()->getMenuService()->delete($menuIds);

        //返回数据
        $this->success($data);
    }

    /**
     * 菜单-编辑
     *
     * @return void
     *
     * @author  ensong.liu@vhall.com
     * @date    2019-01-29 17:08:34
     * @request int    menu_id  菜单ID
     * @request string name     菜单名称
     * @request string url      菜单地址，不包含host
     * @request int    pid      菜单父级ID
     * @request int    sort     排序
     */
    public function editAction()
    {
        $params = $this->getParam();
        //参数列表
        vss_validator($params, [
            'menu_id' => 'required',
            'name'    => 'required',
            'url'     => 'required',
            'pid'     => 'required',
            'sort'    => 'required',
        ]);
        //参数列表
        $menuId = $params['menu_id'];
        $name   = $params['name'];
        $url    = $params['url'];
        $pid    = (int)$params['pid'];
        $sort   = $params['sort'];

        vss_service()->getMenuService()->update($menuId, $name, $url, $pid, $sort);

        $this->success();
    }
}
