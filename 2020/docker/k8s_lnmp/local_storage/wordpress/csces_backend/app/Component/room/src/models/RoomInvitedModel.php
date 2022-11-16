<?php

namespace App\Component\room\src\models;
use App\Component\room\src\constants\RoomConstant;
use App\Component\room\src\constants\RoomInvitedConstant;
use Illuminate\Database\Eloquent\Builder;
use App\Component\account\src\models\AccountsModel;
use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class RoomExtendsModel
 * 房间扩展信息类模型
 * @package App\Models
 * @property int     $id         主键id
 * @property int     $il_id      房间id
 * @property string  $room_role  房间角色
 * @property int     $account_id 用户id
 * @property string  $created_at 创建时间
 * @property string  $updated_at 更新时间
 * @property string  $deleted_at
 *
 * @author  ensong.liu@vhall.com
 * @date    2019-06-09 16:16:35
 * @version v1.0.0
 */
class RoomInvitedModel extends WebBaseModel
{
    protected $table      = 'room_invited';

    protected $primaryKey = 'id';

    protected $cacheExpire = [
        'getInvitedByAccountIdAndIlid' => 86400,
    ];

    protected static function boot()
    {
        self::created(function (RoomInvitedModel $data) {
            $data->deleteCache('getInvitedByAccountIdAndIlid', $data->il_id . $data->account_id);
            vss_service()->getCacheRoomInvitedService()->delCache($data->il_id);
        });
        self::updated(function (self $data) {
            $data->deleteCache('getInvitedByAccountIdAndIlid', $data->il_id . $data->account_id);
            vss_service()->getCacheRoomInvitedService()->delCache($data->il_id);
        });
        self::deleted(function (self $data) {
            $data->deleteCache('getInvitedByAccountIdAndIlid', $data->il_id . $data->account_id);
            vss_service()->getCacheRoomInvitedService()->delCache($data->il_id);
        });
        parent::boot();
    }

    protected $appends = [
        'role_name'
    ];

    public function getRoleNameAttribute(): string
    {
        return $this->room_role;
    }
    /**
     * 模型关联-房间表
     */
    public function rooms()
    {
        return $this->belongsTo(RoomsModel::class, 'il_id', 'il_id');
    }

    /**
     * 模型关联-人员表
     */
    public function accounts()
    {
        return $this->belongsTo(AccountsModel::class, 'account_id', 'account_id');
    }

    public function createRoomInvited($ilId, $accountId, $roomRole)
    {
        $datetime               = date('Y-m-d H:i:s');
        $this->il_id            = $ilId;
        $this->account_id       = $accountId;
        $this->room_role        = $roomRole;
        $this->updated_at       = $datetime;
        $this->created_at       = $datetime;
        $insert                 = $this->save();
        if (!$insert) {
            return false;
        }
        return $this->toArray();
    }

    /**
     * 依据account_id,il_id获取邀请的信息
     *
     * @param $ilId
     *
     * @return $this|null
     */
    public function getInvitedByAccountIdAndIlid($accountId, $ilId)
    {
        $key = $ilId . $accountId;
        $attributes = $this->getCache('getInvitedByAccountIdAndIlid', $key, function () use ($ilId, $accountId) {
            $model = $this->where('il_id', $ilId)->where('account_id', $accountId)->first();
            return $model ? $model->getAttributes() : [];
        });
        return empty($attributes) ? null : $this->newInstance($attributes, true)->syncOriginal();
    }

    public function delCacheByAccountIdAndIlid($key)
    {
        $this->deleteCache('getInvitedByAccountIdAndIlid', $key);
    }

    /**
     * 依据account_id,il_id获取邀请的信息
     *
     * @param $ilId
     *
     * @return $this|null
     */
    public function getInvitedByAccountIdAndIlids($accountId, $ilIds)
    {
        $data = [];
        array_walk($ilIds, function ($ilId) use(&$data, $accountId){
            $item = $this->getInvitedByAccountIdAndIlid($accountId, $ilId);
            if($item){
                $data[] = $item;
            }
        });
        return $data;
    }

    public function deleteInvitedByIlId($ilId)
    {
        $this->where('il_id', $ilId)->forceDelete();
    }

    public function deleteInvitedById($id)
    {
        $this->where('id', $id)->forceDelete();
    }

    public function deleteByIlidAndRoomrole($ilId, $roomRole)
    {
        $this->where('il_id', $ilId)->where('room_role', $roomRole)->forceDelete();
    }

    /**
     * 条件构造器
     */
    protected function buildCondition(Builder $model, array $condition): Builder
    {
        //当前表字段条件构建
        $model = parent::buildCondition($model, $condition);

        $model->when(in_array($condition['source'] ,['invited-list','invited-count']),function (Builder $query) use ($condition) {

            $query->rightJoin(RoomsModel::getInstance()
                    ->getTable() . ' as rooms', 'rooms.il_id', '=', 'room_invited.il_id');

            $query->leftJoin(AccountsModel::getInstance()
                    ->getTable() . ' as accounts', 'rooms.account_id', '=', 'accounts.account_id');

            $query->where('rooms.account_id','!=', $condition['invited_account_id']);

            $query->where(function ($query) use($condition){
                $query->where('room_invited.account_id', $condition['invited_account_id'])
                    ->orWhere('limit_type', $condition['limit_type']);
            });
        });

        $model->when(
            isset($condition['keyword']) && !empty($condition['keyword']),
            function (Builder $query) use ($condition) {
                //$query->leftJoin(AccountsModel::getInstance()
                        //->getTable() . ' as accounts', 'rooms.account_id', '=', 'accounts.account_id')
                $query->where(function (Builder $query) use (
                        $condition
                    ) {
                        $query->where('rooms.il_id', 'like', sprintf('%%%s%%', $condition['keyword']))
                            ->orWhere('rooms.subject', 'like', sprintf('%%%s%%', $condition['keyword']))
                            ->orWhere('accounts.nickname', 'like', sprintf('%%%s%%', $condition['keyword']));
                    });
            }
        );

        $model->when(isset($condition['created']) && !empty($condition['created']), function ($query) use ($condition) {
            if (!empty($condition['created'][0])) {
                if (empty($condition['created'][1])) {
                    $condition['created'][1] = date('Y-m-d H:i:s');
                }
                $query->whereBetween('rooms.created_at', $condition['created']);
            }
        });

        $model->when(
            isset($condition['account_id']) && is_array($condition['account_id']),
            function ($query) use ($condition) {
                $query->whereIn('room_invited.account_id', $condition['account_id']);
            }
        );

        $model->when(isset($condition['room_role_ids']) && is_array($condition['room_role_ids']),
            function ($query) use ($condition) {
                $query->whereIn('room_invited.room_role', $condition['room_role_ids']);
            });

        //时间范围-开始
        $model->when(
            isset($condition['created_at_begin']) && !empty($condition['created_at_begin']),
            function (Builder $query) use ($condition) {
                $query->where('rooms.created_at', '>=', $condition['created_at_begin']);
            }
        );
        //时间范围-结束
        $model->when(
            isset($condition['created_at_end']) && !empty($condition['created_at_end']),
            function (Builder $query) use ($condition) {
                $query->where(
                    'rooms.created_at',
                    '<=',
                    date('Y-m-d 23:59:59', strtotime($condition['created_at_end']))
                );
            }
        );
        if(isset($condition['group'])){
            $model->groupBy($condition['group']);
        }
        if(isset($condition['distinct'])){
            $model->distinct('rooms.il_id');
        }
        return $model;
    }
}
