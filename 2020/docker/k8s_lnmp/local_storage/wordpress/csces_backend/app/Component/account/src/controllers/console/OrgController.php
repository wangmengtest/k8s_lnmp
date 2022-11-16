<?php
namespace App\Component\account\src\controllers\console;
use vhallComponent\decouple\controllers\BaseController;
/**
 * OrgController extends BaseController
 */
class OrgController extends BaseController
{
    /**
     * 组织架构数据
     */
    public function listAction()
    {
        //$this->success(vss_service()->getAccountOrgService()->orgList());
        $this->success(vss_service()->getAccountOrgService()->cacheOrgList());
    }

    /**
     * 组织架构数据-不包含人员
     */
    public function listNoneuserAction()
    {
        //$this->success(vss_service()->getAccountOrgService()->orgListNoneUser($this->accountInfo['dept'] ?: $this->accountInfo['org']));
        $this->success(vss_service()->getAccountOrgService()->cacheOrgsNoneUser($this->accountInfo['dept'] ?: $this->accountInfo['org']));
    }

    /**
     * 同级及以下ORG_IDS
     */
    public function commonOrgAction()
    {
        //$this->accountInfo['dept'];
        $org = '1001A11000000006LVB8';
        $orgIds = vss_service()->getAccountOrgService()->getCommonOrgsAndDepts($org);
        //vss_model()->getAccountsModel()->updateRow(1, ['depts'=>implode(',', $orgIds['depts']), 'orgs'=>implode(',', $orgIds['orgs'])]);
        $this->success($orgIds);
    }
}
