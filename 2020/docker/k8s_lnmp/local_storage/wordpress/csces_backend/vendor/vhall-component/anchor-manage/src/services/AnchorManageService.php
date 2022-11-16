<?php

namespace vhallComponent\anchorManage\services;

use App\Constants\ResponseCode;
use vhallComponent\account\constants\AccountConstant;
use Vss\Common\Services\WebBaseService;

/**
 * Class AnchorManageService
 * @authro wei.yang@vhall.com
 * @date 2021/6/15
 */
class AnchorManageService extends WebBaseService
{


    /**
     * 主播登录状态存储redis前缀
     */
    const ANCHOR_LOGIN_PREFIX = 'anchor_login_prefix_';

    /**
     * 主播登录过期时间
     */
    const ANCHOR_LOGIN_EXPIRE = 3600 * 24;


    /**
     * 分页查询所有主播
     *
     * @param integer $page      页码
     * @param integer $pageSize  每页数量
     * @param mixed   $search    搜索参数
     * @param integer $accountId 账户id
     * @author wei.yang@vhall.com
     * @date   2021/6/11
     */
    public function getListByPage($page, $pageSize, $search, $accountId)
    {
        $model = vss_model()->getAnchorManageModel()->newQuery();
        $model->where('account_id', $accountId);
        if ($search) {
            if (is_numeric($search) && preg_match('/^1.*/', $search)) {
                $model->where('anchor_id', $search);
            } else {
                $model->where('nickname', 'like', "%{$search}%");
            }
        }
        return $model->orderBy('anchor_id', 'desc')->paginate($pageSize, ['*'], 'page', $page);
    }

    /**
     * 主播详情
     *
     * @param integer $anchorId 主播id
     * @author wei.yang@vhall.com
     * @date   2021/6/15
     */
    public function getAnchorInfo($anchorId)
    {
        return vss_model()->getAnchorManageModel()->find($anchorId);
    }

    /**
     * APP端获取主播详情
     *
     * @param integer $phone 电话
     * @author wei.yang@vhall.com
     * @date   2021/6/16
     */
    public function getAnchorInfoInApp($phone = '')
    {
        $model = vss_model()->getAnchorManageModel()
            ->selectRaw('account_id, anchor_id, nickname, avatar')
            ->where('phone', $phone)
            ->first();
        return $model ? $model->toArray() : [];
    }

    /**
     * 通过房间id获取关联主播信息
     *
     * @param integer $ilId 主播id
     * @return \vhallComponent\anchorManage\models\AnchorRoomLkModel
     * @author wei.yang@vhall.com
     * @date   2021/6/17
     */
    public function getAnchorIdByIlId($ilId)
    {
        return vss_model()->getAnchorRoomLkModel()
            ->where('il_id', $ilId)
            ->first();
    }

    /**
     * 创建主播
     *
     * @param integer $accountId 账户id
     * @param array   $params    参数
     * @param string  $avatar    头像地址
     * @author wei.yang@vhall.com
     * @date   2021/6/15
     */
    public function create($accountId, $params, $avatar)
    {
        $exist = vss_model()->getAnchorManageModel()
            ->where('phone', $params['phone'])
            ->exists();
        if ($exist) {
            $this->fail(ResponseCode::BUSINESS_REPEAT_CREATE);
        }
        $data = array(
            'account_id' => $accountId,
            'nickname' => $params['nickname'],
            'real_name' => $params['real_name'],
            'phone' => $params['phone'],
            'avatar' => $avatar
        );
        return vss_model()->getAnchorManageModel()->create($data);
    }

    /**
     * 编辑主播
     *
     * @param integer $accountId 账户id
     * @param array   $params    参数
     * @param string  $avatar    头像地址
     * @author wei.yang@vhall.com
     * @date   2021/6/15
     */
    public function update($accountId, $params, $avatar)
    {
        $data = array(
            'nickname' => $params['nickname'],
            'real_name' => $params['real_name'],
        );
        if ($avatar) {
            $data['avatar'] = $avatar;
        }
        return vss_model()->getAnchorManageModel()
            ->where('account_id', $accountId)
            ->where('anchor_id', $params['anchor_id'])
            ->update($data);
    }

