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
use App\Services\Paas\PaasService;

/**
 * Class QuestionLogs
 *
 * @package App\Modules\Models
 * @property int $question_log_id
 * @property int $account_id
 * @property int $question_id '问卷id',
 * @property int $il_id '互动直播id',
 * @property string $updated_at datetime
 * @property string $created_at datetime
 * @property Accounts $accounts
 */
class QuestionLogsModel extends WebBaseModel
{
    protected $primaryKey = 'question_log_id';

    /**
     * 可以被批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = ['account_id', 'il_id', 'question_id'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['answer_account_count'];

    public static function boot()
    {
        parent::boot();
        /**
         * 发送自定义消息
         *
         * @var $model Questions
         */
        static::created(function ($model) {
            //如果是待审核消息,发送一条通知,审核端响应后获取新数据
            try {
                if (!is_object($model)) {
                    throw new \Exception();
                }
                $body = ['module' => 'sentQuestion', 'q_id' => $model->questions->q_id, 'question_id' => $model->questions->question_id, 'title' => $model->questions->title];
                PaasService::getInstance()->sendMessage($model->interactiveLives->channel_id, $body);
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                vss_logger()->error('QuestionLog::created', ['data' => $model, 'errorInfo' => $msg]);
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

    /**
     * 问卷回答总数
     *
     * @author ensong.liu@vhall.com
     * @date 2019-05-22 19:31:33
     * @return int
     */
    public function getAnswerAccountCountAttribute():int
    {
        $condition = [
            'question_log_id' => $this->getAttribute('question_log_id')
        ];
        return QuestionAnswerLogs::getInstance()->getCount($condition) ?: 0;
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
            $query->leftJoin(RoomsModel::getInstance()->getTable() . ' AS interactive_lives', 'interactive_lives.il_id', '=', 'question_logs.il_id')
                  ->where(function ($query) use ($condition) {
                      $query->where('interactive_lives.account_id', '=', $condition['master_account_id']);
                  });
        });
        //用户ID
        $model->when($condition['account_id'], function ($query) use ($condition) {
            $query->leftJoin(QuestionsModel::getInstance()->getTable() . ' as questions', 'questions.question_id', '=', 'question_logs.question_id')->where(function ($query) use ($condition) {
                $query->where('questions.account_id', '=', $condition['account_id']);
            });
        });
        //统计时间范围-开始
        $model->when($condition['begin_time'], function ($query) use ($condition) {
            $query->where('question_logs.created_at', '>=', $condition['begin_time']);
        });
        //统计时间范围-结束
        $model->when($condition['end_time'], function ($query) use ($condition) {
            $query->where('question_logs.created_at', '<=', date('Y-m-d 23:59:59', strtotime($condition['end_time'])));
        });

        return $model;
    }

    /**
     * 创建数据-问卷发起记录
     *
     * @param $accountId
     * @param $questionId
     * @param $ilId
     *
     * @return QuestionLogs
     */
    public static function createLog($accountId, $questionId, $ilId)
    {
        $data = [
            'account_id'  => $accountId,
            'question_id' => $questionId,
            'il_id'       => $ilId,
        ];

        $model = self::create($data);

        return $model;
    }

    /**
     * 查询问卷使用总数
     *
     * @param int $accountId
     * @param int $ilId
     * @param string $begintime
     * @param string $endtime
     *
     * @return int
     */
    public static function getStatCount($accountId = 0, $ilId = 0, $begintime = '', $endtime = '')
    {
        $model = self::query();

        if ($accountId) {
            $model->where('account_id', $accountId);
        }
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
     * 查询关联用户
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function accounts()
    {
        return $this->hasOne(AccountsModel::class, 'account_id', 'account_id');
    }

    /**
     * 查询房间的最后一个问卷记录
     */
    public static function getLastQuestion($questionId, $ilId)
    {
        return self::query()->where(['question_id' => $questionId])->where(['il_id' => $ilId])->orderBy('question_log_id', 'desc')->first();
    }
}
