<?php

namespace App\Component\room\src\services\invited;
use App\Component\room\src\constants\RoomInvitedConstant;
use App\Component\room\src\constants\RoomJoinRoleNameConstant;
use App\Constants\ResponseCode;
use Vss\Common\Services\WebBaseService;

/**
 * RoomInvitedServiceTrait
 */
class RoomInvitedService extends WebBaseService
{
    /*
     * 我参加的会议-关系创建
     * */
    public function createAudienceInvited($ilId, array $params)
    {
        //先删除一遍再重新生成(强制删除)
        vss_model()->getRoomInvitedModel()->deleteByIlidAndRoomrole($ilId, RoomJoinRoleNameConstant::USER);

        //观众
        if($params['audience_ids']){
            $audiences = explode(',', $params['audience_ids']);
            if(count($audiences) > RoomInvitedConstant::INVITED_AUDIENCE_MAX){
                $this->fail(ResponseCode::INVITED_AUDIENCE_MAX);
            }
            array_walk($audiences, function ($accountId) use($ilId){
                vss_model()->getRoomInvitedModel()->createRoomInvited($ilId, $accountId, RoomJoinRoleNameConstant::USER);
            });
        }
    }

    /*
     * 我参加的会议-关系创建
     * */
    public function createRoomInvited($ilId, array $params)
    {
        //先删除一遍再重新生成(强制删除)
        $roomInvited = vss_service()->getRoomInvitedService()->getAll([
            'il_id' => $ilId
        ], [])->toArray();
        array_walk($roomInvited, function ($invite)use($ilId){
            vss_model()->getRoomInvitedModel()->deleteInvitedById($invite['id']);
            vss_model()->getRoomInvitedModel()->delCacheByAccountIdAndIlid($ilId . $invite['account_id']);
        });
        /*foreach ($roomInvited as $invite){
            vss_model()->getRoomInvitedModel()->deleteInvitedById($invite['id']);
            vss_model()->getRoomInvitedModel()->delCacheByAccountIdAndIlid($ilId . $invite['account_id']);
        }*/
        //vss_model()->getRoomInvitedModel()->deleteInvitedByIlId($ilId);
        //主持人
        vss_model()->getRoomInvitedModel()->createRoomInvited($ilId, $params['account_id'], RoomJoinRoleNameConstant::HOST);
        //观众
        $audiences = $guests = $assistants = [];
        if($params['audience_ids']){
            $audiences = array_unique(explode(',', $params['audience_ids']));
        }
        //嘉宾
        if($params['guest_ids']){
            $guests = array_unique(explode(',', $params['guest_ids']));
        }
        //助理
        if($params['assistant_ids']){
            $assistants = array_unique(explode(',', $params['assistant_ids']));
        }
        $totalLists = array_unique(array_merge($guests, $assistants, $audiences));
        if(in_array($params['account_id'], $totalLists)){
            $this->fail(ResponseCode::BUSINESS_REPEAT_INVITATION);
        }
        if(count($totalLists) != (count($guests) + count($assistants) + count($audiences))){
            $this->fail(ResponseCode::BUSINESS_REPEAT_INVITATION);
        }
        //观众
        if($audiences){
            if(count($audiences) > RoomInvitedConstant::INVITED_AUDIENCE_MAX){
                $this->fail(ResponseCode::INVITED_AUDIENCE_MAX);
            }
            array_walk($audiences, function ($accountId) use($ilId){
                vss_model()->getRoomInvitedModel()->createRoomInvited($ilId, $accountId, RoomJoinRoleNameConstant::USER);
            });
        }

        //嘉宾
        if($guests){
            if(count($guests) > RoomInvitedConstant::INVITED_GUEST_MAX){
                $this->fail(ResponseCode::INVITED_GUEST_MAX);
            }
            array_walk($guests, function ($accountId) use($ilId){
                vss_model()->getRoomInvitedModel()->createRoomInvited($ilId, $accountId, RoomJoinRoleNameConstant::GUEST);
            });
        }

        //助理
        if($assistants){
            if(count($assistants) > RoomInvitedConstant::INVITED_ASSISTANT_MAX){
                $this->fail(ResponseCode::INVITED_ASSISTANT_MAX);
            }
            array_walk($assistants, function ($accountId) use($ilId){
                vss_model()->getRoomInvitedModel()->createRoomInvited($ilId, $accountId, RoomJoinRoleNameConstant::ASSISTANT);
            });
        }
    }

