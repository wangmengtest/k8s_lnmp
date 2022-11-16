<?php

namespace vhallComponent\filterWord\models;

use vhallComponent\account\models\AccountsModel;
use vhallComponent\decouple\models\WebBaseModel;

/**
 * @property string  $content       内容
 * @property integer $account_id    发送id
 * @property integer $il_id         房间id
 *
 * Class FilterWordsModel
 *
 * @package App\Models
 */
class FilterWordsLogModel extends WebBaseModel
{
    

    protected $table = 'filter_words_log';

    protected $fillable = ['account_id', 'content', 'il_id', 'update_at', 'created_at', 'live_status'];

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
     * 模型关联-用户表
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @author ensong.liu@vhall.com
     * @date   2019-02-14 10:39:24
     */
    public function accounts()
    {
        return $this->belongsTo(AccountsModel::class, 'account_id', 'account_id');
    }
}
