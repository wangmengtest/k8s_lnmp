<?php

namespace App\Component\account\src\services;

use Vss\Common\Services\WebBaseService;

/**
 * AccountOrgServiceTrait
 */
class AccountOrgService extends WebBaseService
{
    protected $userData = [];

    protected $orgData = [];

    protected static $orgDataByCode = [];

    protected static $orgIdByCode = [];

    protected static $orgNameByOrgId = [];

    protected $hasUsers = true;

    protected $startOrgType = -1;

    protected $commonOrgs = [];

    protected $commonDepts = [];

    protected $virtualId = 90000000;//虚拟部门ID

    protected $virtualOrgType = 100000;//虚拟部门org_type

    /**
     * 同步组织部门,带人员列表,1小时缓存
     * @param     $params
     */
    public function cacheOrgList()
    {
        return vss_service()->getCacheOrgService()->cacheOrgList();
    }

    /** 同步组织部门,带人员列表-带缓存
     * @param     $params
     */
    public function orgList()
    {
        $sortOrgData = $this->formatOrgData();
        return $this->foreachOrgData($sortOrgData);
    }

    /*
     * 只返回组织架构,不需要人
     * */
    public function orgListNoneUser($startOrg = '')
    {
        $this->hasUsers = false;
        $sortOrgData = $this->formatOrgData($startOrg);
        return $this->foreachOrgData($sortOrgData);
    }

    /*
     * 只返回组织架构,不需要人,1小时缓存
     * */
    public function cacheOrgsNoneUser($startOrg = '')
    {
        return vss_service()->getCacheOrgService()->cacheOrgsNoneUser($startOrg);
    }

    /*
     * 只返回同级及以下部门IDS
     * */
    public function getCommonOrgsAndDepts($startOrg = '')
    {
        $this->commonDepts = [];
        $this->commonOrgs = [];
        if(empty($startOrg)){
            return [];
        }
        $this->hasUsers = false;
        $sortOrgData = $this->formatOrgData($startOrg);
        $this->foreachOrgData($sortOrgData);
        return [
            'orgs' => array_unique($this->commonOrgs),
            'depts' => array_unique($this->commonDepts),
        ];
    }

    /*
     * 组装人员信息,执照部门分组
     * */
    private function formatUserData(){
        $list = $this->allUser();
        $orgData = [];
        foreach ($list as $org){
            $org['id'] = $org['account_id'];
            $org['name'] = $org['nickname'];
            $org['org_name'] = $this->orgDataByCode()[$org['org']] ?? '';
            $org['dept_name'] = $this->orgDataByCode()[$org['dept']] ?? '';
            foreach ($org as $field=>$item){
                if(!in_array($field, ['org','dept','org_name','dept_name','username','name','id'])){
                    unset($org[$field]);
                }
            }
            if($org['org'] && empty($org['dept'])){
                $orgData[$org['org']][] = $org;
            }
            if($org['dept']){
                $orgData[$org['dept']][] = $org;
            }
        }
        $this->userData = $orgData;
    }

    /*
     * 组装组织数据
     * */
    private function formatOrgData($startOrg = ''){
        $list = $this->allOrg();
        $orgData = $startData = [];
        foreach ($list as $org){
            $org['id'] = $org['org_id'];
            $orgData[$org['parent_org']][] = $org;
            if($org['parent_org'] == '~' || empty($org['parent_org'])){
                $startData = $org;
            }

            if($org['org'] == $startOrg){
                $this->startOrgType = $org['org_type'];
                $startData = $org;
            }
        }
        $this->orgData = $orgData;
        $startData['child'] = $this->diffOrgDept($orgData[$startData['org']] ?? []);
        return $startData;
    }

    /*
     * 循环处理组织架构
     * */
    private function foreachOrgData($startData){
        $this->formatUserData();
        $this->recursionData($startData['child'], $startData);
        return $startData;
    }

    /*
     * 递归每一条组织数据,查找下面的child
     * */
    private function recursionData(&$children, &$startData){
        //保存同级及以下部门ID集合和组织ID集合
        $this->setCommonOrgsAndDepts($startData);
        array_walk($children, function (&$org){
            //组合起来的部门，是最后一级
            if($org['org_type'] == $this->virtualOrgType){
                if($org['child']){
                    array_walk($org['child'], function (&$org){
                        //保存同级及以下部门ID集合和组织ID集合
                        $this->setCommonOrgsAndDepts($org);
                        //是否填充人员信息
                        $this->getCurrentUserList($org);
                    });
                }
                return;
            }
            //保存同级及以下部门ID集合和组织ID集合
            $this->setCommonOrgsAndDepts($org);
            if(!$org['child']){
                $org['child'] = $this->diffOrgDept($this->orgData[$org['org']] ?? []);
                if($org['child']){
                    $this->recursionData($org['child'], $startData);
                }
            }

            //是否填充人员信息
            $this->getCurrentUserList($org);
        });

        if(!$children){
            //考虑末级没有child,但是也需要填充人员时候时候
            $this->getCurrentUserList($startData);
        }
    }

