<?php

namespace vhallComponent\access\controllers\v2;

use vhallComponent\decouple\controllers\BaseController;

/**
 * AccessController extends BaseController
 *
 * @uses     yangjin
 * @date     2020-07-31
 * @author   jin.yangjin@vhall.com
 * @license  PHP Version 7.3.x {@link http://www.php.net/license/3_0.txt}
 */
class AccessController extends BaseController
{
    /**
     * 获取权限列表
     */
    public function listAction()
    {
        $this->success(vss_service()->getAccessService()->getList($page = 1, $pageSize = 20));
    }

    /**
     * 获取用户的操作记录
     *
     */
    public function getLogAction()
    {
        $params    = $this->getParam();
        vss_validator($params, [
            'account_id' => '',
            'page'       => '',
        ]);

        $this->success(
            vss_service()->getAccessService()
                ->getAccessLog(
                    $params['account_id'],
                    $params['page'] ?? 1
                )
        );
    }

    /**
     * 获取用户权限列表
     *
     */
    public function userListAction()
    {
        $params    = $this->getParam();
        vss_validator($params, [
            'account_id' => 'required',
            'app_id'     => 'required'
        ]);
        $this->success(
            vss_service()->getAccessService()
                ->getAccessListByUid(
                    $params['account_id'],
                    $params['app_id']
                )
        );
    }

    /**
     * 导入权限表
     */
    public function addAction()
    {
        $data = [
            100001 => 'logo下显示登录注册',
            100002 => '直播助手',
        ];
        $data = [
            '第三方推流'         => 10001,
            '打点录制'          => 10002,
            '分享 -- 重复了'     => 10003,
            '播放器控制条'        => 10004,
            '清晰度选择'         => 10005,
            '倍速播放'          => 10006,
            '弹幕'            => 10007,
            '开启旁路推流'        => 10008,
            '设置旁路布局'        => 10009,
            '设置大屏显示'        => 10010,
            '设置主讲人'         => 10011,
            '操作上麦申请（同意、拒绝）' => 10012,
            '邀请上麦'          => 10013,
            '申请上麦'          => 10014,
            '开关自己音视频'       => 10015,
            '开关他人音视频'       => 10016,
            '下麦自己'          => 10017,
            '下麦他人'          => 10018,
            '全屏'            => 10019,
            '举手开关'          => 10020,
            '举手'            => 10021,
            '举手列表'          => 10022,
            '文档上传'          => 11001,
            '文档开关'          => 11002,
            '文档演示'          => 11003,
            '文档翻页'          => 11004,
            '文档画笔'          => 11005,
            '白板'            => 11006,
            '成员列表'          => 12001,
            '踢出/恢复'         => 12002,
            '禁言/恢复'         => 12003,
            '禁言踢出列表'        => 12004,
            '聊天审核'          => 12005,
            '全员禁言'          => 12006,
            '回放'            => 13001,
            '红包'            => 14001,
            '抽奖'            => 15001,
            '问卷'            => 16001,
            '问答'            => 17001,
            '签到'            => 18001,
            '点赞'            => 19001,
            '礼物'            => 20001,
            '分享'            => 21001,
            '打赏'            => 22001,
        ];


        $data = [
            '公告'   => 23001,
            '公告发送' => 23002,
        ];

        $this->success(vss_service()->getAccessService()->add($data));
    }
}
