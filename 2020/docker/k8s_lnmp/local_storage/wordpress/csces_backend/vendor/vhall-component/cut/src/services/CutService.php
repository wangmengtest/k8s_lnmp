<?php
/**
 * Created by PhpStorm.
 * User: zhangshilong
 * Date: 2019/12/16
 * Time: 16:50
 */

namespace vhallComponent\cut\services;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Vss\Common\Services\WebBaseService;

class CutService extends WebBaseService
{
    // 0:初始化； 1:已发布 ； 2:回放生成成功；3:回放生成失败； 4:审核中；5:审核成功；
    // 6: 审核失败；7: 转码中；8:转码失败；9:转码部分成功；
    private $vodSuccessStatus = [1, 2, 5];

    private $vodErrorStatus = [3, 6];

    /**
     * 获取列表信息
     *
     * @param $param
     *
     * @return array
     */
    public function getList($param): array
    {
        //1、接收参数信息
        $page_num  = !empty($param['page_num']) ? $param['page_num'] : 1;
        $page_size = !empty($param['page_size']) ? $param['page_size'] : 100;

        //2、获取数据信息
        $query = vss_model()->getRecordModel()->newQuery();
        if (!empty($param['keywords'])) {
            $query->where(function (Builder $query) use ($param) {
                $query->where('il_id', $param['keywords']);
                $query->orwhere('vod_id', $param['keywords']);
            });
        }
        if (isset($param['account_id'])) {
            $query->where('account_id', $param['account_id']);
        }
        $query->where('status', 0);
        $list = $query->selectRaw('vod_id,name,duration,storage,created_at,il_id,id,account_id,source,transcode_status')
            ->orderBy('created_at', 'desc')
            ->paginate($page_size, ['*'], 'page', $page_num);
        $list = json_decode(json_encode($list, JSON_UNESCAPED_UNICODE), true);
        //3、返回数据
        return [
            'list'     => $list['data'],
            'total'    => $list['total'],
            'page_all' => $list['last_page'],
            'per_page' => $page_size,
            'page_num' => $list['current_page'],
        ];
    }

    /**
     * 获取回放详情
     *
     * @param $params
     *
     * @return array|int
     * @throws Exception
     */
    public function getInfo($params)
    {
        //1、获取参数信息
        $data = [];
        //1.2、组织参数
        if (!empty($params['room_id'])) {
            $data['room_id'] = $params['room_id'];
        }
        //2、获取点播信息
        $info = vss_model()->getRecordModel()->where(['vod_id' => $params['vod_id']])->first();
        if (empty($info)) {
            return -2;
        }
        //生成回放成功之后，不再读取PAAS
        if ($info->storage != 0 && $info->duration != 0) {
            return $info->toArray();
        }
        $data['vod_id'] = $info->vod_id;
        //在PAAS中读取数据
        $recordInfo = vss_service()->getPaasService()->recordInfo($data);
        if ($recordInfo) {
            $vodInfo = $recordInfo['vod_info'];
            if (in_array($vodInfo['status'], $this->vodSuccessStatus)) { //成功
                $field['duration'] = $vodInfo['duration'];
                $info->source != 11 && $field['transcode_status'] = $vodInfo['transcode_status'];
                $field['storage'] = $vodInfo['storage'];
                if (empty($info->name)) {
                    $field['name'] = $vodInfo['name'];
                }
                return $this->completeById($info, $field);
            } elseif (in_array($vodInfo['status'], $this->vodErrorStatus)) { //失败
                return -1;
            }
        }

        return [];
    }

    /**
     * 保存剪辑内容
     *
     * @param $params
     *
     * @return array
     * @throws Exception
     */
    public function saveRecord($params): array
    {
        //1、获取参数信息
        $data['stream_id']    = $params['stream_id'];
        $data['cut_sections'] = $params['cut_sections'];   //裁剪操作具体信息

        if (!empty($params['point_sections'])) {
            $data['point_sections'] = $params['point_sections'];   //打点操作具体信息
        }
        if (isset($params['cut_type'])) {
            $data['ts_cut_type'] = $params['cut_type'];  //0--去除，1--保留；默认为 0
        }
        //1.1、判断原始文件是否正确

        //2、生成剪辑信息
        $data['vod_id'] = $params['vod_id'];    //原始点播ID
        $cutInfo        = vss_service()->getPaasService()->submitVideoEditTasks($data);
        if (!empty($cutInfo)) {
            //3、添加生成的信息至数据库中
            $field['room_id']          = $params['stream_id'];
            $field['il_id']            = $params['il_id'];
            $field['source']           = 11;   //裁剪
            $field['account_id']       = $params['account_id'];
            $field['vod_id']           = $cutInfo['vod_id'];   //合成
            $field['transcode_status'] = 1;
            if (!empty($params['name'])) {
                $field['name'] = $params['name'];
            }

            $createRe = $this->createRecord($field);
            if ($createRe) {
                $result = vss_model()->getRecordModel()->getInfoByVodId($cutInfo['vod_id']);
                return ['msg' => 'success', 'data' => $result];
            }
        }

        return ['msg' => '生成失败', 'data' => []];
    }

    /**
     * @param $params
     *
     * @return bool
     */
    public function createRecord($params): bool
    {
        //1、获取数据信息
        vss_logger()->info('mergeRecord', ['param' => $params]);
        if ($params) {
            //1.1、组织参数信息
            $data['duration']         = 0;
            $data['storage']          = 0;
            $data['created_at']       = date('Y-m-d H:i:s');
            $data['account_id']       = $params['account_id'];
            $data['source']           = $params['source'];
            $data['il_id']            = $params['il_id'];
            $data['room_id']          = $params['room_id'];
            $data['vod_id']           = $params['vod_id'];
            $data['transcode_status'] = !empty($params['transcode_status']) ? $params['transcode_status'] : 0;
            if (!empty($params['name'])) {
                $data['name'] = $params['name'];
            }
            $res = vss_model()->getRecordModel()->create($data)->toArray();
            vss_logger()->info('mergeRecord1', ['res' => $res, 'param' => $data]);
            //1.2、添加数据
            if (!$res) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * 完善回放信息
     *
     * @param       $info
     * @param       $data
     *
     * @return array
     */
    public function completeById($info, $data)
    {
        //1、查看数据是否存在
        if ($info && $info->id > 0) {
            //1.1、更新回放信息
            vss_logger()->info('completeById', [$info, $data]);
            try {
                $result = vss_model()->getRecordModel()->where(['id' => $info->id])->update($data);
                if ($result) {
                    return $this->getInfoById($info->id);
                }
            } catch (Exception $e) {
                vss_logger()->info('RecordSqlErroe', [$e->getCode() => $e->getMessage()]);
                return [];
            }
        }
        return [];
    }

    /**
     * 通过ID获取信息
     *
     * @param $id
     */
    public function getInfoById($id)
    {
        $query = vss_model()->getRecordModel()->newQuery();
        return $query->selectRaw('*')->where('id', $id)
            ->first()->toArray();
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function getDetailByRecordId($params)
    {
        try {
            $result               = vss_model()->getRecordModel()->getInfoByVodId($params['vod_id']);
            $roomInfo             = vss_model()->getRoomsModel()->getInfoByIlId($result['il_id']);
            $result['channel_id'] = $roomInfo['channel_id'];
            $result['app_id']     = vss_service()->getTokenService()->getAppId();
            return $result;
        } catch (Exception $e) {
            vss_logger()->info('RecordSqlErroe', [$e->getCode() => $e->getMessage()]);
            return [];
        }
    }
}
