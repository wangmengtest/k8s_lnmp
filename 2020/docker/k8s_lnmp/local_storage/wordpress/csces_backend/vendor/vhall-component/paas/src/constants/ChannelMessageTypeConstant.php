<?php
/**
 * Created by PhpStorm.
 * User: zhangxz
 * Date: 2018/9/10
 * Time: 下午7:21
 */

namespace vhallComponent\paas\constants;

class ChannelMessageTypeConstant
{
    // 公告
    const  ANNOUNCEMENT = 'ANNOUNCEMENT';

    // 踢出
    const KICK = 'KICK';

    // 取消提出
    const DISABLE_KICK = 'DISABLE_KICK';

    // 禁言
    const GAG = 'GAG';

    // 取消禁言
    const DISABLE_GAG = 'DISABLE_GAG';

    // 全体禁言
    const GAG_ALL = 'GAG_ALL';

    // 取消全体禁言
    const DISABLE_GAG_ALL = 'DISABLE_GAG_ALL';

    // 增加人数
    const INCREMENT_ONLINE = 'INCREMENT_ONLINE';

    // 开始直播
    const BEGIN_LIVE = 'BEGIN_LIVE';

    // 结束直播
    const FINISH_LIVE = 'FINISH_LIVE';

    // 封停房间
    const DISABLE_LIVE = 'DISABLE_LIVE';

    // 回放下载
    const DOWNLOAD = 'DOWNLOAD';

    // 微信邀请测试
    const WECHAT_INVITE_TEST = 'WECHAT_INVITE_TEST';

    // 商品上架
    const GOODS_ADDED = 'GOODS_ADDED';

    // 商品置顶
    const GOODS_TOP = 'GOODS_TOP';

    // 参加红包活动消息
    const RED_PACKET_JOIN = 'RED_PACKET_JOIN';

    // 充值成功
    const RECHARGE_SUCCESS = 'RECHARGE_SUCCESS';

    const USER_IMPORT = 'USER_IMPORT';

    const CLOSE_SERVICE = 'CLOSE_SERVICE';

    const DELETE_MESSAGE = 'DELETE_MESSAGE';
}
