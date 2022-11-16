<?php


//namespace vhallComponent\documnet;
namespace vhallComponent\document\models;

use Illuminate\Database\Eloquent\Builder;
use vhallComponent\account\models\AccountsModel;
use vhallComponent\decouple\models\WebBaseModel;

/**
 * Class RoomDocumentsModel
 * @package App\Models
 */

class RoomDocumentsModel extends WebBaseModel
{
    protected $table = 'room_documents';

    protected $attributes = [
        'app_id' => '',
        'document_id' => '',
        'room_id' => '',
        'account_id' => '0',
        'created_at' => '1970-01-01 00:00:00',
        'updated_at' => '1970-01-01 00:00:00',
        'hash' => '',
        'ext' => '',
        'page' => 0,
        'trans_status' => 1,
        'file_name' => '',
        'status_jpeg' => '0',
        'status' => '0',
        'status_swf' => '0',

    ];

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
        //关键字搜索
        $model->when($condition['keyword'], function ($query) use ($condition) {
            $query->leftJoin(AccountsModel::getInstance()->getTable() . ' as accounts', 'room_documents.account_id', '=', 'accounts.account_id')->where(function ($query) use ($condition) {
                $query->where('room_documents.file_name', 'like', sprintf('%%%s%%', $condition['keyword']))
                    ->orWhere('accounts.username', 'like', sprintf('%%%s%%', $condition['keyword']))
                    ->orWhere('accounts.nickname', 'like', sprintf('%%%s%%', $condition['keyword']));
            });
        });
        //时间范围-开始
        $model->when($condition['begin_time'], function ($query) use ($condition) {
            $query->where('room_documents.created_at', '>=', $condition['begin_time']);
        });
        //时间范围-结束
        $model->when($condition['end_time'], function ($query) use ($condition) {
            $query->where('room_documents.created_at', '<=', date('Y-m-d 23:59:59', strtotime($condition['end_time'])));
        });

        return $model;
    }

    /**
     * 获取文档列表
     *
     * @date 2019-05-13 20:44:35
     *
     * @param int $accountId
     * @param array $documentIds
     *
     * @return array
     */
    public function getListByAccountIdAndDocumentIds(int $accountId, array $documentIds)
    {
        $model = $this->select(['document_id', 'account_id'])
            ->where('account_id', $accountId)
            ->whereIn('document_id', $documentIds)
            ->get();
        return $model;
    }
}