    /*
     * 保存同级及以下部门ID集合和组织ID集合
     * */
    protected function setCommonOrgsAndDepts($org){
        //保存同级及以下部门ID集合
        $this->setCommonDepts($org);
        //保存同级及以下组织ID集合
        $this->setCommonOrgs($org);
    }

    /*
     * 获取当前节点的人员列表
     * */
    protected function getCurrentUserList(&$subOrg){
        //是否填充人员信息
        if($this->hasUsers){
            $subOrg['child'] = $subOrg['child'] ?? [];
            $this->userData[$subOrg['org']] = $this->userData[$subOrg['org']] ?? [];
            foreach($this->userData[$subOrg['org']] as $listChild) {
                $subOrg['child'][] = $listChild;
            }
        }
    }

    /**
     * 区分同级的组织 部门，然后部门包装在一起打包展示
     * @return array
     */
    protected function diffOrgDept($orgs){
        $depts = [];
        foreach ($orgs as $index=>$org){
            if($org['org_type'] == 1){
                //属于部门
                $depts[] = $org;
                unset($orgs[$index]);
            }
        }
        if(empty($depts)){
            return $this->sortOrgDept($orgs);
        }
        $this->virtualId++;
        $virtualId = $this->virtualId;
        return $this->sortOrgDept(array_merge([['id'=>$virtualId, 'org_id'=>$virtualId, 'org'=>uniqid(), 'parent_org'=>uniqid(), 'org_type'=>$this->virtualOrgType, 'name'=>'部门', 'child'=>$depts]], $orgs));
    }

    /**
     * 排序，把领导领班提到第一位
     * @return array
     */
    protected function sortOrgDept($orgs){
        if($orgs){
            $name = '领导班子';
            $names = array_column($orgs, 'name');
            if(in_array($name, $names)){
                //如何本层次存在领导班子,就执行排序
                foreach ($orgs as $index => $org){
                    if($org['name'] === $name){
                        $first = $org;
                        unset($orgs[$index]);
                        break;
                    }
                }
                $orgs = array_merge([$first], $orgs);
            }
        }
        return $orgs;
    }

    /**
     * 保存同级及以下组织ID集合
     * @return array
     */
    protected function setCommonOrgs($startData){
        if($this->startOrgType == 0){
            //属于组织
            if($startData['org'] && $startData['org_type'] == 0){
                $this->commonOrgs[] = $startData['org_id'];
            }
        }
    }

    /**
     * 保存同级及以下部门ID集合
     * @return array
     */
    protected function setCommonDepts($startData){
        if($this->startOrgType == 1){
            //属于部门
            if($startData['org'] && $startData['org_type'] == 1){
                $this->commonDepts[] = $startData['org_id'];
            }
        }
    }

    /** 组织部门数据
     *
     * @return array
     *  ['0001A110000000002ZF0'=>['name'=>'中建科工集团']]
     * @throws \Vss\Exceptions\JsonResponseException
     */
    public function orgDataByCode()
    {
        if(self::$orgDataByCode){
            return self::$orgDataByCode;
        }
        return self::$orgDataByCode = array_column($this->allOrg(), 'name', 'org');
    }

    /** 组织部门数据
     *
     * @return array
     *  ['0001A110000000002ZF0'=>['org_id'=>'1']]
     * @throws \Vss\Exceptions\JsonResponseException
     */
    public function orgIdByCode()
    {
        if(self::$orgIdByCode){
            return self::$orgIdByCode;
        }
        return self::$orgIdByCode = array_column($this->allOrg(), 'org_id', 'org');
    }

    /** 组织部门数据
     *
     * @return array
     *  ['0001A110000000002ZF0'=>['name'=>'中建科工集团']]
     * @throws \Vss\Exceptions\JsonResponseException
     */
    public function orgNameByOrgId()
    {
        if(self::$orgNameByOrgId){
            return self::$orgNameByOrgId;
        }
        return self::$orgNameByOrgId = array_column($this->allOrg(), 'name', 'org_id');
    }

    /** 所有组织部门数据
     *
     * @return array
     */
    public function allOrg()
    {
        return (array)vss_model()->getAccountOrgModel()->getCscesAllOrg();
    }

    /** 所有用户数据
     *
     * @return array
     */
    public function allUser()
    {
        return (array)vss_model()->getAccountsModel()->getCscesAllUser();
    }
}
