<?php

namespace App\Component\account\src\services;
use App\Component\account\src\constants\AccountOrgConstant;
use Vss\Common\Services\WebBaseService;
/**
 * RoomFormatService
 */
class AccountFormatService extends WebBaseService
{
    protected $source = '';
    public function formatList($list){
        $orglist = vss_service()->getAccountOrgService()->orgDataByCode();
        $list = (array)$list->toArray();
        $list['data'] = array_map(function ($item) use($orglist){
            $item['org_name'] = $orglist[$item['org']] ?? '';
            $item['dept_name'] = $orglist[$item['dept']] ?? '';
            return $item;
        }, (array)$list['data']);
        return $list;
    }

    /*
     * 获取列表的组织 部门搜索条件
     * */
    public function getCommonDeptOrOrg(&$dept, &$org, $accountInfo, $source = 'room'){
        $this->source = $source;
        if(empty($dept) && empty($org)){
            $orgIdByCode = vss_service()->getAccountOrgService()->orgIdByCode();
            if(empty($accountInfo['dept'])){
                $orgId = $orgIdByCode[$accountInfo['org']];
                $orgsDepts = $this->getDeptsOrOrgs($orgId);
                $org = $orgsDepts['orgs'] ?: '';
                $dept = $orgsDepts['depts'] ?: '';
            }
            if(!empty($accountInfo['dept'])){
                $orgId = $orgIdByCode[$accountInfo['dept']];
                $orgsDepts = $this->getDeptsOrOrgs($orgId);
                $org = $orgsDepts['orgs'] ?: '';
                $dept = $orgsDepts['depts'] ?: '';
            }
            return;
        }

        if($dept){
            $orgsDepts = $this->getDeptsOrOrgs($dept);
            $org = $orgsDepts['orgs'] ?: '';
            $dept = $orgsDepts['depts'] ?: '';
        }

        if($org){
            $orgsDepts = $this->getDeptsOrOrgs($org);
            $org = $orgsDepts['orgs'] ?: '';
            $dept = $orgsDepts['depts'] ?: '';
        }
    }

    /*
     * 获取部门或者组织ID
     * */
    public function getDeptsOrOrgs($orgId){
        $orgInfo = vss_model()->getAccountOrgModel()->getInfoByOrgId($orgId);
        if($orgInfo->org_type == AccountOrgConstant::ORG_TYPE_ORG){
            $org = explode(',', $orgInfo->orgs);
            if($this->source == 'account'){
                $org = $this->getDeptOrOrgCodes($org);
            }
        }
        if($orgInfo->org_type == AccountOrgConstant::ORG_TYPE_DEPT){
            $dept = explode(',', $orgInfo->depts);
            if($this->source == 'account'){
                $dept = $this->getDeptOrOrgCodes($dept);
            }
        }
        return ['orgs'=>$org, 'depts'=>$dept];
    }

    /*
     * 获取组织的org码
     * */
    protected function getDeptOrOrgCodes($orgIds){
        $orgList = vss_model()->getAccountOrgModel()->getInfoByOrgIds($orgIds);
        return array_column($orgList, 'org');
    }
}
