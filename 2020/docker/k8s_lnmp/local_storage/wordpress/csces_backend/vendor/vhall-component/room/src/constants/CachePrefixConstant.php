<?php

namespace vhallComponent\room\constants;

class CachePrefixConstant
{
    //上麦互动参数
    const SPEAKER_MAX_NUM = 6;  //允许上麦人数

    const INVITE_VALID_TIME = 30;   //邀请有效时间

    const HANDSUP_VALID_TIME = 30;  //举手有效时间

    //互动
    const INTERACT_INVITE = 'op:interact:invite:';

    const INTERACT_SPEAKER = 'op:interact:speaker:';

    const INTERACT_HANDSUP = 'op:interact:handsup:'; //1申请 2同意 3拒绝 4取消

    const INTERACT_GLOBAL = 'op:interact:global:';

    const INTERACT_TOOL = 'op:interact:tool:'; // 房间下其他互动工具的信息

    const INTERACT_TOOL_RECORDS = 'op:interact:tool:records:'; // 房间下其他互动工具浮窗记录

    //房间
    const ROOM_GLOBAL = 'op:room:global:';
}
