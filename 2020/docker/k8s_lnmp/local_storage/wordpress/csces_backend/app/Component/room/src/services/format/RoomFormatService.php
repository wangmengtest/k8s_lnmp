<?php

namespace App\Component\room\src\services\format;
use App\Component\room\src\constants\RoomJoinRoleNameConstant;
use Vss\Common\Services\WebBaseService;
/**
 * RoomFormatService
 */
class RoomFormatService extends WebBaseService
{
    /*
     * 列表信息format
     * */
    public function formatList($list, $accountInfo){
        $orglist = vss_service()->getAccountOrgService()->orgNameByOrgId();
        $list = (array)$list->toArray();
        $inviteList = $this->getInvitedListForkeyIlid($accountInfo['account_id'], array_column($list['data'], 'il_id'));
        $list['data'] = array_map(function ($item) use($orglist, $accountInfo, $inviteList){
            /*$item['account_name'] = $item['account']['nickname'] ?? '';
            if($item['nickname']){
                $item['account_name'] = $item['nickname'];
            }*/
            if($accountInfo['account_id'] == $item['account_id']){
                $item['role_name'] = RoomJoinRoleNameConstant::HOST;
            }
            if(isset($inviteList[$item['il_id']])){
                $item['role_name'] = $inviteList[$item['il_id']];
            }else{
                $item['role_name'] = RoomJoinRoleNameConstant::USER;
            }
            $item['org_name'] = $orglist[$item['org']] ?? '';
            $item['dept_name'] = $orglist[$item['dept']] ?? '';
            return $item;
        }, (array)$list['data']);
        return $list;
    }

    /*
     * 详情信息format
     * */
    public function formatDetail($ilId, $data, $accountInfo){
        /*$inviteList = $this->getInvitedListForkeyIlid($accountInfo['account_id'], [$ilId]);
        if(isset($inviteList[$ilId]) && $data['role_name'] == RoomJoinRoleNameConstant::USER){
            $data['role_name'] = $inviteList[$ilId];
        }*/
        if(isset($data['notice_time'])){
            $data['notice_time'] = $data['notice_time'] ?: '';
        }else{
            $data['notice_time'] = '';
        }
        return$data;
    }

    /*
    * 获取在房间内的角色
    * */
    public function getRoleName($ilId, $accountId, $roleName){
        if($roleName == RoomJoinRoleNameConstant::USER){
            $inviteList = $this->getInvitedListForkeyIlid($accountId, [$ilId]);
            if(isset($inviteList[$ilId])){
                return $inviteList[$ilId];
            }
            return '';
        }
    }

    /*
     * 获取邀请信息列表
     * */
    protected function getInvitedListForkeyIlid($accountId, $ilIds){
        $inviteList = vss_model()->getRoomInvitedModel()->getInvitedByAccountIdAndIlids($accountId, $ilIds);
        return array_column($inviteList, 'role_name', 'il_id');
    }
}
