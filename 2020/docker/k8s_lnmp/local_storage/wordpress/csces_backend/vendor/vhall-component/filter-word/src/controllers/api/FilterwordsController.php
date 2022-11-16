<?php

namespace vhallComponent\filterWord\controllers\api;

use vhallComponent\decouple\controllers\BaseController;

class FilterwordsController extends BaseController
{
    /**
     * 获取敏感词
     *
     *
     */
    public function getFilterWordsAction()
    {
        $words = vss_service()->getFilterWordsService()->getFilterWordsString($this->getParam());
        $this->success($words);
    }

    /**
     * 上报敏感词
     *
     *
     * @author   ming.wang@vhall.com
     * @uses     wang-ming
     */
    public function reportFilterWordsAction()
    {
        $words = vss_service()->getFilterWordsService()->reportFilterWords(
            $this->getParam(),
            $this->accountInfo['account_id']
        );
        $this->success($words);
    }
}
