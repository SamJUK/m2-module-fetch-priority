<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Test\Unit\Plugin\Catalog\Block\Product\ProductList;

use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\View\Asset\Image as ViewAssetImage;
use Magento\Catalog\Model\View\Asset\ImageFactory as AssetImageFactory;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\Config\View as ViewConfig;
use Magento\Framework\View\ConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SamJUK\FetchPriority\Model\Config;
use SamJUK\FetchPriority\Model\Links\Preload;
use SamJUK\FetchPriority\Model\Links\PreloadFactory;
use SamJUK\FetchPriority\Model\LinkStore;
use SamJUK\FetchPriority\Plugin\Catalog\Block\Product\ProductList\SetCollection;

class SetCollectionTest extends TestCase
{
    private SetCollection $subject;
    private LinkStore|MockObject $linkStoreMock;
    private PreloadFactory|MockObject $preloadFactoryMock;
    private ConfigInterface|MockObject $presentationConfigMock;
    private ParamsBuilder|MockObject $imageParamsBuilderMock;
    private PlaceholderFactory|MockObject $viewAssetPlaceholderFactoryMock;
    private AssetImageFactory|MockObject $viewAssetImageFactoryMock;
    private Config|MockObject $configMock;
    private Toolbar|MockObject $toolbarMock;

    protected function setUp(): void
    {
        // phpcs:disable
        if (!class_exists(PreloadFactory::class)) {
            eval('namespace SamJUK\FetchPriority\Model\Links; class PreloadFactory { public function create() { return null; } }');
        }
        // phpcs:enable

        $this->preloadFactoryMock = $this->getMockBuilder(PreloadFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->linkStoreMock = $this->createMock(LinkStore::class);
        $this->presentationConfigMock = $this->createMock(ConfigInterface::class);
        $this->imageParamsBuilderMock = $this->createMock(ParamsBuilder::class);
        $this->viewAssetPlaceholderFactoryMock = $this->createMock(PlaceholderFactory::class);
        $this->viewAssetImageFactoryMock = $this->createMock(AssetImageFactory::class);
        $this->configMock = $this->createMock(Config::class);
        $this->toolbarMock = $this->createMock(Toolbar::class);

        $viewConfigMock = $this->createMock(ViewConfig::class);
        $viewConfigMock->method('getMediaAttributes')->willReturn(['image_type' => 'small_image']);
        $this->presentationConfigMock->method('getViewConfig')->willReturn($viewConfigMock);
        $this->imageParamsBuilderMock->method('build')->willReturn(['image_type' => 'small_image']);

        $this->subject = new SetCollection(
            $this->linkStoreMock,
            $this->preloadFactoryMock,
            $this->presentationConfigMock,
            $this->imageParamsBuilderMock,
            $this->viewAssetPlaceholderFactoryMock,
            $this->viewAssetImageFactoryMock,
            $this->configMock
        );
    }

    public function testDoesNothingWhenModuleDisabled(): void
    {
        $this->configMock->method('isEnabled')->willReturn(false);
        $this->configMock->method('isCategoryProductPreloadEnabled')->willReturn(true);

        $this->linkStoreMock->expects($this->never())->method('add');

        $this->subject->afterSetCollection($this->toolbarMock, $this->toolbarMock, []);
    }

    public function testDoesNothingWhenCategoryPreloadDisabled(): void
    {
        $this->configMock->method('isEnabled')->willReturn(true);
        $this->configMock->method('isCategoryProductPreloadEnabled')->willReturn(false);

        $this->linkStoreMock->expects($this->never())->method('add');

        $this->subject->afterSetCollection($this->toolbarMock, $this->toolbarMock, []);
    }

    public function testPreloadsProductsFromAlreadySortedCollection(): void
    {
        $this->configMock->method('isEnabled')->willReturn(true);
        $this->configMock->method('isCategoryProductPreloadEnabled')->willReturn(true);
        $this->toolbarMock->method('getCurrentMode')->willReturn('grid');

        $productMock = $this->createMock(Product::class);
        $productMock->method('getData')->with('small_image')->willReturn('product-image.jpg');

        $imageAssetMock = $this->createMock(ViewAssetImage::class);
        $imageAssetMock->method('getUrl')->willReturn('https://example.com/media/product-image.jpg');
        $this->viewAssetImageFactoryMock->method('create')->willReturn($imageAssetMock);

        $preloadMock = $this->createMock(Preload::class);
        $this->preloadFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($args) {
                return $args['href'] === 'https://example.com/media/product-image.jpg';
            }))
            ->willReturn($preloadMock);

