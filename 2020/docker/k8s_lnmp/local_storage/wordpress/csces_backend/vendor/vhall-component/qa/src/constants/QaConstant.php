<?php


namespace vhallComponent\qa\constants;

class QaConstant
{
    /**
     * 问题状态
     */
    const QUESTION_STATUS_PENDING = 0; // 待处理

    const QUESTION_STATUS_NOT_REPLY = 1; // 不处理

    const QUESTION_STATUS_TEXT_REPLY = 3; // 文字回复

    const QUESTION_STATUS_LIVE_REPLY = 4; // 直播中回复

    const QUESTION_STATUS_MAP = [
        self::QUESTION_STATUS_PENDING    => '待处理',
        self::QUESTION_STATUS_NOT_REPLY  => '不处理',
        self::QUESTION_STATUS_TEXT_REPLY => '文字回复',
        self::QUESTION_STATUS_LIVE_REPLY => '语音回复',
    ];

    // 导出类型
    const EXPORT_QA = 'qa';

    /**
     * 问答用户角色
     */
    const ROLE_NAME_MAP = [
        '1' => '主持人',
        '2' => '观众',
        '3' => '助理',
        '4' => '嘉宾'
    ];
}
