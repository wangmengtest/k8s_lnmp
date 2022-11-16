<?php

namespace App\Component\record\src\constants;

class RecordConstant
{
    const PAGE_SIZE  = 10;

    const PAGE_NUM   = 1;

    const STATUS_YES = 0; //正常

    const STATUS_DEL = 1;//删除

    /**
     * 平台类型
     *
     * @link http://wiki.vhallops.com/pages/viewpage.action?pageId=2491279
     */
    const TERMINAL_IOSAPP      = 1;

    const TERMINAL_ANDROIDAPP  = 2;

    const TERMINAL_FLASH       = 3;

    const TERMINAL_WAP         = 4;

    const TERMINAL_IOSSDK      = 5;

    const TERMINAL_ANDROIDSDK  = 6;

    const TERMINAL_XIAOZHUSHOU = 6;

    const TERMINAL_H5PC        = 7;

    //点播
    const RECORD_DOWN_URL = 'records:down:vod_id:';

    const RECORD_DOWN_QA_URL = 'records:down:qality:vod_id:';
}
