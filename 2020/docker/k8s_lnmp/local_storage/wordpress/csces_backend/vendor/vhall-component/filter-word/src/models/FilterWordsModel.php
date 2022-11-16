<?php

namespace vhallComponent\filterWord\models;

use Illuminate\Database\Eloquent\Builder;
use vhallComponent\decouple\models\WebBaseModel;
use vhallComponent\filterWord\constants\FilterwordsConstant;

/**
 * @property string  $word       敏感词
 * @property integer $accountId  所属商家id 0表示超管
 * @property integer $ilId       所属房间id 0表示超管
 * @property integer $user_id    创建用户id
 *
 * Class FilterWordsModel
 *
 * @package App\Models
 */
class FilterWordsModel extends WebBaseModel
{

    protected $table = 'filter_words';

    protected $fillable = ['account_id', 'keyword', 'il_id', 'user_id'];

    protected static function boot()
    {
        parent::boot();

        static::created(function () {
            self::delKey();
        });

        static::updated(function () {
            self::delKey();
        });

        static::deleted(function () {
            self::delKey();
        });

    }

    public static function delKey()
    {
        $ret = vss_redis()->smembers(FilterwordsConstant::FILTER_WORDS_LIST_IL_ID_KEY);
        foreach ($ret as $row) {
            vss_redis()->del(FilterwordsConstant::FILTER_WORDS_CACHE_KEY . $row);
        }
    }

    /**
     * 条件构造器
     *
     * @param Builder $model
     * @param array   $condition
     *
     * @return Builder
     */
    protected function buildCondition(Builder $model, array $condition): Builder
    {
        $model = parent::buildCondition($model, $condition);
        //关键字搜索
        $model->when(isset($condition['search']) && !empty($condition['search']), function ($query) use ($condition) {
            $query->where('keyword', 'like', "%{$condition['search']}%");
        });

        $model->when(isset($condition['search']) && !empty($condition['begin_time']),
            function ($query) use ($condition) {
                $query->where('created_at', '>', $condition['begin_time']);
            });

        $model->when(isset($condition['search']) && !empty($condition['end_time']), function ($query) use ($condition) {
            $query->where('created_at', '<', $condition['end_time'] . ' 23:59:59');
        });

        return $model;
    }

    /**
     * 获取敏感词数组
     *
     * @param null $accountId
     *
     * @return array
     */
    public function getFilterWordsArr($accountId, $ilId)
    {
        return $this->where(['account_id' => 0])
            ->orWhere(function ($query) use ($accountId) {
                return $query->where(['account_id' => $accountId, 'il_id' => 0]);
            })->orWhere(function ($query) use ($ilId) {
                return $query->where(['il_id' => $ilId]);
            })->pluck('keyword')->toArray();
    }

    /**
     * 获取逗号拼接的敏感词字符串
     *
     * @param null $accountId
     *
     * @return string
     */
    public function getFilterWordsString($accountId = null)
    {
        $words = vss_redis()->get($this->getCacheKey($accountId));
        if (empty($words)) {
            $wordList = $this->getFilterWordsArr($accountId, '');
            $words    = implode(',', $wordList);
            vss_redis()->set($this->getCacheKey($accountId), $words, FilterwordsConstant::FILTER_WORDS_EXPIRE);
        }

        return empty($words) ? '' : $words;
    }

    /**
     * 获取敏感词缓存键名
     *
     * @param null $accountId
     *
     * @return string
     */
    public function getCacheKey($accountId = null)
    {
        if ($accountId) {
            return FilterwordsConstant::FILTER_WORDS_CACHE_KEY . $accountId;
        }

        return FilterwordsConstant::FILTER_WORDS_CACHE_KEY;
    }

    /**
     * 校验敏感词是否重复
     *
     * @param $keyword
     * @param $accountId
     * @param $ilId
     *
     * @return \Illuminate\Database\Eloquent\Model|object|static|null
     * @author   ming.wang@vhall.com
     *
     * @uses     wang-ming
     */
    public function checkFilterWordsRepeat($keyword, $accountId, $ilId)
    {
        return $this->where(['keyword' => $keyword, 'account_id' => 0])
            ->when($accountId, function ($query) use ($keyword, $accountId) {
                return $query->orWhere(function ($q) use ($keyword, $accountId) {
                    return $q->where(['keyword' => $keyword, 'account_id' => $accountId, 'il_id' => 0]);
                });
            })
            ->when($ilId, function ($query) use ($keyword, $ilId) {
                return $query->orWhere(function ($q) use ($keyword, $ilId) {
                    return $q->where(['keyword' => $keyword, 'il_id' => $ilId]);
                });
            })
            ->first();
    }

    /**
     * @param $data
     *
     * @return mixed
     * @author   ming.wang@vhall.com
     *
     * @uses     wang-ming
     */
    public function inserted($data)
    {
        return $this->insert($data);
    }

    /**
     * 批量删除
     *
     * @param $ids
     *
     * @return \Illuminate\Database\Query\Builder|FilterWordsModel
     * @author   ming.wang@vhall.com
     *
     * @uses     wang-ming
     */
    public function delByIds($ids)
    {
        return $this->whereIn('id', $ids)->forceDelete();
    }
}
