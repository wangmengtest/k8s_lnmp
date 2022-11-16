<?php

namespace vhallComponent\admin\models;

use Vss\Utils\IpUtil;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use vhallComponent\action\models\RoleModel;
use vhallComponent\decouple\models\WebBaseModel;

/**
 *+----------------------------------------------------------------------
 * Class AdminsModel
 * 管理员表模型
 *+----------------------------------------------------------------------
 *
 * @property int $admin_id     主键
 * @property string  $admin_name   登录名
 * @property string  $nick_name    昵称
 * @property string  $password     密码
 * @property string  $mobile       手机号
 * @property string  $email        邮箱
 * @property string  $token        凭证
 * @property string  $token_expire 凭证过期时间
 * @property int $role_id      角色ID
 * @property string  $last_ip      登录ip
 * @property string  $last_time    最后登录时间
 * @property int $login_num    登入统计
 * @property bool $status       状态:0>禁用,1>正常
 * @property string  $updated_at   更新时间
 * @property string  $created_at   创建时间
 * @property string  $deleted_at
 *
 * @package App\Models
 * @author  ensong.liu@vhall.com
 * @date    2019-01-30 14:00:03
 * @version
 *+----------------------------------------------------------------------
 */
class AdminsModel extends WebBaseModel
{
    public static function getIp()
    {
        return IpUtil::getIp();
    }

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'admin_id';

    protected $table = 'admins';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'admin_name',
        'nick_name',
        'password',
        'mobile',
        'email',
        'token',
        'role_id',
        'last_ip',
        'last_time',
        'login_num',
        'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password', 'token', 'token_expire'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['status_str'];

    /**
     * 管理员状态
     */
    const STATUS_DISABLED = 0;

    const STATUS_ENABLED = 1;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
    }

    /**
     * 模型关联-角色表
     *
     * @return BelongsTo
     * @author ensong.liu@vhall.com
     * @date   2019年02月14日20:17:33
     */
    public function role()
    {
        return $this->belongsTo(RoleModel::class, 'role_id', 'role_id');
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
     * 获取状态字符串
     *
     * @param int $status
     *
     * @return null|string
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 11:04:35
     *
     */
    public static function getStatusStr(int $status): string
    {
        switch ($status) {
            case self::STATUS_DISABLED:
                $string = '禁用';
                break;
            case self::STATUS_ENABLED:
                $string = '正常';
                break;
            default:
                $string = null;
        }

        return $string;
    }

    /**
     * 列表/总数条件构造器
     *
     * @param Builder $model
     * @param array   $condition
     *
     * @return Builder
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 10:20:49
     *
     */
    protected function buildCondition(Builder $model, array $condition): Builder
    {
        $model = parent::buildCondition($model, $condition);
        //关键字
        $model->when($condition['keyword'] ?? '', function (Builder $query) use ($condition) {
            $query->where(function (Builder $query) use ($condition) {
                $query->where('admins.admin_name', 'like', sprintf('%%%s%%', $condition['keyword']))
                    ->orWhere('admins.nick_name', 'like', sprintf('%%%s%%', $condition['keyword']))
                    ->orWhere('admins.mobile', 'like', sprintf('%%%s%%', $condition['keyword']));
            });
        });
        //时间范围-开始
        $model->when($condition['begin_time'] ?? '', function ($query) use ($condition) {
            $query->where('admins.created_at', '>=', $condition['begin_time']);
        });
        //时间范围-结束
        $model->when($condition['end_time'] ?? '', function ($query) use ($condition) {
            $query->where('admins.created_at', '<=', date('Y-m-d 23:59:59', strtotime($condition['end_time'])));
        });
        //登录名
        $model->when($condition['admin_name'] ?? '', function ($query) use ($condition) {
            $query->where('admins.admin_name', '=', $condition['admin_name']);
        });
        //昵称
        $model->when($condition['nick_name'] ?? '', function ($query) use ($condition) {
            $query->where('admins.nick_name', '=', $condition['nick_name']);
        });
        //角色
        $model->when($condition['role_id'] ?? '', function ($query) use ($condition) {
            $query->where('admins.role_id', '=', $condition['role_id']);
        });
        //状态
        $model->when(isset($condition['status']) && $condition['status'] != ''
            && in_array($condition['status'], [
                self::STATUS_DISABLED,
                self::STATUS_ENABLED,
            ]), function ($query) use ($condition) {
                $query->where('admins.status', '=', $condition['status']);
            });

        return $model;
    }

    /**
     * 生成hash密码
     *
     * @param string $password
     *
     * @return string
     * @author ensong.liu@vhall.com
     * @date   2019-01-31 14:25:20
     *
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * 验证hash密码
     *
     * @param string $password
     * @param string $hash
     *
     * @return bool
     * @author ensong.liu@vhall.com
     * @date
     *
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * 更新TOKEN
     *
     * @param int $adminId
     * @param int $expire -1标识永不过期
     *
     * @return string
     * @author ensong.liu@vhall.com
     * @date   2019-01-31 14:41:08
     *
     */
    public static function refreshToken($adminId, $expire = 86400): string
    {
        $token       = substr(md5(rand(1000, 9999) . time() . $adminId . rand(1000, 9999)), 8, 16);
        $tokenExpire = date(
            'Y-m-d H:i:s',
            ($expire == -1 ? strtotime('+10 years') : strtotime(sprintf('+ %d seconds', $expire)))
        );
        $condition   = [
            'admin_id' => $adminId,
        ];
        $values      = [
            'token'        => $token,
            'token_expire' => $tokenExpire,
        ];
        self::query()->where($condition)->update($values);

        return $token;
    }

    /**
     * 更新登录次数
     *
     * @param int $adminId
     *
     * @return int|null
     * @author ensong.liu@vhall.com
     * @date   2019-02-21 11:37:12
     *
     */
    public static function refreshLoginNum($adminId): int
    {
        $condition = [
            'admin_id' => $adminId,
        ];

        return self::query()->where($condition)->increment('login_num', 1);
    }

    /**
     * 更新登录信息
     *
     * @param int $adminId
     *
     * @return int|null
     * @author ensong.liu@vhall.com
     * @date   2019-02-21 11:37:29
     *
     */
    public static function refreshLoginInfo($adminId): int
    {
        $condition = [
            'admin_id' => $adminId,
        ];
        $values    = [
            'last_ip'   => self::getIp(),
            'last_time' => date('Y-m-d H:i:s'),
        ];

        return self::query()->where($condition)->update($values);
    }
}
