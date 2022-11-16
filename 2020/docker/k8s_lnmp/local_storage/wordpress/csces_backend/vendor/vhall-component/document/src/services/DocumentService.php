<?php


namespace vhallComponent\document\services;

use App\Constants\ResponseCode;
use vhallComponent\common\services\UploadFile;
use Vss\Common\Services\WebBaseService;

class DocumentService extends WebBaseService
{
    /**
     * 文档上传
     *
     * @param $params
     *
     * @return mixed|void
     *
     */
    public function upload($params)
    {
        vss_validator($params, [
            'app_id'     => 'required',
            'room_id'    => ['required_without:account_id'],
            'account_id' => 'required_without:room_id'
        ]);
        vss_logger()->info('文档转换', ['file' => var_export($_FILES, true), 'params' => $params]);
        $file = new UploadFile('document');
        if (!$file->isValid()) {
            $this->fail(ResponseCode::BUSINESS_INVALID_FILE);
        }
        if (empty($params['account_id'])) {
            $roomInfo = vss_model()->getRoomsModel()->findByRoomId($params['room_id']);
            if (empty($roomInfo)) {
                $this->fail(ResponseCode::EMPTY_ROOM);
            }
            $accountId = $roomInfo['account_id'];
        } else {
            $accountId = $params['account_id'];
        }
        $file = new \CURLFile(
            realpath($file->getPathname()),
            $file->getClientOriginalExtension(),
            $file->getClientOriginalName()
        );
        $res  = vss_service()->getPaasService()->createDocument($file);
        if ($res) {
            $createArr = [
                'app_id'      => $params['app_id'],
                'document_id' => $res['document_id'],
                'account_id'  => $accountId
            ];
            if (!empty($params['room_id'])) {
                $createArr['room_id'] = $params['room_id'];
            }

            $documentInfo = vss_service()->getPaasService()->getDocumentInfo($res['document_id']);
            if ($documentInfo) {
                $createArr['file_name'] = $documentInfo['file_name'];
                $createArr['hash']      = $documentInfo['hash'];
                $createArr['ext']       = $documentInfo['ext'];
            }
            vss_model()->getRoomDocumentsModel()->create($createArr);
            return $res;
        }
        $this->fail(ResponseCode::BUSINESS_UPLOAD_FAILED);
    }

    /**
     * 文档删除
     *
     * @param $params
     *
     * @return bool
     *
     */
    public function delete($params)
    {
        vss_validator($params, [
            'app_id'      => 'required',
            'document_id' => 'required'
        ]);
        $arr = [
            'app_id'       => $params['app_id'],
            'document_ids' => $params['document_id']
        ];

        $documentIdList = explode(',', $params['document_id']);
        foreach ($documentIdList as $documentId) {
            vss_service()->getPaasService()->deleteDocument($documentId);
            vss_model()->getRoomDocumentsModel()->where([
                'app_id'      => $params['app_id'],
                'document_id' => $documentId
            ])->forcedelete();
        }
        return true;
    }

    /**
     * 文档列表
     *
     * @param $params
     *
     * @return mixed
     *
     */
    public function lists($params)
    {
        $query = vss_model()->getRoomDocumentsModel()->newQuery();
        if (empty($params['is_back'])) {
            vss_validator($params, [
                'app_id'     => 'required',
                'room_id'    => ['required_without:account_id'],
                'account_id' => 'required_without:room_id',
                'curr_page'  => 'filled',
                'page_size'  => 'filled',
            ]);

            if (empty($params['account_id'])) {
                $roomInfo = vss_model()->getRoomsModel()->findByRoomId($params['room_id']);
                if (empty($roomInfo)) {
                    $this->fail(ResponseCode::EMPTY_ROOM);
                }
                $accountId = $roomInfo['account_id'];
            } else {
                $accountId = $params['account_id'];
            }
            if (!empty($params['app_id'])) {
                $query->where('app_id', $params['app_id']);
            }
            if (!empty($accountId)) {
                $query->where('account_id', $accountId);
            }
        }

        $page     = !empty($params['curr_page']) ? $params['curr_page'] : 1;
        $pagesize = !empty($params['page_size']) ? $params['page_size'] : 20;

        if (!empty($params['begin_time'])) {
            $query->where('created_at', '>=', "{$params['begin_time']}");
        }

        if (!empty($params['end_time'])) {
            $query->where('created_at', '<=', "{$params['end_time']} 23:59:59");
        }

        if (!empty($params['file_name'])) {
            $query->where('file_name', 'like', '%' . $params['file_name'] . '%');
        }

        if (!empty($params['transform_schedule'])) {
            $query->where('trans_status', $params['transform_schedule']);
        }
        if (!empty($params['document_id'])) {
            $query->whereIn('document_id', explode(',', $params['document_id']));
        }

        $list = $query->selectRaw('*')->orderBy('created_at', 'desc')->paginate($pagesize, ['*'], 'page', $page);
        $list = json_decode(json_encode($list, JSON_UNESCAPED_UNICODE), true);
        return [
            'detail'     => $list['data'],
            'total'      => $list['total'],
            'total_page' => $list['last_page'],
            'curr_page'  => $list['current_page'],
        ];
    }

    /**
     * 文档信息
     *
     * @param $params
     *
     * @return mixed
     *
     */
    public function info($params)
    {
        vss_validator($params, [
            'app_id'      => 'required',
            'document_id' => 'required'
        ]);

        return vss_service()->getPaasService()->getDocumentInfo($params['document_id']);
    }

