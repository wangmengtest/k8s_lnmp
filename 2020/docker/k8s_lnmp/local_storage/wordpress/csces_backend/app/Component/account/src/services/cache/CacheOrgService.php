<?php

namespace App\Component\account\src\services\cache;

use App\Component\account\src\constants\AccountOrgConstant;
use Vss\Common\Services\WebBaseService;
use Vss\Traits\CacheTrait;
use Vss\Traits\SingletonTrait;
/**
 * CacheOrgService
 */
class CacheOrgService extends WebBaseService
{
    use CacheTrait;
    use SingletonTrait;

    /** 缓存组织架构数据-无人员信息
     * @param     $params
     */
    public function cacheOrgsNoneUser($key)
    {
        $this->cacheExpire = [
            AccountOrgConstant::ORGS_NONEUSER_CACHE => 3600,
        ];
        $attributes = $this->getCache(AccountOrgConstant::ORGS_NONEUSER_CACHE, $key, function () use ($key) {
            return vss_service()->getAccountOrgService()->orgListNoneUser($key);
        });
        return $attributes;
    }

    /** 缓存组织架构数据-带人员信息
     * @param     $params
     */
    public function cacheOrgList($key = 'all')
    {
        $this->cacheExpire = [
            AccountOrgConstant::ORGS_HASUSER_CACHE => 3600,
        ];
        $attributes = $this->getCache(AccountOrgConstant::ORGS_HASUSER_CACHE, $key, function () use ($key) {
            return vss_service()->getAccountOrgService()->orgList($key);
        });
        return $attributes;
    }
}