    /*
     * 获取通知的嘉宾用户,助理信息
     * */
    public function getNoticeUserInfo($ilId){
        return vss_model()->getRoomInvitedModel()->where('il_id', $ilId)->whereIn('room_role', [3, 4])->with('accounts')->get(['account_id','room_role'])->toArray();
    }

    public function getAll(array $where, $with = [])
    {
        $roomModel = vss_model()->getRoomInvitedModel()->where($where)->with($with)->get();
        return $roomModel;
    }

    public function getRow(array $where, $with = [])
    {
        $roomModel = vss_model()->getRoomInvitedModel()->getRow($where, $with);
        return $roomModel;
    }

    public function getCountByFilter($condition){
        return $this->invitedCount($condition);
    }

    public function getCount(
        $ilId,
        $keyword,
        $beginTime,
        $endTime,
        $status,
        $page,
        $pageSize,
        $accountId,
        $roomRole = RoomJoinRoleNameConstant::ROLES_WITHOUT_HOST,
        $keyName = ''
    )
    {
        //列表数据
        $condition = [
            'keyword'    => $keyword,
            'created'    => [$beginTime, $endTime . ' 23:59:59'],
            'status'     => $status,
            'il_id'      => $ilId,
            'account_id' => $accountId,
            'room_role_ids'  => $roomRole,
        ];
        return $this->invitedCount($condition, $page, $pageSize, $keyName);
    }

    /**
     * 获取数量
     */
    public function invitedCount($condition)
    {
        $model = vss_model()->getRoomInvitedModel();
        return $model->getCount($condition);
    }

    public function getInvitedList($condition, $page, $pageSize, $orderKey){
        return $this->roomList($condition, $page, $pageSize, $orderKey);
    }

    public function getList(
        $ilId,
        $keyword,
        $beginTime,
        $endTime,
        $status,
        $page,
        $pageSize,
        $accountId,
        $roomRole = RoomJoinRoleNameConstant::ROLES_WITHOUT_HOST,
        $keyName = ''
    )
    {
        //列表数据
        $condition = [
            'keyword'    => $keyword,
            'created'    => [$beginTime, $endTime . ' 23:59:59'],
            'status'     => $status,
            'il_id'      => $ilId,
            'invited_account_id' => $accountId,
            'room_role_ids'  => $roomRole,
        ];
        return $this->roomList($condition, $page, $pageSize, $keyName);
    }

    /**
     * 房间列表
     */
    public function roomList($condition, $page = 1, $pageSize = 10, $keyName = '')
    {
        $with  = [];
        $model = vss_model()->getRoomInvitedModel();
        //设置排序字段
        if ($keyName) {
            $model = $model->setKeyName($keyName);
        }
        $fields = ['*'];
        if($condition['source'] == 'invited-list'){
            $fields = ['rooms.*','room_invited.room_role','accounts.nickname'];
        }
        $liveList = $model->setPerPage($pageSize)->getList($condition, $with, $page, $fields);
        return $liveList;
    }

    /**
     * 获取room邀请数据
     */
    public function getInvitesByIlId($ilId){
        return vss_service()->getRoomInvitedService()->getAll([
            'il_id' => $ilId
        ], [])->toArray();
    }

    /**
     * 获取room邀请数据-加人员数据打包
     */
    public function getInvitedAccountInfoByIlId($ilId){
        $data = [];
        $roomInvited = vss_service()->getRoomInvitedService()->getAll([
            'il_id' => $ilId
        ], ['accounts'])->toArray();
        array_walk($roomInvited, function ($item) use(&$data){
            if($item['room_role'] == RoomJoinRoleNameConstant::USER){
                $data['audience_ids'][] = ['account_id'=>$item['account_id'], 'nickname'=>$item['accounts']['nickname'] ?? ''];
            }
            if($item['room_role'] == RoomJoinRoleNameConstant::GUEST){
                $data['guest_ids'][] = ['account_id'=>$item['account_id'], 'nickname'=>$item['accounts']['nickname'] ?? ''];
            }
            if($item['room_role'] == RoomJoinRoleNameConstant::ASSISTANT){
                $data['assistant_ids'][] = ['account_id'=>$item['account_id'], 'nickname'=>$item['accounts']['nickname'] ?? ''];
            }
        });
        return $data;
    }
}
