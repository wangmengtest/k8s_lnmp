<?php
/**
 * Created by PhpStorm.
 * User: songyue
 * Date: 18-11-23
 * Time: 下午11:48
 */

namespace vhallComponent\question\models;

use Illuminate\Database\Eloquent\Builder;
use vhallComponent\account\models\AccountsModel;
use vhallComponent\room\models\RoomsModel;
use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class QuestionAnswerLogs
 *
 * @package App\Modules\Models
 * @property int $answer_log_id
 * @property int $account_id
 * @property int $answer_id '微吼云答卷id',
 * @property int $question_id '问卷id',
 * @property int $q_id '微吼云问卷id',
 * @property int $il_id '互动直播id',
 * @property int $question_log_id '问卷使用表id',
 * @property string $updated_at datetime
 * @property string $created_at datetime
 * @property Accounts $accounts
 */
class QuestionAnswerLogsModel extends WebBaseModel
{
    protected $primaryKey = 'answer_log_id';

    /**
     * 可以被批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = ['account_id', 'il_id', 'question_id', 'answer_id', 'q_id'];

    public static function boot()
    {
        parent::boot();
        /**
         * 检查question_id 是否存在,如果不存在,则根据q_id 查询
         */
        static::creating(function ($model) {
            if (empty($model->question_id)) {
                if (empty($model->q_id)) {
                    return false;
                }
                $model->question_id = QuestionsModel::where(['q_id' => $model->q_id])->value('question_id');
            }
            if (empty($model->question_log_id)) {
                $questionLogsModel = QuestionLogsModel::getLastQuestion($model->question_id, $model->il_id);
                if ($questionLogsModel == null) {
                    return false;
                }
                $model->question_log_id = QuestionLogsModel::getLastQuestion($model->question_id, $model->il_id)->question_log_id;
            }
        });
    }

    /**
     * 模型关联-用户表
     *
     * @author ensong.liu@vhall.com
     * @date   2019-02-14 10:39:24
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(AccountsModel::class, 'account_id', 'account_id');
    }

    /**
     * 模型关联-房间表
     *
     * @author ensong.liu@vhall.com
     * @date   2019-02-14 10:41:10
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function interactiveLive()
    {
        return $this->belongsTo(RoomsModel::class, 'il_id', 'il_id');
    }

    /**
     * 模型关联-问卷表
     *
     * @author ensong.liu@vhall.com
     * @date   2019-02-14 10:41:10
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question()
    {
        return $this->belongsTo(QuestionsModel::class, 'question_id', 'question_id');
    }

    public static function createLog($accountId, $questionId, $ilId, $answerId, $qId)
    {
        $data = [
            'account_id'  => $accountId,
            'question_id' => $questionId,
            'il_id'       => $ilId,
            'answer_id'   => $answerId,
            'q_id'        => $qId,
        ];

        $model = self::create($data);

        return $model;
    }

    /**
     * 条件构造器
     *
     * @author ensong.liu@vhall.com
     * @date   2019-02-13 15:14:34
     *
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @param array $condition
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildCondition(Builder $model, array $condition):Builder
    {
        $model = parent::buildCondition($model, $condition);

        //房主ID
        $model->when($condition['master_account_id'], function ($query) use ($condition) {
            $query->leftJoin(RoomsModel::getInstance()->getTable() . ' AS interactive_lives', 'interactive_lives.il_id', '=', 'question_answer_logs.il_id')
                  ->where(function ($query) use ($condition) {
                      $query->where('interactive_lives.account_id', '=', $condition['master_account_id']);
                  });
        });
        //统计时间范围-开始
        $model->when($condition['begin_time'], function ($query) use ($condition) {
            $query->where('question_answer_logs.created_at', '>=', $condition['begin_time']);
        });
        //统计时间范围-结束
        $model->when($condition['end_time'], function ($query) use ($condition) {
            $query->where('question_answer_logs.created_at', '<=', date('Y-m-d 23:59:59', strtotime($condition['end_time'])));
        });

        return $model;
    }

    /**
     * 查询用户房间回答记录
     *
     * @param $accoundId
     * @param $ilId
     *
     * @return mixed
     */
    public static function getAnswerByAccountIdAndIlId($accountId, $ilId)
    {
        $model = self::where(['account_id' => $accountId])->where(['il_id' => $ilId]);

        return $model;
    }

    /**
     * 查询答卷总数
     *
     * @param int $ilId
     * @param string $begintime
     * @param string $endtime
     *
     * @return int
     */
    public static function getStatCount($ilId = 0, $begintime = '', $endtime = '')
    {
        $model = self::query();

        if ($ilId) {
            if (is_array($ilId)) {
                $model->whereIn('il_id', $ilId);
            } else {
                $model->where('il_id', $ilId);
            }
        }

        if ($begintime && $endtime) {
            $model->whereBetween('created_at', [$begintime, $endtime]);
        }

        return $model->count();
    }

    /**
     * 查询关联房间模型
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function interactiveLives()
    {
        return $this->hasOne(RoomsModel::class, 'il_id', 'il_id');
    }

    /**
     * 查询关联问卷
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function questions()
    {
        return $this->hasOne(QuestionsModel::class, 'question_id', 'question_id');
    }

    /**
     * 查询关联用户
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function accounts()
    {
        return $this->hasOne(AccountsModel::class, 'account_id', 'account_id');
    }
}
