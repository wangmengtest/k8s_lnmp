<?php

namespace vhallComponent\filterWord\services;

use App\Constants\ResponseCode;
use vhallComponent\export\models\ExportModel;
use vhallComponent\filterWord\constants\FilterwordsConstant;
use vhallComponent\filterWord\jobs\FilterWordJob;
use vhallComponent\filterWord\models\FilterWordsModel;
use Vss\Common\Services\WebBaseService;

/**
 * FilterWordsService
 *
 * @date     2020-10-21
 * @author   wangming
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class FilterWordsService extends WebBaseService
{

    /**
     * 敏感词校验
     *
     * @param $filerwords
     *
     * @return bool
     * @throws \Exception
     * @uses     wang-ming
     * @author   ming.wang@vhall.com
     *
     */
    public function checkFilterWords($filerwords)
    {
        if (mb_strlen($filerwords) < 1 || mb_strlen($filerwords) > 50) {
            $this->fail(ResponseCode::TYPE_INVALID_STRING_LEN_RANGE, [
                'minLen' => 1,
                'maxLen' => 50
            ]);
        }

        // 0 会被认为空
        if (is_numeric($filerwords)) {
            return true;
        }

        if (!preg_match("/^[a-zA-Z0-9\s*\x{4e00}-\x{9fa5}]+$/u", $filerwords) || empty(rtrim($filerwords))) {
            $this->fail(ResponseCode::TYPE_INVALID_STRING);
        }

        return true;
    }

    /**
     * 敏感词查询-后台
     *
     * @param $condition
     * @param $page
     * @param $pageSize
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * @author   ming.wang@vhall.com
     *
     * @uses     wang-ming
     */
    public function list($search, $page, $pageSize, $type = 0, $ilId = 0, $accountId = 0)
    {
        if ($type == 2) {
            $accountId = 0;
        }

        $condition = [
            'search'     => $search,
            'account_id' => $accountId,
            'il_id'      => $ilId,
        ];

        return vss_model()->getFilterWordsModel()->setPerPage($pageSize)->getList($condition, [], $page);
    }

    /**
     *
     * 敏感词创建
     *
     * @param string $keyword    敏感词
     * @param int    $account_id 商家id
     * @param int    $ilId       房间id
     * @param int    $userId     操作用户id
     *
     * @return FilterWordsModel
     *
     * @uses     wang-ming
     * @author   ming.wang@vhall.com
     */
    public function create($params, $accountId = 0, $userId = 0)
    {
        vss_validator($params, [
            'keyword' => 'required|string',
            'il_id'   => 'integer',
        ]);

        $keyword = $params['keyword'];
        $ilId    = $params['il_id'] ?? 0;

        $this->checkFilterWords($keyword);

        $keyword = trim($keyword); //去除首尾空格

        if (vss_model()->getFilterWordsModel()->checkFilterWordsRepeat($keyword, $accountId, $ilId)) {
            $this->fail(ResponseCode::BUSINESS_REPEAT_FILTER);
        }

        $data = [
            'keyword'    => $keyword,
            'account_id' => $accountId,
            'il_id'      => $ilId,
            'user_id'    => $userId,
        ];

        return vss_model()->getFilterWordsModel()->create($data);
    }

    /**
     *
     * 敏感词修改
     *
     * @param     $id
     * @param     $keyword
     * @param     $accountId
     * @param     $ilId
     * @param     $userId
     *
     * @return bool|FilterWordsModel
     *
     * @uses     wang-ming
     * @author   ming.wang@vhall.com
     */
    public function update($params, $accountId, $userId)
    {
        vss_validator($params, [
            'id'      => 'required|integer',
            'keyword' => 'required|string',
            'il_id'   => 'integer',
        ]);

        $id      = $params['id'];
        $keyword = $params['keyword'];
        $ilId    = $params['il_id'] ?? 0;

        $this->checkFilterWords($keyword);

        $keyword = trim($keyword); //去除首尾空格

        $word = vss_model()->getFilterWordsModel()->checkFilterWordsRepeat($keyword, $accountId, $ilId);
        if (!empty($word['id']) && $word['id'] != $id) {
            $this->fail(ResponseCode::BUSINESS_REPEAT_FILTER);
        }

        $data = [
            'keyword'    => $keyword,
            'account_id' => $accountId,
            'il_id'      => $ilId,
            'user_id'    => $userId,
        ];

        return vss_model()->getFilterWordsModel()->updateRow($id, $data);
    }

    /**
     * @param $ids
     *
     * @return bool|\Illuminate\Database\Query\Builder|FilterWordsModel
     * @author   ming.wang@vhall.com
     *
     * @uses     wang-ming
     */
    public function delete($params)
    {
        vss_validator($params, [
            'ids' => 'required|string',
        ]);

        $ids = $params['ids'];
        $ids = explode(',', $ids);

        return vss_model()->getFilterWordsModel()->delByIds($ids);
    }

    /**
     * @param     $file
     * @param     $extension
     * @param int $accountId
     * @param int $ilId
     * @param int $userId
     *
     * @return array
     *
     * @author   ming.wang@vhall.com
     *
     * @uses     wang-ming
     */
    public function importFile($file, $extension, $accountId = 0, $ilId = 0, $userId = 0)
    {
        $filename = $file->sourceFile;

        // 创建读取对象并加载Excel文件--应该支持多种版本
        $objReader = vss_service()->getUploadService()->getExcelReader($extension, $filename);

        $objExcel = $objReader->load($filename);
        // 默认使用第一个工作簿，并获取行数
        $sheet = $objExcel->getSheet(0);
        $rows  = $sheet->getHighestRow();
        if ($rows > 1000) {
            $this->fail(ResponseCode::BUSINESS_IMPORT_COUNT_OVERFLOW, ['count' => 1000]);
        }

        $num       = $total = $number = 0;
        $failWords = $importWord = $keywordsImport = [];
        // 遍历，并读取单元格内容
        for ($i = 1; $i < $rows + 1; $i++) {
            $keywords = $sheet->getCell('A' . $i)->getValue();
            $number++;
            if (!is_numeric($keywords) && ($keywords == FilterwordsConstant::TEMPLATE || empty($keywords))) {
                if ($number == $rows && !empty($importWord)) {
                    FilterWordsModel::getInstance()->insert(array_filter($importWord));
                    $importWord = [];
                }
                continue;
            }

            //参数校验
            try {
                // 去掉末尾的换行符
                $keywords = rtrim($keywords);
                $this->checkFilterWords($keywords);
            } catch (\Exception $e) {
                $failWords[] = ['keyword' => $keywords, 'message' => $e->getMessage()];
                continue;
            }

            //是否重复判断
            if (in_array($keywords, $keywordsImport) || vss_model()->getFilterWordsModel()
                    ->checkFilterWordsRepeat($keywords, $accountId, $ilId)) {
                $failWords[] = ['keyword' => $keywords, 'message' => '敏感词重复'];
            } else {
                $keywordsImport[] = $keywords;
                $importWord[]     = [
                    'account_id' => $accountId,
                    'user_id'    => $userId,
                    'keyword'    => $keywords,
                    'il_id'      => $ilId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $num++;
                $total++;
            }

            if ($num == 100 || $i == $rows) {
                if ($importWord) {
                    vss_model()->getFilterWordsModel()->inserted(array_filter($importWord));
                    $importWord = [];
                }
                $num = 0;
            }
        }

        //校验最后执行一次，以免丢失
        if ($importWord) {
            vss_model()->getFilterWordsModel()->inserted(array_filter($importWord));
        }

        // 关闭
        $objExcel->disconnectWorksheets();
        unset($objExcel);   //释放资源
        $result['success_num'] = $total;
        $result['fail_num']    = count($failWords);
        $result['fail_list']   = $failWords;

        //清理redis
        vss_model()->getFilterWordsModel()->delKey();

        return $result;
    }

    /**
     * 获取关键词
     *
     * @param $ilId
     *
     * @return false|int|mixed|string
     * @author   ming.wang@vhall.com
     *
     * @uses     wang-ming
     */
    public function getFilterWordsString($params)
    {
        vss_validator($params, [
            'il_id' => 'required|integer',
        ], ['required' => '请上传房间id']);

        $ilId = $params['il_id'];

        $words = vss_redis()->get(FilterwordsConstant::FILTER_WORDS_CACHE_KEY . $ilId);
        if (empty($words)) {
            $roomInfo = vss_model()->getRoomsModel()->getInfoByIlId($ilId);

            $wordList = vss_model()->getFilterWordsModel()->getFilterWordsArr($roomInfo['account_id'], $ilId);
            $words    = implode(',', $wordList);
            vss_redis()
                ->set(FilterwordsConstant::FILTER_WORDS_CACHE_KEY . $ilId, $words,
                    FilterwordsConstant::FILTER_WORDS_EXPIRE);
            vss_redis()->sadd(FilterwordsConstant::FILTER_WORDS_LIST_IL_ID_KEY, $ilId);
        }

        return empty($words) ? "" : $words;
    }

    /**
     * @param $ilId
     * @param $keyword
     * @param $accountId
     *
     * @return bool
     * @author   ming.wang@vhall.com
     * @uses     wang-ming
     */
    public function reportFilterWords($params, $accountId)
    {
        vss_validator($params, [
            'il_id'   => 'required|integer',
            'keyword' => 'required|string',
        ]);

        $ilId    = $params['il_id'];
        $keyword = trim($params['keyword']);

        $roomInfo = vss_model()->getRoomsModel()->getInfoByIlId($ilId);

        $time = date('Y-m-d H:i:s');
        $data = [
            'account_id'  => $accountId,
            'content'     => $keyword,
            'live_status' => $roomInfo['status'],
            'il_id'       => $ilId,
            'created_at'  => $time,
            'updated_at'  => $time,
        ];

        // 加入队列
        vss_queue()->push(new FilterWordJob($data), 20);
        return true;
    }

    /**
     * 导出发送的敏感词
     *
     * @throws \Exception
     * @author   ming.wang@vhall.com
     * @uses     wang-ming
     */
    public function exportSendFilterWords($export, $filePath)
    {
        $params = json_decode($export['params'], true);
        $ilId   = $params['il_id'];

        $roomInfo = vss_model()->getRoomsModel()->getInfoByIlIdAndAccountId($ilId);
        if (empty($roomInfo)) {
            $this->fail(ResponseCode::EMPTY_ROOM);
        }

        //表格信息
        $file   = $filePath . $export['file_name'] . '.' . $export['ext'] ?? 'csv';
        $header = ['房间ID', '房间名称', '昵称', '用户id', '时间', '消息内容', '分类'];

        $exportProxyServer = vss_service()->getExportProxyService()->init($file)->putRow($header);

        //发送敏感词列表
        for ($k = 1; $k >= 1; $k++) {
            $condition = [
                'il_id'      => $ilId,
                'begin_time' => $params['start_time'],
                'end_time'   => $params['end_time'],
            ];

            $log = vss_model()->getFilterWordsLogModel()
                ->setPerPage(5000)
                ->getList($condition, ['accounts'], $k)
                ->toArray();
            if (empty($log['data'])) {
                break;
            }

            foreach ($log['data'] as $item) {
                $row = [
                    $ilId,
                    $roomInfo['subject'],
                    $item['accounts']['nickname'] . "\t",
                    $item['account_id'],
                    " " . $item['created_at'],
                    $item['content'] . "\t",
                    FilterwordsConstant::FILTERWORD_LIVE_STATUS[$item['live_status']],
                ];
                $exportProxyServer->putRow($row);
            }
        }

        //写入文件
        $exportProxyServer->close();
    }

    /**
     * 导出消息配置
     *
     * @param $accountId
     * @param $fileName
     * @param $beginTime
     * @param $endTime
     *
     * @param $ilId
     *
     * @return ExportModel
     *
     * @author  jin.yang@vhall.com
     * @date    2020-10-23
     */
    public function exportMessage($ilId, $accountId, $fileName, $beginTime, $endTime)
    {
        $accountId = $accountId ?: 0;
        $liveInfo  = vss_service()->getRoomService()->getInfoByIlId($ilId);
        if (!$accountId && empty($liveInfo)) {
            $this->fail(ResponseCode::BUSINESS_INVALID_PARAM);
        }

        $params = [
            'account_id' => $accountId,
            'il_id'      => $ilId,
            'file_name'  => $fileName,
            'start_time' => $beginTime,
            'end_time'   => $endTime,
        ];

        $insert = [
            'export'     => 'filterword',
            'il_id'      => $ilId,
            'account_id' => $accountId,
            'file_name'  => $fileName,
            'title'      => ['房间ID', '房间名称', '昵称', '用户id', '时间', '消息内容', '分类'],
            'ext'        => 'csv',
            'callback'   => 'filterWords:exportSendFilterWords',
            'params'     => json_encode($params),
        ];

        return vss_model()->getExportModel()->create($insert);
    }
}
