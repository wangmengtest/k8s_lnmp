<?php

namespace vhallComponent\action\services;

use App\Constants\ResponseCode;
use ReflectionClass;
use ReflectionMethod;
use Vss\Common\Services\WebBaseService;

class ActionService extends WebBaseService
{
    /**
     * @param     $tree
     * @param int $perPage
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection|null
     * @author   ming.wang@vhall.com
     *
     * @uses     wangming
     */
    public function getList($tree, $perPage = 1000)
    {
        $condition = [];

        $actionList = vss_model()->getActionsModel()->setPerPage($perPage)->getList($condition);
        $actionList = $actionList->items();
        if ($tree) {
            $actionModel = vss_model()->getActionsModel();
            $actionList  = $actionModel::getTreeList($actionList);
        }

        return $actionList;
    }

    /**
     * 操作-创建
     *
     * @param $params
     *
     * @return \Illuminate\Database\Eloquent\Model|\vhallComponent\action\models\ActionsModel|null
     *
     * @uses     wangming
     * @author   ming.wang@vhall.com
     *
     */
    public function create($params)
    {
        //保存数据
        $attributes = [
            'controller_name' => $params['controller_name'],
            'action_name'     => $params['action_name'],
        ];
        $actionInfo = vss_model()->getActionsModel()->getRow($attributes);
        if ($actionInfo) {
            $this->fail(ResponseCode::EMPTY_ACTION);
        }

        $actionInfo = vss_model()->getActionsModel()->addRow($params);
        if (!$actionInfo) {
            $this->fail(ResponseCode::BUSINESS_CREATE_FAILED);
        }

        return $actionInfo;
    }

    /**
     * @param $ids
     *
     * @return mixed
     * @author   ming.wang@vhall.com
     * @uses     wangming
     */
    public function delete($ids)
    {
        return vss_model()->getActionsModel()->delByIds($ids);
    }

    /**
     * 修改数据
     *
     * @param array $params
     *
     * @return bool
     *
     * @uses     wangming
     * @author   ming.wang@vhall.com
     *
     */
    public function update(array $params)
    {
        $action_id = $params['action_id'];
        //操作信息
        $condition  = [
            'action_id' => $params['action_id'],
        ];
        $actionInfo = vss_model()->getActionsModel()->getRow($condition);
        if (empty($actionInfo)) {
            $this->fail(ResponseCode::EMPTY_ACTION);
        }

        //是否存在
        $condition = [
            'controller_name' => $params['controller_name'],
            'action_name'     => $params['action_name'],
        ];
        if (vss_model()->getActionsModel()->getRow($condition)) {
            $this->fail(ResponseCode::BUSINESS_ACTION_EXIST);
        }

        //保存数据
        unset($params['action_id']);

        if (!$actionInfo->updateRow($action_id, $params)) {
            $this->fail(ResponseCode::BUSINESS_EDIT_FAILED);
        }

        return true;
    }

    public function generate($directoryIterator)
    {
        foreach ($directoryIterator as $fileinfo) {
            if ($fileinfo->isDir() == false) {
                include_once $fileinfo->getPathname();
                $controllerClassName = sprintf('%sController', $fileinfo->getBasename('.php'));
                $reflectedClass      = new ReflectionClass($controllerClassName);
                //父记录
                $attributes = [
                    'controller_name' => $reflectedClass->getName(),
                    'action_name'     => '',
                    'pid'             => 0,
                    'desc'            => $reflectedClass->getName(),
                ];

                $parentActionInfo = vss_model()->getActionsModel()->firstOrCreate($attributes);
                //子记录
                foreach (
                    $reflectedClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod
                ) {
                    if (preg_match('/\w+Action$/', $reflectionMethod->name) > 0
                        && !vss_model()->getActionsModel()->getRow([
                            'controller_name' => $reflectionMethod->class,
                            'action_name'     => $reflectionMethod->name,
                        ])
                    ) {
                        $attributes = [
                            'controller_name' => $reflectionMethod->class,
                            'action_name'     => $reflectionMethod->name,
                            'pid'             => $parentActionInfo->action_id,
                            'desc'            => $reflectionMethod->getDocComment() ? @str_replace([
                                '*',
                                ' ',
                            ], '', preg_split("/[\n]+/", $reflectionMethod->getDocComment())[1]) : '',
                        ];
                        $actionInfo = vss_model()->getActionsModel()->firstOrCreate($attributes);
                        $data[]     = $actionInfo ? $attributes : $actionInfo->toArray();
                    }
                }
            }
        }
    }
}