    public function status($params)
    {
        vss_logger()->info('doc status', $params);
        vss_validator($params, [
            'document_id'        => 'required',
            'old_converted_page' => 'numeric',
            'page'               => 'numeric',
            'old_status'         => '',
            'type'               => 'required'
        ]);

        $status        = $params['old_status'];
        $convertedPage = $params['old_converted_page'];
        $type          = 'doc_convert';
        if ($params['type'] == 'static') {
            $type = 'doc_convert_jpeg';
        }
        $sendDataArr = [
            'page'           => $params['page'],
            'document_id'    => $params['document_id'],
            'converted_page' => $convertedPage,
            'status'         => $status,
        ];
        $sendData    = [
            'type' => $type,
            'data' => $sendDataArr,
        ];
        $document    = vss_model()->getRoomDocumentsModel()->where(['document_id' => $params['document_id']])->first();
        $roomInfo    = vss_model()->getRoomsModel()->findByRoomId($document->room_id);
        if (empty($roomInfo)) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }
        return vss_service()->getPaasChannelService()->sendMessage($roomInfo->room_id, $sendData);
    }

    /**
     * 文档信息
     *
     * @param $params
     *
     * @return mixed
     *
     */
    public function watchInfo($params)
    {
        vss_validator($params, [
            'channel' => 'required'
        ]);
        return vss_service()->getPaasService()->getDocumentWatchInfo($params['channel']);
    }

    /**
     * 统计信息
     *
     * @param $params
     *
     * @return int|mixed
     */
    public function getStat($params)
    {
        if (isset($params['created_at']) && !empty($params['created_at'])) {
            if (isset($params['end_date']) && !empty($params['end_date'])) {
                $condition = [
                    ['created_at', '>=', "{$params['created_at']}"],
                    ['created_at', '<=', "{$params['end_date']} 23:59:59"]
                ];
            } else {
                $condition = [['created_at', '>=', "{$params['created_at']}"]];
            }
        }
        $condition['app_id'] = $params['app_id'];
        if (isset($params['room_id']) && !empty($params['room_id'])) {
            $condition['room_id'] = $params['room_id'];
        }
        if (isset($params['account_id']) && !empty($params['account_id'])) {
            $condition['account_id'] = $params['account_id'];
        }

        $num = vss_model()->getRoomDocumentsModel()->where($condition)->count();
        return $num > 0 ? $num : 0;
    }

    /**
     * lite使用文档信息
     *
     * @param $params
     *
     * @return mixed
     *
     */
    public function getInfo($params)
    {
        vss_validator($params, [
            'app_id'      => 'required',
            'document_id' => 'required',
        ]);

        if (isset($params['account_id']) && !empty($params['account_id'])) {
            $condition['account_id'] = $params['account_id'];
        }

        $condition['app_id']      = $params['app_id'];
        $condition['document_id'] = $params['document_id'];
        $res                      = vss_model()->getRoomDocumentsModel()->where($condition)->first();
        if ($res->id < 1) {
            $this->fail(ResponseCode::EMPTY_DOCUMENT);
        }
        return $res->toArray();
    }

    /**
     * 信息更新
     *
     * @param $params
     *
     * @return mixed|void
     *
     */
    public function updateInfo($params)
    {
        //获取文档信息
        $documentInfo = vss_service()->getPaasService()->getDocumentInfo($params['document_id']);
        $model        = vss_model()->getRoomDocumentsModel()->where(['document_id' => $params['document_id']])->first();
        if (empty($model)) {
            $this->fail(ResponseCode::EMPTY_DOCUMENT);
        }

        $data = [
            'trans_status' => $documentInfo['trans_status'],
            'page'         => $documentInfo['page'],
            'status_jpeg'  => $documentInfo['status_jpeg'],
            'status_swf'   => $documentInfo['status_swf'],
            'status'       => $documentInfo['status'],
        ];

        $res = vss_model()->getRoomDocumentsModel()->where(['document_id' => $params['document_id']])->update($data);
        if (!$res) {
            $this->fail(ResponseCode::BUSINESS_EDIT_FAILED);
        }
    }

    public function exportList($keyword, $beginTime, $endTime, $transformSchedule)
    {
        //Excel文件名
        $fileName = 'DocumentList' . date('YmdHis');
        $header             = ['文档ID', '文档名称', '上传人', '上传时间', '页数', '转码状态'];
        $exportProxyService = vss_service()->getExportProxyService()->init($fileName)->putRow($header);

        //列表数据
        $page     = 1;
        $pageSize = 3000;
        while (true) {
            //当前page下列表数据
            $condition    = [
                'keyword'            => $keyword,
                'begin_time'         => $beginTime,
                'end_time'           => $endTime,
                'transform_schedule' => $transformSchedule,
            ];
            $documentList = vss_model()->getRoomDocumentsModel()->setPerPage($pageSize)->getList(
                $condition,
                ['account'],
                $page
            );
            if (!empty($documentList->items())) {
                foreach ($documentList->items() as $documentItem) {
                    $row = [
                        $documentItem['document_id'] ?: '-',
                        $documentItem['name'] ?: ' -',
                        $documentItem['account']['nickname'] ?: '-',
                        $documentItem['created_at'] ?: ' -',
                        $documentItem['page_total'] ?: '-',
                        $documentItem['transform_schedule_str'] ?: '-'
                    ];
                    $exportProxyService->putRow($row);
                }
            }

            //跳出while
            if ($page >= $documentList->lastPage() || $page >= 10) { //1页表示1W上限
                break;
            }

            //下一页
            $page++;
        }

        //下载文件
        $exportProxyService->download();;
    }
}
