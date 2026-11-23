<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_ENABLED = 'samjuk_fetch_priority/general/enabled';
    private const XML_PATH_PRELOAD_PRODUCT_MAIN = 'samjuk_fetch_priority/preloads/product_main';
    private const XML_PATH_PRELOAD_CATEGORY_PRODUCTS = 'samjuk_fetch_priority/preloads/category_grid';
    private const XML_PATH_PRELOAD_PAGEBUILDER_CONTENT = 'samjuk_fetch_priority/preloads/pagebuilder_content';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->getFlag(self::XML_PATH_ENABLED);
    }

    public function isProductMainPreloadEnabled(): bool
    {
        return $this->getFlag(self::XML_PATH_PRELOAD_PRODUCT_MAIN);
    }

    public function isCategoryProductPreloadEnabled(): bool
    {
        return $this->getFlag(self::XML_PATH_PRELOAD_CATEGORY_PRODUCTS);
    }

    public function isPageBuilderPreloadEnabled(): bool
    {
        return $this->getFlag(self::XML_PATH_PRELOAD_PAGEBUILDER_CONTENT);
    }

    /**
     * @param null|int|string $scopeCode
     */
    private function getFlag(string $path, string $scope = ScopeInterface::SCOPE_STORE, $scopeCode = null): bool
    {
        return (bool)$this->scopeConfig->isSetFlag($path, $scope, $scopeCode);
    }

    /**
     * @param null|int|string $scopeCode
     */
    private function getValue(string $path, string $scope = ScopeInterface::SCOPE_STORE, $scopeCode = null): mixed
    {
        return $this->scopeConfig->getValue($path, $scope, $scopeCode);
    }
}
