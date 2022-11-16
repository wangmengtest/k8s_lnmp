<?php

namespace vhallComponent\menu\services;

use App\Constants\ResponseCode;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Vss\Common\Services\WebBaseService;
use vhallComponent\menu\models\MenuesModel;

/**
 * MenuService
 *
 * @uses     yangjin
 * @date     2020-09-02
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class MenuService extends WebBaseService
{
    /**
     * 菜单列表
     *
     * @param $tree
     *
     * @return Collection|MenuesModel|null
     * @author  jin.yang@vhall.com
     * @date    2020-09-02
     */
    public function getList($tree)
    {
        /** @var MenuesModel $menuList */
        $menuList = MenuesModel::getInstance()->get();
        $menuList = $tree ? MenuesModel::getTreeList($menuList) : $menuList;

        return $menuList;
    }

    /**
     * 菜单-添加
     *
     * @param $name
     * @param $url
     * @param $pid
     * @param $sort
     *
     * @return Model|object|MenuesModel|null
     *
     * @author  jin.yang@vhall.com
     * @date    2020-09-02
     */
    public function add($name, $url, $pid, $sort)
    {
        //父级菜单是否存在
        $condition = ['menu_id' => $pid];
        if ($pid > 0 && MenuesModel::getInstance()->getCount($condition) <= 0) {
            $this->fail(ResponseCode::EMPTY_MENU_PARENT);
        }

        //保存数据
        $attributes = [
            'name' => $name,
            'pid'  => $pid,
        ];
        $menuInfo   = MenuesModel::getInstance()->getRow($attributes);
        if (!$menuInfo) {
            $attributes['url']  = $url;
            $attributes['sort'] = $sort;
            $menuInfo           = MenuesModel::getInstance()->addRow($attributes);
            if (!$menuInfo) {
                $this->fail(ResponseCode::BUSINESS_CREATE_FAILED);
            }
        }

        return $menuInfo;
    }

    /**
     * 菜单-删除
     *
     * @param $menuIds
     *
     * @return array
     * @author  jin.yang@vhall.com
     * @date    2020-09-02
     */
    public function delete($menuIds)
    {
        //删除菜单记录
        $data       = [];
        $menuIdList = explode(',', $menuIds);
        foreach ($menuIdList as $menuId) {
            $condition = [
                'menu_id' => $menuId,
            ];
            $menuInfo  = MenuesModel::getInstance()->getRow($condition);
            if ($menuInfo && $menuInfo->delRow($menuId, true)) {
                array_push($data, $menuInfo['menu_id']);
            }
        }

        return $data;
    }

    /**
     * 菜单-编辑
     *
     * @param $menuId
     * @param $name
     * @param $url
     * @param $pid
     * @param $sort
     *
     *
     * @author  jin.yang@vhall.com
     * @date    2020-09-02
     */
    public function update($menuId, $name, $url, $pid, $sort)
    {
        //菜单信息
        $condition = [
            'menu_id' => $menuId,
        ];
        $menuInfo  = MenuesModel::getInstance()->getRow($condition);
        if (empty($menuInfo)) {
            $this->fail(ResponseCode::EMPTY_MENU_PARENT);
        }

        //是否存在
        $condition = [
            'name' => $name,
            'url'  => $url,
            'pid'  => $pid,
        ];
        if (MenuesModel::getInstance()->getRow($condition)) {
            $this->fail(ResponseCode::BUSINESS_MENU_EXIST);
        }

        //保存数据
        $attributes = [
            'name' => $name,
            'url'  => $url,
            'pid'  => $pid,
            'sort' => $sort,
        ];
        if ($menuInfo->update($attributes) == false) {
            $this->fail(ResponseCode::BUSINESS_EDIT_FAILED);
        }

        return true;
    }
}
