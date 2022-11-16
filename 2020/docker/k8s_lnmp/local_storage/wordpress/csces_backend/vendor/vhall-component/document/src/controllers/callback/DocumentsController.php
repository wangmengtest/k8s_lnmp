<?php

namespace vhallComponent\document\controllers\callback;

use Vss\Common\Controllers\CallbackBaseController;

/**
 * ExamControllerTrait
 *
 * @uses     yangjin
 * @date     2020-07-21
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class DocumentsController extends CallbackBaseController
{
    /**
     * 转码成功回调 document/trans-over
     *
     * @see http://www.vhallyun.com/docs/show/1060.html
     */
    public function eventDocumentTransOver()
    {
        $this->syncDocuments();
    }

    /**
     * 数据同步
     *
     */
    public function syncDocuments()
    {
        //0、 如果存在 room_id, 则送消息通知转码完成
        $roomId = vss_model()->getRoomDocumentsModel()
            ->where(['document_id' => $this->params['document_id']])
            ->value('room_id');
        if ($roomId) {
            vss_service()->getPaasChannelService()->sendMessage($roomId, [
                'type'        => 'document_trans_over',
                'document_id' => $this->params['document_id'],
            ]);
        }

        //1、获取文档信息
        vss_service()->getDocumentService()->updateInfo($this->params);
    }
}
