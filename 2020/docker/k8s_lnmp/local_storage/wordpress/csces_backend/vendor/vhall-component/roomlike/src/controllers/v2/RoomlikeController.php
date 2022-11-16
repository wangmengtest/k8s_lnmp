<?php
/**
 * Class RoomController
 * 房间组件
 * +++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * @Author: michael
 * @Date: 2019/9/30 11:21
 * @Link http://chandao.ops.vhall.com:3000/project/28/interface/api/cat_51
 */
namespace vhallComponent\roomlike\controllers\v2;

use vhallComponent\decouple\controllers\BaseController;

class RoomlikeController extends BaseController
{
    /**
     * 点赞
     *
     */
    public function likeAction()
    {
        vss_service()->getRoomlikeService()->like($this->getParam());
        $this->success();
    }
}