    /**
     * 检查主播是否关联房间
     *
     * @param string $anchorId 主播id，1,2,3,4这种格式
     * @author wei.yang@vhall.com
     * @date   2021/6/16
     */
    public function checkLink($anchorId)
    {
        $anchorIds = explode(',', $anchorId);
        $row = vss_model()->getAnchorManageModel()
            ->whereIn('anchor_manage.anchor_id', $anchorIds)
            ->join('anchor_room_lk', 'anchor_manage.anchor_id', 'anchor_room_lk.anchor_id')
            ->join('rooms', 'anchor_room_lk.il_id', 'rooms.il_id')
            ->selectRaw('anchor_manage.nickname as anchor_nickname, rooms.subject as room_name, anchor_manage.anchor_id')
            ->first();

        return $row ? $row->toArray() : [];
    }

    /**
     * 删除主播
     *
     * @param string $anchorId   主播id，1,2,3,4这种格式
     * @param integer $accountId 账户id
     * @author wei.yang@vhall.com
     * @date   2021/6/16
     */
    public function deleteAnchor($anchorId, $accountId)
    {
        $check = $this->checkLink($anchorId);
        if ($check) {
            $this->fail(ResponseCode::COMP_ANCHOR_EXIST_ROOM_LK);
        }
        $anchorIds = explode(',', $anchorId);
        return vss_model()->getAnchorManageModel()
            ->whereIn('anchor_id', $anchorIds)
            ->where('account_id', $accountId)
            ->forceDelete();
    }

    /**
     * 关联房间和主播
     *
     * @param integer $anchorId 主播id
     * @param integer $ilId     房间id
     * @author wei.yang@vhall.com
     * @date   2021/6/16
     */
    public function linkAnchorRoom($anchorId, $ilId)
    {
        $anchor = $this->getAnchorInfo($anchorId);
        if ($anchor) {
            $exist = vss_model()->getAnchorRoomLkModel()
                ->where('il_id', $ilId)
                ->exists();
            if (!$exist) {
                $data = [
                    'anchor_id' => $anchorId,
                    'il_id' => $ilId,
                ];
                vss_model()->getAnchorRoomLkModel()->create($data);
            }
        }
    }

    /**
     * 修改主播房间的关联关系
     *
     * @param integer $anchorId 主播id
     * @param integer $ilId     房间id
     * @throws \Vss\Exceptions\JsonResponseException
     * @author wei.yang@vhall.com
     * @date   2021/6/16
     */
    public function modifyLink($anchorId, $ilId)
    {
        $anchor = $this->getAnchorInfo($anchorId);
        if ($anchor) {
            $exist = vss_model()->getAnchorRoomLkModel()
                ->where('il_id', $ilId)
                ->exists();
            if ($exist) {
                $data = [
                    'anchor_id' => $anchorId,
                ];
                vss_model()->getAnchorRoomLkModel()
                    ->where('il_id', $ilId)
                    ->update($data);
            } else {
                $this->linkAnchorRoom($anchorId, $ilId);
            }
        }
    }

    /**
     * 取消关联主播
     *
     * @param integer $ilId 房间id
     * @return \vhallComponent\anchorManage\models\AnchorRoomLkModel
     * @author wei.yang@vhall.com
     * @date   2021/6/18
     */
    public function deleteLink($ilId)
    {
        return vss_model()->getAnchorRoomLkModel()
            ->where('il_id', $ilId)
            ->forceDelete();
    }

    /**
     * 检查主播手机号是否存在
     *
     * @param integer $phone 手机号
     * @author wei.yang@vhall.com
     * @date   2021/6/16
     */
    public function checkAnchorByPhone($phone)
    {
        $bool = vss_model()->getAnchorManageModel()
            ->where('phone', $phone)
            ->exists();
        if (!$bool) {
            $this->fail(ResponseCode::COMP_ANCHOR_PHONE_NOT_EXIST);
        }
    }

    /**
     * 短信风控，一个ip，一个手机号
     * 一分钟不超过10次
     *
     * @param string  $ip    地址
     * @param integer $phone 电话
     * @throws \Vss\Exceptions\JsonResponseException
     * @author wei.yang@vhall.com
     * @date   2021/6/18
     */
    public function smsRiskControl($ip, $phone)
    {
        $limitTimes = 10;
        $expire     = 60;
        $key = sprintf("anchor_sms_risk_%s_%s", $phone, $ip);
        $num = vss_redis()->get($key);
        if ($num) {
            vss_redis()->incr($key);
            if ($num +1 > $limitTimes) {
                $this->fail(ResponseCode::COMP_ANCHOR_SMS_RISK);
            }
        } else {
            vss_redis()->set($key, 1, $expire);
        }
    }

