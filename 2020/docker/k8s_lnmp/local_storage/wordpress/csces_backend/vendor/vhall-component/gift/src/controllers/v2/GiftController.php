<?php
namespace vhallComponent\gift\controllers\v2;

use App\Constants\ResponseCode;
use vhallComponent\decouple\controllers\BaseController;

class GiftController extends BaseController
{
    protected $userInfo;

    protected $roomInfo;

    /**
     * @return ValidatorUtils
     */
    public function validate($data, $rules, $messages = [], $customAttributes = [])
    {
        return new ValidatorUtils($data, $rules, $messages, $customAttributes);
    }

    /**
     * Notes: 保存礼物关联关系
     * Author: michael
     * Date: 2019/10/8
     * Time: 16:27
     */
    public function maapingSaveAction()
    {
        $this->success(vss_service()->getGiftService()->mappingSave($this->getParam()));
    }

    /**
     * Notes: 创建礼物
     * Author: michael
     * Date: 2019/10/8
     * Time: 16:29
     */
    public function createAction()
    {
        $this->success(vss_service()->getGiftService()->create($this->getParam()));
    }

    /**
     * Notes: 删除礼物
     * Author: michael
     * Date: 2019/10/8
     * Time: 16:27
     */
    public function deleteAction()
    {
        $this->success(vss_service()->getGiftService()->delete($this->getParam()));
    }

    /**
     * Notes: 更新支付状态
     * Author: michael
     * Date: 2019/10/8
     * Time: 16:27
     */
    public function setPayStatusAction()
    {
        $this->success(vss_service()->getGiftService()->setPayStatus($this->getParam()));
    }

    /**
     * Notes: 编辑礼物
     * Author: michael
     * Date: 2019/10/8
     * Time: 16:27
     */
    public function updateAction()
    {
        $this->success(vss_service()->getGiftService()->update($this->getParam()));
    }

    /**
     * Notes: 送礼物
     * Author: michael
     * Date: 2019/10/8
     * Time: 16:27
     */
    public function sendAction()
    {
        $params = $this->getParam();
        $this->userInfo = vss_service()->getRoomService()->getUserInfoByAccountId(
            $params['room_id'],
            $params['third_party_user_id']
        );

        if (!empty($this->userInfo['nickname'])) {
            $params['gift_user_nickname'] = $this->userInfo['nickname'];
        }
        if (!empty($this->userInfo['avatar'])) {
            $params['gift_user_avatar'] = $this->userInfo['avatar'];
        }
        $this->success(vss_service()->getGiftService()->send($params));
    }

    /**
     * Notes: 礼物使用列表
     * Author: michael
     * Date: 2019/10/8
     * Time: 16:29
     */
    public function useListAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'room_id' => 'required',
        ]);
        $room = vss_model()->getRoomsModel()->findByRoomId($params['room_id']);
        !$room && $this->fail(ResponseCode::EMPTY_ROOM);
        $params = array_merge($params, [
            'page' => 1,
            'pagesize' => 100,
            'creator_id' => $room->account_id,
            'source_id' => $room->room_id
        ]);
        $this->success(vss_service()->getGiftService()->usedList($params));
    }


    public function listAction()
    {
        $params = $this->getParam();
        vss_validator($params, [
            'room_id' => '',
        ]);
        if ($params['room_id']) {
            $room = vss_model()->getRoomsModel()->findByRoomId($params['room_id']);
            !$room && $this->fail(ResponseCode::EMPTY_ROOM);
            $params = array_merge($params, [
                'creator_id' => $room->account_id,
                'source_id' => $room->room_id
            ]);
        }
        $this->success(vss_service()->getGiftService()->list($params));
    }
}
