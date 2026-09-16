<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;
use SamJUK\FetchPriority\Model\Config;

class ConfigTest extends TestCase
{
    /**
     * @dataProvider flagProvider
     */
    public function testFlagsAreReadAtStoreScope(string $method, string $path): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with($path, ScopeInterface::SCOPE_STORE, null)
            ->willReturn(true);

        $this->assertTrue((new Config($scopeConfig))->$method());
    }

    public static function flagProvider(): array
    {
        return [
            ['isEnabled', 'samjuk_fetch_priority/general/enabled'],
            ['isProductMainPreloadEnabled', 'samjuk_fetch_priority/preloads/product_main'],
            ['isCategoryProductPreloadEnabled', 'samjuk_fetch_priority/preloads/category_grid'],
            ['isPageBuilderPreloadEnabled', 'samjuk_fetch_priority/preloads/pagebuilder_content'],
        ];
    }
}
