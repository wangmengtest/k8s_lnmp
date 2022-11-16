<?php
/**
 * Class ChatController
 * 聊天组件
 * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * @Author: michael
 * @Date  : 2019/9/30 16:57
 * @Link http://chandao.ops.vhall.com:3000/project/28/interface/api/cat_55
 */

namespace App\Component\chat\src\controllers\v2;

use App\Constants\ResponseCode;
use App\Http\services\FileUpload;
use Illuminate\Http\Request;
use vhallComponent\decouple\controllers\BaseController;
use vhallComponent\room\constants\RoomJoinRoleNameConstant;

class ChatController extends BaseController
{
    /**
     * Notes: 获取在线用户列表
     * Author: michael
     * Date: 2019/9/30
     * Time: 17:11
     */
    public function getOnlineListAction()
    {
        $roomId    = $this->getPost('room_id');
        $page      = $this->getPost('page', 1);
        $pageSize  = $this->getPost('pagesize', 20);
        $condition = [
            'nickname' => $this->getPost('nickname')
        ];

        $data = vss_service()->getRoomService()->getOnlineList($roomId, $page, $pageSize, $condition);
        $this->success([
            'total'    => vss_service()->getRoomService()->getOnlineCount($roomId),
            'page'     => $page,
            'pagesize' => $pageSize,
            'list'     => $data
        ]);
    }

    /**
     * Notes: 获取禁言用户列表
     * Author: michael
     * Date: 2019/9/30
     * Time: 17:11
     */
    public function getBannedListAction()
    {
        $roomId   = $this->getPost('room_id');
        $page     = $this->getPost('page', 1);
        $pageSize = $this->getPost('pagesize', 10);

        $data = vss_service()->getRoomService()->getBannedList($roomId, $page, $pageSize);
        $this->success([
            'total'    => $data->total(),
            'page'     => $data->currentPage(),
            'pagesize' => $data->perPage(),
            'list'     => $data->items()
        ]);
    }

    /**
     * Notes: 获取踢出用户列表
     * Author: michael
     * Date: 2019/9/30
     * Time: 17:11
     */
    public function getKickedListAction()
    {
        $roomId   = $this->getPost('room_id');
        $page     = $this->getPost('page', 1);
        $pageSize = $this->getPost('pagesize', 10);

        $data = vss_service()->getRoomService()->getKickedList($roomId, $page, $pageSize);
        $this->success([
            'total'    => $data->total(),
            'page'     => $data->currentPage(),
            'pagesize' => $data->perPage(),
            'list'     => $data->items()
        ]);
    }

    /**
     * Notes: 全体禁言
     * Author: michael
     * Date: 2019/10/8
     * Time: 10:41
     *
     */
    public function setAllBannedAction()
    {
        $params = $this->getPost();
        vss_validator($params, [
            'room_id' => 'required',
            'type'    => 'required'
        ]);
        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        if ($join_user->role_name == RoomJoinRoleNameConstant::USER) {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }
        vss_service()->getInavService()->setAllBanned($join_user, $params['type']);
        $this->success();
    }

    /**
     * Notes: 发送公告
     * Author: michael
     * Date: 2019/10/8
     * Time: 10:47
     */
    public function sendNoticeAction()
    {
        vss_service()->getInavService()->sendNotice($this->getPost());
        $this->success();
    }

    /**
     * Notes: 发送自定义消息
     * Author: michael
     * Date: 2019/10/8
     * Time: 11:14
     */
    public function customSendAction()
    {
        $this->success(vss_service()->getChatService()->customSend($this->getParam()));
    }

    /**
     * Notes:聊天图片上传
     * Author: michael
     * Date: 2019/10/8
     * Time: 11:20
     */
    public function uploadAction(FileUpload $fileUpload, Request $request)
    {
        try {
            $params = $this->getParam();
            if($request->file('file')){
                $url = $fileUpload->store('file', $params['ext'] ?? 'img');
            }
            //$file = isset($_FILES['file']) ? 'file' : ($params['file'] ?? '');
            //$file = vss_service()->getUploadService()->uploadImg($file, false, $params['ext'] ?? '');
        } catch (Exception $e) {
            vss_logger()->error('ChatService:upload error', [
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
            $this->fail(ResponseCode::BUSINESS_UPLOAD_FAILED);
        }
        $data = ['url'=>$url];
        //$data = vss_service()->getChatService()->upload($this->getParam());
        $this->success($data);
    }

    /**
     * 聊天历史消息
     * @author fym
     * @since  2021/7/29
     * @throws \Exception
     */
    public function listAction()
    {
        $this->success(vss_service()->getChatService()->lists($this->getParam()));
    }

    /**
     * 公告列表
     */
    public function noticeListsAction()
    {
        $this->success(vss_service()->getChatService()->noticeLists($this->getParam()));
    }

    /**
     * 待审核消息列表
     */
    public function auditListsAction()
    {
        $this->success(vss_service()->getChatService()->auditLists($this->getParam()));
    }

    /**
     * 获取历史消息数据
     */
    public function messageListsAction()
    {
        $this->success(vss_service()->getChatService()->messageLists($this->getParam()));
    }

    /**
     * 设置审核开关接口
     */
    public function setChannelSwitchAction()
    {
        $this->success(vss_service()->getChatService()->setChannelSwitch($this->getParam()));
    }

    /**
     * 待审核消息列表
     */
    public function getChannelSwitchAction()
    {
        $this->success(vss_service()->getChatService()->getChannelSwitch($this->getParam()));
    }

    /**
     * 待审核消息列表
     */
    public function setChannelSwitchOptionsAction()
    {
        $this->success(vss_service()->getChatService()->setChannelSwitchOptions($this->getParam()));
    }

    /**
     * 待审核消息列表
     */
    public function applyMessageSendAction()
    {
        $this->success(vss_service()->getChatService()->applyMessageSend($this->getParam()));
    }
}
