<?php


namespace vhallComponent\document\controllers\v2;

use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;

/**
 * ExamControllerTrait
 *
 * @uses     yangjin
 * @date     2020-07-21
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class DocumentController extends BaseController
{
    /**
     * @return ValidatorUtils
     */
    public function validate($data, $rules, $messages = [], $customAttributes = [])
    {
        return new ValidatorUtils($data, $rules, $messages, $customAttributes);
    }

    public function deleteAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getDocumentService()->delete($params));
    }

    public function uploadAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getDocumentService()->upload($params));
    }

    /**
     * 设置文档白板权限
     *
     */
    public function setDocPermissionAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'room_id' => 'required',
            'receive_account_id' => 'required',
        ]);
        $join_user = vss_service()->getTokenService()->getCurrentJoinUser($params['room_id']);
        $receive_join_user = $this->getJoinUser($params['receive_account_id'], $params['room_id']);
        if ($join_user->role_name != \vhallComponent\room\constants\RoomJoinRoleNameConstant::HOST) {
            $this->fail(ResponseCode::AUTH_NOT_PERMISSION);
        }
        vss_service()->getInavService()->setDocPermission($receive_join_user);
        $this->success();
    }

    public function getJoinUser($account_id, $room_id)
    {
        $join_user = vss_model()->getRoomJoinsModel()->findByAccountIdAndRoomId($account_id, $room_id);
        empty($join_user) && $this->fail(ResponseCode::BUSINESS_INVALID_USER);
        return $join_user;
    }

    public function listsAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getDocumentService()->lists($params));
    }

    /**
     * 统计文档
     */
    public function statAction()
    {
        $this->success(vss_service()->getDocumentService()->getStat($this->getParam()));
    }

    /**
     * 文档详情
     */
    public function infoAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getDocumentService()->getInfo($params));
    }

    /**
     * 更新相关信息
     */
    public function updateInfoAction()
    {
        $params = $this->getParam();
        $this->success(vss_service()->getDocumentService()->updateInfo($params));
    }
}
