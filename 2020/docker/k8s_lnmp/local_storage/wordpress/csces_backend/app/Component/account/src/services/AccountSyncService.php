<?php

namespace App\Component\account\src\services;

use App\Component\account\src\constants\AccountConstant;
use App\Component\account\src\constants\AccountOrgConstant;
use Vss\Common\Services\WebBaseService;
use Vss\Utils\HttpUtil;

/**
 * AccountSyncService
 */
class AccountSyncService extends WebBaseService
{
    /*
     * 同步人员
     */
    public function syncUser()
    {
        $response = HttpUtil::post(AccountConstant::SYNC_USER_API, [], null, 600);
        $data     = $response->getData();

        vss_logger()->info('csces-account-sync', ['action'=>AccountConstant::SYNC_USER_API, 'result' => count($data)]); //日志
        if(empty($data)){
            return;
        }
        array_walk($data, function ($info){
            $userInfo =  vss_model()->getAccountsModel()->getRow(['username'=>$info['code'], 'user_type'=>AccountConstant::USER_TYPE_CSCES]);
            $data = self::formatUser($info);
            if($userInfo){
                /*if($userInfo->user_id != $info['userid']){
                    return;
                }*/
                //人员修改了姓名,需要修改自己创建的直播间的姓名
                if($userInfo->nickname != $data['nickname']){
                    vss_logger()->info('csces-account-sync', ['action'=>'updateAccountNameByAccountId', 'new_data' => $data, 'old_data'=>$userInfo]); //日志
                    vss_service()->getRoomService()->updateAccountNameByAccountId($userInfo->account_id, $data['nickname']);
                }
                $countKey = AccountConstant::ACCOUNT_NOTEXISTS_COUNT . $userInfo->account_id;
                if(vss_redis()->exists($countKey)){
                    vss_redis()->del($countKey);
                }
                vss_model()->getAccountsModel()->updateRow($userInfo->account_id, $data);
            }else{
                vss_model()->getAccountsModel()->addRow($data);
            }
        });

        //比对用户集合的差异
        self::diffUser(array_values(array_column($data, 'code')));
    }

    /*
     * 比对用户集合的差异
     * */
    protected static function diffUser($userIds){
        //现在库中的集合
        $dbUsers = vss_service()->getAccountOrgService()->allUser();
        array_walk($dbUsers, function ($info)use($userIds){
            if($info['user_id'] == 0){
                //user_id=0的是测试账号
                return;
            }

            //用户名是否存在
            if(!in_array($info['username'], $userIds)){
                $countKey = AccountConstant::ACCOUNT_NOTEXISTS_COUNT . $info['account_id'];
                if(!vss_redis()->exists($countKey)){
                    vss_redis()->incr($countKey);
                    vss_redis()->expire($countKey, 3600*4);
                }else{
                    vss_redis()->incr($countKey);
                }
                //出现大于3次再标记失效
                if(intval(vss_redis()->get($countKey)) < 3){
                    return;
                }

                $accountModel = vss_model()->getAccountsModel()->getRow(['account_id'=>$info['account_id'], 'user_type'=>AccountConstant::USER_TYPE_CSCES]);
                if (vss_redis()->exists($accountModel->token)) {
                    vss_redis()->del($accountModel->token);
                }
                vss_model()->getAccountsModel()->updateRow($info['account_id'], ['status'=>AccountConstant::STATUS_DISABLED]);
            }
        });
    }

    /*
     * 组装用户数据
     * */
    protected static function formatUser($info){
        $data = [];
        $data['phone'] = trim($info['phone']);
        $data['username'] = trim($info['code'] ?: '');
        $data['nickname'] = trim($info['name'] ?: '');
        $data['org'] = $info['pkOrg'] ?: '';
        $data['org_name'] = $info['orgName'] ?: '';
        $data['dept'] = $info['pkDept'] ?: '';
        $data['role_id'] = $info['roleid'];
        $data['user_id'] = $info['userid'];
        $data['c_user_id'] = $info['cuserId'] ?: '';
        $data['password'] = $info['password'];
        $data['pro_id'] = intval($info['proId']);
        $data['user_type'] = AccountConstant::USER_TYPE_CSCES;
        $data['account_type'] = AccountConstant::TYPE_NULL;
        $data['status'] = AccountConstant::STATUS_ENABLED;
        return $data;
    }

