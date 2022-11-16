<?php
/**
 * Created by PhpStorm.
 * User: liuxiangliang
 * Date: 2020/3/27
 * Time: 16:27
 */

namespace vhallComponent\vote\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class VoteModel
 * @package App\Models
 * @property int $vote_id
 * @property string  $title      标题
 * @property string  $extend     业务端扩展字段
 * @property int $account_id 用户id
 * @property int $app_id     应用id
 * @property int $is_public  是否公开投票，1是0否，默认是
 * @property string  $source_id  来源id
 * @property int $limit_time 投票限时时长
 * @property int $option_num 一次可投选项的数量
 * @property string  $created_at
 * @property string  $updated_at
 * @property string  $deleted_at
 */
class VotesModel extends WebBaseModel
{
    protected $table        = 'votes';

    public $incrementing = false;

    protected $attributes = [
        'vote_id'    => null,
        'title'      => null,
        'extend'     => null,
        'account_id' => null,
        'app_id'     => null,
        'source_id'  => null,
        'is_public'  => 1,
        'limit_time' => 0,
        'option_num' => 1,
        'created_at' => '0000-00-00 00:00:00',
        'updated_at' => '0000-00-00 00:00:00'
    ];

    protected $primaryKey = 'vote_id';

    protected static function boot()
    {
        self::created(function (VotesModel $data) {
            $data->putCache('InfoByVoteId', $data->vote_id, $data->getAttributes());
//            vss_service()->getSaasService()->createSurveyTpl($data); //??
        });
        self::updated(function (self $data) {
            $data->putCache('InfoByVoteId', $data->vote_id, $data->getAttributes());
        });
        self::deleted(function (self $data) {
            $data->deleteCache('InfoByVoteId', $data->vote_id);
        });
        parent::boot();
    }

    /**
     * @param $vote_id
     *
     * @return $this
     */
    public function findByVoteId($vote_id)
    {
        $attributes = $this->getCache('InfoByVoteId', $vote_id, function () use ($vote_id) {
            $model = $this->find($vote_id);
            return $model ? $model->getAttributes() : null;
        });
        return empty($attributes) ? null : $this->newInstance($attributes, true)->syncOriginal();
    }

    /**
     * @param $source_id
     *
     * @return $this
     */
    public function findBySourceId($source_id)
    {
        $vote_id = $this->getCache('InfoBySourceId', $source_id, function () use ($source_id) {
            $model = $this->where(compact('source_id'))->first();
            return $model ? $model->vote_id : null;
        });
        return empty($vote_id) ? null : $this->findByVoteId($vote_id);
    }
}