    /**
     * 直播间状态判断
     *
     * @param integer $phone 电话
     * @author wei.yang@vhall.com
     * @date   2021/6/25
     * @throws \Vss\Exceptions\JsonResponseException
     */
    public function roomStatusCheck($phone)
    {
        $existRoom = vss_model()->getAnchorManageModel()
            ->where('phone', $phone)
            ->join('anchor_room_lk', 'anchor_manage.anchor_id', 'anchor_room_lk.anchor_id')
            ->join('rooms', 'anchor_room_lk.il_id', 'rooms.il_id')
            ->where('rooms.status', 1)
            ->exists();
        if ($existRoom) {
            $this->fail(ResponseCode::COMP_ANCHOR_IS_LIVING);
        }
    }

    /**
     * 登录
     *
     * @param integer $phone 手机号
     * @param integer $code  验证码
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Vss\Exceptions\JsonResponseException
     * @author wei.yang@vhall.com
     * @date   2021/6/16
     */
    public function login($phone, $code)
    {
        $bool = vss_service()->getCodeService()->checkCode($phone, $code);
        if (!$bool) {
            $this->fail(ResponseCode::AUTH_VERIFICATION_CODE_ERROR);
        }
        $existToken = vss_redis()->get(self::ANCHOR_LOGIN_PREFIX . $phone);
        if ($existToken) {
            // 踢出之前的登录
            $this->logout($existToken, $phone);
        }

        $token = $this->getToken($phone);
        $anchorInfo = $this->getAnchorInfoInApp($phone);

        // 用同一个token兼容主播管理和原来的登录逻辑
        $accountInfo = vss_service()->getAccountsService()->getOne(['account_id' => $anchorInfo['account_id']]);
        $accountInfo = $accountInfo->toArray();
        if ($accountInfo['status'] == AccountConstant::STATUS_DISABLED) {
            $this->fail(ResponseCode::AUTH_LOGIN_ACCOUNT_DISABLE);
        }
        $accountInfo['anchor_phone'] = $phone;
        $accountInfo['token']        = $token;
        $accountInfo['modules']      = AccountConstant::ALLOW_MODULES;

        vss_redis()->set(self::ANCHOR_LOGIN_PREFIX . $phone, $token, self::ANCHOR_LOGIN_EXPIRE);
        vss_redis()->set($token, json_encode($accountInfo), self::ANCHOR_LOGIN_EXPIRE);

        return $accountInfo;
    }

    /**
     * 退出登录
     *
     * @param string  $token token
     * @param integer $phone 电话
     * @return bool
     * @author wei.yang@vhall.com
     * @date   2021/6/21
     */
    public function logout($token, $phone = '')
    {
        vss_redis()->del($token);
        vss_redis()->del(self::ANCHOR_LOGIN_PREFIX . $phone);
        return true;
    }

    /**
     * 获取Token字符串
     *
     * @param integer $phone 手机号
     *
     * @return bool|string
     */
    private function getToken($phone)
    {
        return substr(md5(rand(1000, 9999) . time() . $phone . rand(1000, 9999)), 8, 16);
    }

    /**
     * 主播关联的直播房间列表
     *
     * @param integer $phone    电话
     * @param integer $page     页码
     * @param integer $pageSize 数量
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @author wei.yang@vhall.com
     * @date   2021/6/16
     */
    public function liveList($phone, $page, $pageSize)
    {
        return vss_model()->getAnchorManageModel()
            ->where('phone', $phone)
            ->join('anchor_room_lk', 'anchor_manage.anchor_id', 'anchor_room_lk.anchor_id')
            ->join('rooms', 'anchor_room_lk.il_id', 'rooms.il_id')
            ->orderBy('rooms.start_time', 'desc')
            ->paginate($pageSize, ['rooms.*'], 'page', $page);
    }

    /**
     * 检查主播和直播间是否存在关联关系
     *
     * @param integer $ilId  主播id
     * @param integer $phone 电话
     * @return bool
     * @author wei.yang@vhall.com
     * @date   2021/6/16
     */
    public function checkRelation($ilId, $phone)
    {
        return vss_model()->getAnchorManageModel()
            ->where('phone', $phone)
            ->join('anchor_room_lk', 'anchor_manage.anchor_id', 'anchor_room_lk.anchor_id')
            ->where('anchor_room_lk.il_id', $ilId)
            ->exists();
    }

    /**
     * 主播修改昵称
     *
     * @param string  $nickname 昵称
     * @param integer $phone    电话
     * @return bool|int
     * @author wei.yang@vhall.com
     * @date   2021/6/16
     */
    public function updateNickname($nickname, $phone)
    {
        return vss_model()->getAnchorManageModel()
            ->where('phone', $phone)
            ->update(['nickname' => $nickname]);
    }
}
