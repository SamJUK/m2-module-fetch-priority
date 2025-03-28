<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    private const XML_PATH_ENABLED = 'samjuk_fetch_priority/general/enabled';
    private const XML_PATH_PRELOAD_PRODUCT_MAIN = 'samjuk_fetch_priority/preloads/product_main';
    private const XML_PATH_PRELOAD_CATEGORY_PRODUCTS = 'samjuk_fetch_priority/preloads/category_grid';
    private const XML_PATH_PRELOAD_PAGEBUILDER_CONTENT = 'samjuk_fetch_priority/preloads/pagebuilder_content';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) { }

    public function isEnabled()
    {
        return $this->getFlag(self::XML_PATH_ENABLED);
    }

    public function isProductMainPreloadEnabled()
    {
        return $this->getFlag(self::XML_PATH_PRELOAD_PRODUCT_MAIN);
    }

    public function isCategoryProductPreloadEnabled()
    {
        return $this->getFlag(self::XML_PATH_PRELOAD_CATEGORY_PRODUCTS);
    }

    public function isPageBuilderPreloadEnabled()
    {
        return $this->getFlag(self::XML_PATH_PRELOAD_PAGEBUILDER_CONTENT);
    }

    private function getFlag($path, $scope = 'default', $scopeCode = null)
    {
        return (bool)$this->scopeConfig->isSetFlag($path, $scope, $scopeCode);
    }

    private function getValue($path, $scope = 'default', $scopeCode = null)
    {
        return $this->scopeConfig->getValue($path, $scope, $scopeCode);
    }
}
