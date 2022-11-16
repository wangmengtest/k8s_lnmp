<?php

namespace vhallComponent\account\models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use vhallComponent\account\constants\AccountConstant;
use vhallComponent\decouple\models\WebBaseModel;

/**
 * AccountsModel
 *
 * @property int $account_id
 * @property int $phone         手机号码
 * @property string  $username      用户名
 * @property string  $nickname      昵称
 * @property bool $sex           性别>0|女,1|男
 * @property string  $token         登录标识
 * @property bool $status        状态>-1|封停,0|正常
 * @property string  $profile_photo 头像
 * @property bool $account_type  用户类型：1--发起端；2--观看端；
 * @property string  $third_user_id 三方用户id
 * @property string  $updated_at
 * @property string  $created_at
 * @property string  $deleted_at
 *
 * @uses     yangjin
 * @date     2020-05-19
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class AccountsModel extends WebBaseModel
{
    protected $table = 'accounts';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'account_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['phone', 'username', 'nickname', 'sex', 'token', 'status', 'type', 'account_type', 'third_user_id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['status_str', 'sex_str', 'type_str'];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // 昵称长度禁止超过20个字符
            if (mb_strlen($model->nickname) > 30) {
                $model->errorInfo = '用户昵称不能超过30个字符';

                return false;
            }
            // 用户姓名html处理
            $model->nickname = htmlspecialchars($model->nickname);
            $model->username = $model->username ? $model->username : ($model->phone ? $model->phone : '');
        });
        self::updated(function (self $data) {
            if ($data->third_user_id) {
                $data->putCache('InfoByThirdUserId', $data->third_user_id, $data->getAttributes());
            }
        });
        self::saved(function (self $data) {
            if ($data->third_user_id) {
                $data->putCache('InfoByThirdUserId', $data->third_user_id, $data->getAttributes());
            }
        });
        self::created(function (self $data) {
            if ($data->third_user_id) {
                $data->putCache('InfoByThirdUserId', $data->third_user_id, $data->getAttributes());
            }
        });
        self::deleted(function (self $data) {
            if ($data->third_user_id) {
                $data->deleteCache('InfoByThirdUserId', $data->third_user_id);
            }
        });
    }

    /**
     * 状态字符串-访问器
     *
     * @return string
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 14:09:50
     */
    public function getStatusStrAttribute(): string
    {
        return self::getStatusStr($this->status);
    }

    /**
     * 性别字符串-访问器
     *
     * @return null|string
     * @author ensong.liu@vhall.com
     * @date   2019-02-16 21:28:11
     */
    public function getSexStrAttribute(): string
    {
        return self::getSexStr($this->sex);
    }

    /**
     * 用户类型字符串-访问器
     *
     * @return null|string
     * @author ensong.liu@vhall.com
     * @date   2019-02-16 21:28:11
     */
    public function getTypeStrAttribute(): string
    {
        return self::getTypeStr($this->type);
    }

    /**
     * 获取用户类型字符串
     *
     * @param int $type
     *
     * @return null|string
     * @author ensong.liu@vhall.com
     * @date   2019-02-16 21:30:30
     *
     */
    public static function getTypeStr($type): string
    {
        switch ($type) {
            case AccountConstant::TYPE_WATCH:
                $string = '观众';
                break;
            case AccountConstant::TYPE_MASTER:
                $string = '主持人';
                break;
            case AccountConstant::TYPE_INTERACTION:
                $string = '嘉宾/互动者';
                break;
            case AccountConstant::TYPE_ASSISTANT:
                $string = '助理';
                break;
            default:
                $string = '未知';
        }

        return $string;
    }

    /**
     * 获取状态字符串
     *
     * @param $status
     *
     * @return null|string
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 11:04:35
     *
     */
    public static function getStatusStr($status): string
    {
        switch ($status) {
            case AccountConstant::STATUS_DISABLED:
                $string = '封停';
                break;
            case AccountConstant::STATUS_ENABLED:
                $string = '正常';
                break;
            default:
                $string = '未知';
        }

        return $string;
    }

    /**
     * 获取性别字符串
     *
     * @param int $sex
     *
     * @return null|string
     * @author ensong.liu@vhall.com
     * @date   2019-02-16 21:30:30
     *
     */
    public static function getSexStr($sex): string
    {
        switch ($sex) {
            case AccountConstant::SEX_MEN:
                $string = '男';
                break;
            case AccountConstant::SEX_WOMEN:
                $string = '女';
                break;
            default:
                $string = '未知';
        }

        return $string;
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
     * 更新用户TOKEN
     *
     * @param $phone
     * @param $nickname
     * @param $token
     * @param $type
     * @return bool
     */
    public function updateToken($phone, $nickname, $token, $type)
    {
        $condition = [
            'phone' => $phone,
            'account_type' => $type,
        ];
        $attributes = [
            'nickname'=>$nickname,
            'token' => $token,
        ];
        return $this->updateInfo($condition, $attributes);
    }

    /**
     * 获取多个用户信息
     *
     * @author ensong.liu@vhall.com
     * @date 2019-05-13 20:27:45
     *
     * @param array $accountIds
     *
     * @return array
     */
    public function getAccountsByAccountIds(array $accountIds, $columns = ['*'], $keyBy = null)
    {
        $model = $this->whereIn('account_id', $accountIds);
        if ($keyBy) {
            $model = $model->get($columns)->keyBy($keyBy);
        } else {
            $model = $model->get($columns);
        }
        if (empty($model)) {
            return [];
        }

        return (array)$model->toArray();
    }

    /**
     * 获取用户信息 通过第三方用户id
     * @param $thirdUserId
     * @return AccountsModel|null
     */
    public function getInfoByThirdUserId($thirdUserId)
    {
        $attributes = $this->getCache('InfoByThirdUserId', $thirdUserId, function () use ($thirdUserId) {
            $model = $this->where('third_user_id', $thirdUserId)->first();
            return $model ? $model->getAttributes() : null;
        });

        return empty($attributes) ? null : $this->newInstance($attributes, true)->syncOriginal();
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