        $this->linkStoreMock->expects($this->once())->method('add')->with($preloadMock);

        // The collection is passed as the setCollection() argument, which by the time
        // this plugin runs (afterSetCollection) already has sort order/pagination applied
        // by the original method — iterating it here does not race the toolbar's ordering.
        $result = $this->subject->afterSetCollection($this->toolbarMock, $this->toolbarMock, [$productMock]);

        $this->assertSame($this->toolbarMock, $result);
    }

    public function testStopsPreloadingAfterFourProducts(): void
    {
        $this->configMock->method('isEnabled')->willReturn(true);
        $this->configMock->method('isCategoryProductPreloadEnabled')->willReturn(true);
        $this->toolbarMock->method('getCurrentMode')->willReturn('list');

        $products = [];
        for ($i = 0; $i < 6; $i++) {
            $productMock = $this->createMock(Product::class);
            $productMock->method('getData')->with('small_image')->willReturn("product-{$i}.jpg");
            $products[] = $productMock;
        }

        $imageAssetMock = $this->createMock(ViewAssetImage::class);
        $imageAssetMock->method('getUrl')->willReturn('https://example.com/media/product.jpg');
        $this->viewAssetImageFactoryMock->method('create')->willReturn($imageAssetMock);

        $preloadMock = $this->createMock(Preload::class);
        $this->preloadFactoryMock->method('create')->willReturn($preloadMock);

        $this->linkStoreMock->expects($this->exactly(4))->method('add');

        $this->subject->afterSetCollection($this->toolbarMock, $this->toolbarMock, $products);
    }

    public function testDoesNotReprocessTheSameCollectionInstanceTwice(): void
    {
        // Regression test: Toolbar::setCollection() is called twice per category page render
        // by core Magento/Luma (same Toolbar, same collection instance) - this plugin must not
        // preload the same collection's images a second time.
        $this->configMock->method('isEnabled')->willReturn(true);
        $this->configMock->method('isCategoryProductPreloadEnabled')->willReturn(true);
        $this->toolbarMock->method('getCurrentMode')->willReturn('grid');

        $productMock = $this->createMock(Product::class);
        $productMock->method('getData')->with('small_image')->willReturn('product-image.jpg');

        $imageAssetMock = $this->createMock(ViewAssetImage::class);
        $imageAssetMock->method('getUrl')->willReturn('https://example.com/media/product-image.jpg');
        $this->viewAssetImageFactoryMock->method('create')->willReturn($imageAssetMock);

        $preloadMock = $this->createMock(Preload::class);
        $this->preloadFactoryMock->method('create')->willReturn($preloadMock);

        $this->linkStoreMock->expects($this->once())->method('add');

        $collection = new \ArrayIterator([$productMock]);

        $this->subject->afterSetCollection($this->toolbarMock, $this->toolbarMock, $collection);
        $this->subject->afterSetCollection($this->toolbarMock, $this->toolbarMock, $collection);
    }

    public function testProcessesDifferentCollectionInstancesIndependently(): void
    {
        $this->configMock->method('isEnabled')->willReturn(true);
        $this->configMock->method('isCategoryProductPreloadEnabled')->willReturn(true);
        $this->toolbarMock->method('getCurrentMode')->willReturn('grid');

        $productMock = $this->createMock(Product::class);
        $productMock->method('getData')->with('small_image')->willReturn('product-image.jpg');

        $imageAssetMock = $this->createMock(ViewAssetImage::class);
        $imageAssetMock->method('getUrl')->willReturn('https://example.com/media/product-image.jpg');
        $this->viewAssetImageFactoryMock->method('create')->willReturn($imageAssetMock);

        $preloadMock = $this->createMock(Preload::class);
        $this->preloadFactoryMock->method('create')->willReturn($preloadMock);

        $this->linkStoreMock->expects($this->exactly(2))->method('add');

        $this->subject->afterSetCollection($this->toolbarMock, $this->toolbarMock, new \ArrayIterator([$productMock]));
        $this->subject->afterSetCollection($this->toolbarMock, $this->toolbarMock, new \ArrayIterator([$productMock]));
    }
}
