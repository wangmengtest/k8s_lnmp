<?php

namespace vhallComponent\filterWord\constants;

class FilterwordsConstant
{
    const FILTER_WORDS_CACHE_KEY      = 'filter_words:';//敏感词key
    const FILTER_WORDS_LIST_IL_ID_KEY = 'filter_words_list_il_id_';//已加载关键词列表
    const FILTER_WORDS_EXPIRE         = 86400;//过期时间
    const FILTERWORD_LIVE_STATUS      = [
        '1' => '直播',
        '2' => '回放',
    ];

    const TEMPLATE = '敏感词(支持中文、英文、数据、空格，不支持特殊字符)';
}
