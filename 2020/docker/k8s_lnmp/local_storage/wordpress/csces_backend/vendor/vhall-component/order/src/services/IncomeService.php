<?php


namespace vhallComponent\order\services;

use Vss\Common\Services\WebBaseService;

class IncomeService extends WebBaseService
{
    /**
     *
     * @param $params
     * @return bool
     */
    public function saveIncome($params)
    {
        if ($params) {
            $ret=vss_model()->getIncomeModel()->where(['app_id'=>$params['app_id'], 'account_id'=>$params['account_id']])->first();
            if ($ret) {
                $total=bcadd($ret->total, $params['amount'], 2);
                vss_model()->getIncomeModel()->where(['id'=>$ret->id])->update(['total'=>$total]);
            } else {
                $save_data=[
                    'total'=>$params['amount'],
                    'app_id'=>$params['app_id'],
                    'account_id'=>$params['account_id'],
                    'balance'=>$params['amount'],
                ];
                vss_model()->getIncomeModel()->create($save_data);
            }
        }
        return false;
    }

    /**
     * @param $params
     * @return array
     *
     */
    public function getInfo($params)
    {
        //验证参数
        vss_validator($params, [
            'account_id'      => '',
            'app_id'          => 'required',
        ]);
        $condition=['app_id'=>$params['app_id']];
        if (empty($params['account_id'])) {
            $ret=vss_model()->getIncomeModel()->where($condition)->sum('balance');
            return  ['balance'=>$ret];
        }
        if (!empty($params['account_id'])) {
            $condition['account_id']=$params['account_id'];
        }
        $ret=vss_model()->getIncomeModel()->where($condition)->first()->toArray();
        if (!$ret) {
            return [];
        }
        return ['balance'=>$ret['balance']];
    }
}
