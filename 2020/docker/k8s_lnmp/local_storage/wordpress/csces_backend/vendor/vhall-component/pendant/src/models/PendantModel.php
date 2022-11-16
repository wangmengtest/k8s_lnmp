<?php

namespace vhallComponent\pendant\models;

use Illuminate\Database\Eloquent\Builder;
use vhallComponent\pendant\constants\PendantConstant;
use App\Traits\ModelTrait;
use Vss\Traits\RedisTrait;
use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class PendantModel
 *
 * @package  vhallComponent\pendant\models
 * @property integer $id              主键id
 * @property integer $account_id      直播间主播用户id
 * @property string  $name            挂件名
 * @property string  $pic             挂件图片
 * @property string  $icon            挂件挂件
 * @property string  $pendant_url     挂件链接
 * @property integer $status          挂件状态，-1删除,1正常
 * @property integer $type            类型；1=推屏挂件，2=固定挂件
 * @property integer $is_default      是否是默认固定挂件，-1=否，1=是
 * @property string  $created_at      创建时间
 * @property string  $updated_at      更新时间
 * @property string  $deleted_at
 *
 * @date     2021/2/22
 * @author   jun.ou@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class PendantModel extends WebBaseModel
{
    
    

    protected $table = 'pendant';
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @param Builder $model
     * @param array   $condition
     *
     * @return Builder
     *
     * @date     2021/3/18
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function buildCondition(Builder $model, array $condition): Builder
    {
        $model = parent::buildCondition($model, $condition); // TODO: Change the autogenerated stub

        $model->when(!empty($condition['keyword']), function ($query) use ($condition) {
            $query->where('name', 'like', '%' . $condition['keyword'] . '%');
        });

        return $model;
    }

    /**
     * @param     $ids
     * @param int $accountId
     *
     * @return int
     *
     * @date     2021/3/18
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function deleteRow($ids, $accountId = 0)
    {
        $query = self::query()->whereIn('id', $ids)
            ->when($accountId, function ($query) use ($accountId) {
                $query->where(['account_id' => $accountId]);
            });

        return $query->update(['status' => PendantConstant::STATUS_OFF]);
    }

    /**
     * @param $ids
     *
     * @return int
     *
     * @date     2021/3/18
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function getCountByBgId($ids)
    {
        return self::query()
            ->where(['status' => PendantConstant::STATUS_ON])
            ->whereIn('id', $ids)
            ->count();
    }

    /**
     * @param $params
     *
     * @return false|PendantModel
     *
     * @date     2021/3/18
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function create($params)
    {
        $model              = new self();
        $model->account_id  = $params['account_id'];
        $model->name        = $params['name'];
        $model->pic         = $params['pic'];
        $model->icon        = $params['icon'] ?? '';
        $model->pendant_url = $params['pendant_url'] ?? '';
        $model->type        = $params['type'];
        $model->is_default  = $params['is_default'] ?? PendantConstant::STATUS_OFF;
        $model->created_at  = date('Y-m-d H:i:s');
        $model->updated_at  = date('Y-m-d H:i:s');
        if (!$model->save()) {
            return false;
        }

        return $model;
    }

    /**
     * @param     $condition
     * @param     $page
     * @param int $pageSize
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     * @date     2021/3/18
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function getPageList($condition, $page, $pageSize = 10)
    {
        return $this->buildCondition($this->newQuery(), $condition)
            ->orderBy('id', 'desc')
            ->paginate($pageSize, ['*'], 'page', $page);
    }

    /**
     * @param $id
     *
     * @return array
     *
     * @date     2021/3/18
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function getListById($id)
    {
        $fileds = [
            'id',
            'account_id',
            'name',
            'pic',
            'pendant_url',
            'type',
            'is_default'
        ];

        return self::query()
            ->whereIn('id', $id)
            ->where('status', PendantConstant::STATUS_ON)
            ->get($fileds)
            ->toArray();
    }

    /**
     * @param     $id
     * @param int $accountId
     *
     * @return $this
     *
     * @date     2021/3/19
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function getRowById($id, $accountId = 0)
    {
        return self::query()
            ->where(['id' => $id])
            ->when($accountId, function ($query) use ($accountId) {
                $query->where(['account_id' => $accountId]);
            })
            ->where('status', PendantConstant::STATUS_ON)
            ->first();
    }

    /**
     * @param $condition
     * @param $attr
     *
     * @return int
     *
     * @date     2021/3/22
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function updateALl($condition, $attr)
    {
        return self::query()->where($condition)->update($attr);
    }

    /**
     * @param $accountId
     *
     * @return Builder|\Illuminate\Database\Eloquent\Model|object|null
     *
     * @date     2021/3/22
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function getDefaultFixedFromDb($accountId)
    {
        return self::query()
            ->where(['account_id' => $accountId])
            ->where(['type' => PendantConstant::TYPE_FIXED])
            ->where(['status' => PendantConstant::STATUS_ON])
            ->where(['is_default' => PendantConstant::STATUS_ON])
            ->first();
    }

    /**
     * @param $accountId
     *
     * @return array
     *
     * @date     2021/3/22
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function setDefaultFixed($accountId)
    {
        $key = PendantConstant::PENDANT_FIXED_INFO_KEY . $accountId;

        $info = $this->getDefaultFixedFromDb($accountId);
        $info = empty($info) ? [] : $info->toArray();

        vss_redis()->set($key, $info, PendantConstant::PENDANT_KEY_EXPIRE);
        return $info;
    }

    /**
     * @param $accountId
     *
     * @return array
     *
     * @date     2021/3/22
     * @author   jun.ou@vhall.com
     * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
     */
    public function getDefaultFixed($accountId)
    {
        $key  = PendantConstant::PENDANT_FIXED_INFO_KEY . $accountId;
        $info = vss_redis()->get($key);
        if (is_array($info)) {
            return $info;
        }

        return $this->setDefaultFixed($accountId);
    }
}