    /*
     * 组装组织数据
     * */
    protected static function formatOrg($info){
        $data = [];
        $data['code'] = $info['code'] ?: '';
        $data['name'] = $info['name'] ?: '';
        $data['parent_org'] = $info['pkFatherorg'] ?: '';
        $data['org'] = $info['pkOrg'] ?: '';
        $data['org_type'] = $info['type'];
        $data['org_id'] = $info['id'];
        return $data;
    }

    /*
     * 同步组织部门
     */
    public function syncOrg()
    {
        $response = HttpUtil::get(AccountConstant::SYNC_ORG_API, [], null, 600);
        $data     = $response->getData();

        vss_logger()->info('csces-account-sync', ['action'=>AccountConstant::SYNC_ORG_API, 'result' => count($data)]); //日志
        if(empty($data)){
            return;
        }
        array_walk($data, function ($info){
            $orgInfo = vss_model()->getAccountOrgModel()->getInfoByOrgId($info['id']);
            $data = self::formatOrg($info);
            if(empty($data['org_id'])){
                return;
            }
            if($orgInfo){
                $countKey = AccountOrgConstant::ORGS_NOTEXISTS_COUNT . $orgInfo->org_id;
                if(vss_redis()->exists($countKey)){
                    vss_redis()->del($countKey);
                }
                vss_model()->getAccountOrgModel()->updateRow($orgInfo->id, $data);
            }else{
                vss_model()->getAccountOrgModel()->addRow($data);
            }
        });

        //比对组织集合的差异
        self::diffOrg(array_column($data, 'id'));
    }

    /*
     * 比对用户集合的差异
     * */
    protected static function diffOrg($orgIds){
        //现在库中的集合
        $dbOrgs = vss_service()->getAccountOrgService()->allOrg();
        array_walk($dbOrgs, function ($info)use($orgIds){
            if(!in_array($info['org_id'], $orgIds)){
                $countKey = AccountOrgConstant::ORGS_NOTEXISTS_COUNT . $info['org_id'];
                if(!vss_redis()->exists($countKey)){
                    vss_redis()->incr($countKey);
                    vss_redis()->expire($countKey, 3600*4);
                }else{
                    vss_redis()->incr($countKey);
                }
                //出现大于23次再标记失效
                if(intval(vss_redis()->get($countKey)) < 3){
                    return;
                }
                vss_model()->getAccountOrgModel()->updateInfo(['org_id'=>$info['org_id']], ['deleted_at'=>date('Y-m-d H:i:s')]);
            }
        });
    }

    /*
     *  同步人员同级及以下部门IDS
     */
    public function syncUserDepts()
    {
        $cscesAll = vss_model()->getAccountsModel()->where('user_type', AccountConstant::USER_TYPE_CSCES)->get(['account_id','dept','org'])->toArray();
        array_walk($cscesAll, function ($user, $key){
            //如何dept有值,需要查询同级及以下的部门
            if(!empty($user['dept']) || !empty($user['org'])){
                $org = $user['dept'] ?: $user['org'];
                $orgIds = vss_service()->getAccountOrgService()->getCommonOrgsAndDepts($org);
                if(!empty($orgIds)){
                    vss_model()->getAccountsModel()->updateRow($user['account_id'], ['depts'=>implode(',', $orgIds['depts']), 'orgs'=>implode(',', $orgIds['orgs'])]);
                }
            }
        });
    }

    /*
     *  同步组织架构同级及以下部门IDS
     */
    public function syncOrgsDepts()
    {
        $cscesAll = vss_service()->getAccountOrgService()->allOrg();
        array_walk($cscesAll, function ($user, $key){
            //如何dept有值,需要查询同级及以下的部门
            if(!empty($user['dept']) || !empty($user['org'])){
                $org = $user['dept'] ?: $user['org'];
                $orgIds = vss_service()->getAccountOrgService()->getCommonOrgsAndDepts($org);
                vss_logger()->info('csces-account-orgs-depts', ['action'=>'syncOrgsDepts', 'user'=>$user, 'result' => $orgIds]); //日志
                /*if(empty($orgIds)){
                }*/
                vss_model()->getAccountOrgModel()->where(['org_id'=>$user['org_id']])->update(['depts'=>implode(',', $orgIds['depts']), 'orgs'=>implode(',', $orgIds['orgs'])]);
            }
        });
    }
}
