<?php

namespace App\Component\account\src\models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use vhallComponent\decouple\models\WebBaseModel;

/**
 * AccountOrgModel
 * @property int $id
 * @property int $code         组织编码
 * @property string  $name      组织名称
 * @property string  $parent_org      上级组织id
 * @property bool $org           组织id
 * @property string  $org_type         类型0组织 1部门
 * @property bool $org_id        中建自增ID
 * @property string  $updated_at
 * @property string  $deleted_at
 * @property string  $orgs
 * @property string  $depts
 */
class AccountOrgModel extends WebBaseModel
{
    protected $cacheExpire = [
        'getInfoByOrgId'=>86400,
        'getInfoByOrgIds'=>3600,
    ];

    protected $table = 'account_org';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','code','name','parent_org','org','org_type','org_id','updated_at','created_at','deleted_at','orgs','depts'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->deleteCache('CscesAllOrg', 'all');
            $model->putCache('getInfoByOrgId', $model->org_id, $model->getAttributes());
        });
        self::updated(function (self $data) {
            $data->deleteCache('CscesAllOrg', 'all');
            $data->putCache('getInfoByOrgId', $data->org_id, $data->getAttributes());
            if($data->deleted_at){
                $data->deleteCache('getInfoByOrgId', $data->org_id);
            }
        });
        self::saved(function (self $data) {
            $data->deleteCache('CscesAllOrg', 'all');
            $data->putCache('getInfoByOrgId', $data->org_id, $data->getAttributes());
            if($data->deleted_at){
                $data->deleteCache('getInfoByOrgId', $data->org_id);
            }
        });
        self::created(function (self $data) {
            $data->deleteCache('CscesAllOrg', 'all');
            $data->putCache('getInfoByOrgId', $data->org_id, $data->getAttributes());
        });
        self::deleted(function (self $data) {
            $data->deleteCache('CscesAllOrg', 'all');
            $data->deleteCache('getInfoByOrgId', $data->org_id);
        });
    }

    /**
     * 新增记录
     *
     * @param array $attributes
     *
     * @return AccountsModel
     * @author ensong.liu@vhall.com
     * @date   2019-02-16 21:05:41
     */
    public function addRow(array $attributes)
    {
        return $this->create($attributes);
    }

    /**
     * 获取组织信息 通过org_id
     * @param $orgId|int
     * @return AccountOrgModel|null
     */
    public function getInfoByOrgId($orgId)
    {
        $attributes = $this->getCache('getInfoByOrgId', $orgId, function () use ($orgId) {
            $model = $this->where('org_id', $orgId)->first();
            return $model ? $model->getAttributes() : null;
        });

        return empty($attributes) ? null : $this->newInstance($attributes, true)->syncOriginal();
    }

    /**
     * 获取组织信息 通过org_id
     * @param $orgIds|array
     * @return AccountOrgModel|null
     */
    public function getInfoByOrgIds($orgIds)
    {
        $key = md5(implode('', $orgIds));
        if(empty($orgIds)){
            return [];
        }
        $attributes = $this->getCache('getInfoByOrgIds', $key, function () use ($orgIds) {
            $model = $this->whereIn('org_id', $orgIds)->get(['code','name','parent_org','org','org_type','org_id'])->toArray();
            return $model ? $model : null;
        });

        return empty($attributes) ? [] : $attributes;
    }

    /**
     * 修改
     * @param $condition
     * @param $update
     * @return bool|Model|AccountsModel
     */
    public function updateInfo($condition, $update)
    {
        $model = $this->getRow($condition);
        if ($model) {
            array_walk($update, function ($value, $key) use ($model) {
                $model->setAttribute($key, $value);
            });
            $model->update();
        }
        return $model ? $model : false;
    }

    /**
     * 获取中建所有组织架构信息
     * @return AccountsModel|null
     */
    public function getCscesAllOrg()
    {
        $attributes = $this->getCache('CscesAllOrg', 'all', function (){
            return vss_model()->getAccountOrgModel()->orderBy('org_id', 'asc')->get(['org_id','org','parent_org','name','org_type'])->toArray();
        });
        return $attributes;
    }

    /**
     * 列表/总数条件构造器
     *
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 10:20:49
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param  array $condition
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildCondition(Builder $model, array $condition) :Builder
    {
        $model = parent::buildCondition($model, $condition);
        //关键字
        $model->when($condition['keyword'], function ($query) use ($condition) {
            $query->where(function ($query) use ($condition) {
                $query->where('accounts.phone', 'like', sprintf('%%%s%%', $condition['keyword']))
                    ->orWhere('accounts.username', 'like', sprintf('%%%s%%', $condition['keyword']))
                    ->orWhere('accounts.nickname', 'like', sprintf('%%%s%%', $condition['keyword']));
            });
        });
        //时间范围-开始
        $model->when($condition['begin_time'], function ($query) use ($condition) {
            $query->where('accounts.created_at', '>=', $condition['begin_time']);
        });
        //时间范围-结束
        $model->when($condition['end_time'], function ($query) use ($condition) {
            $query->where('accounts.created_at', '<=', date('Y-m-d 23:59:59', strtotime($condition['end_time'])));
        });

        return $model;
    }
}
