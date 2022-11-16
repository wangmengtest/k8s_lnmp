<?php

namespace App\Component\room\src\constants;

class RoomInvitedConstant
{
    const INVITED_AUDIENCE_MAX = 200; //邀请观众

    const INVITED_ASSISTANT_MAX = 5; //邀请助理

    const INVITED_GUEST_MAX = 20; //邀请嘉宾

    const RECEIVE_SMS_PHONES = [
        '15533307288',
        '18519184446',
        '13821560765',
        '18810615564',
        '15210207735',
        '18320886589'
    ];

    //获取room邀请数据-加人员数据打包
    const ROOMS_GET_INVITED_ACCOUNTINFO_BY_ILID = 'rooms:getInvitedAccountInfoByIlId:';
}
