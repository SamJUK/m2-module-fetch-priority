<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Test\Unit\Observer\Catalog\Controller\Product;

use Magento\Catalog\Block\Product\View\Gallery;
use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SamJUK\FetchPriority\Model\Config;
use SamJUK\FetchPriority\Model\Links\Preload;
use SamJUK\FetchPriority\Model\Links\PreloadFactory;
use SamJUK\FetchPriority\Model\LinkStore;
use SamJUK\FetchPriority\Observer\Catalog\Controller\Product\View;

class ViewTest extends TestCase
{
    private View $subject;
    private LinkStore|MockObject $linkStoreMock;
    private PreloadFactory|MockObject $preloadFactoryMock;
    private Gallery|MockObject $galleryBlockMock;
    private Config|MockObject $configMock;
    private Observer|MockObject $observerMock;

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
        $this->galleryBlockMock = $this->createMock(Gallery::class);
        $this->configMock = $this->createMock(Config::class);
        $this->observerMock = $this->createMock(Observer::class);

        $this->subject = new View(
            $this->linkStoreMock,
            $this->preloadFactoryMock,
            $this->galleryBlockMock,
            $this->configMock
        );
    }

    public function testDoesNothingWhenModuleDisabled(): void
    {
        $this->configMock->method('isEnabled')->willReturn(false);
        $this->configMock->method('isProductMainPreloadEnabled')->willReturn(true);

        $this->linkStoreMock->expects($this->never())->method('add');

        $this->subject->execute($this->observerMock);
    }

    public function testDoesNothingWhenProductMainPreloadDisabled(): void
    {
        $this->configMock->method('isEnabled')->willReturn(true);
        $this->configMock->method('isProductMainPreloadEnabled')->willReturn(false);

        $this->linkStoreMock->expects($this->never())->method('add');

        $this->subject->execute($this->observerMock);
    }

    public function testAddsPreloadWhenBothConfigsEnabled(): void
    {
        $this->configMock->method('isEnabled')->willReturn(true);
        $this->configMock->method('isProductMainPreloadEnabled')->willReturn(true);

        $productMock = $this->createMock(Product::class);
        $productMock->method('getImage')->willReturn('product-image.jpg');

        $this->observerMock->method('getData')
            ->with('product')
            ->willReturn($productMock);

        $galleryImage = new DataObject([
            'file' => 'product-image.jpg',
            'medium_image_url' => 'https://example.com/media/product-image.jpg'
        ]);

        $collectionMock = $this->createMock(Collection::class);
        $collectionMock->method('getItems')->willReturn([$galleryImage]);

        $this->galleryBlockMock->method('getGalleryImages')->willReturn($collectionMock);

        $preloadMock = $this->createMock(Preload::class);
        $this->preloadFactoryMock->method('create')->willReturn($preloadMock);

        $this->linkStoreMock->expects($this->once())->method('add')->with($preloadMock);

        $this->subject->execute($this->observerMock);
    }

    public function testUsesFirstImageWhenMainImageNotFound(): void
    {
        $this->configMock->method('isEnabled')->willReturn(true);
        $this->configMock->method('isProductMainPreloadEnabled')->willReturn(true);

        $productMock = $this->createMock(Product::class);
        $productMock->method('getImage')->willReturn('non-existent.jpg');

        $this->observerMock->method('getData')
            ->with('product')
            ->willReturn($productMock);

        $galleryImage = new DataObject([
            'file' => 'different-image.jpg',
            'medium_image_url' => 'https://example.com/media/first-image.jpg'
        ]);

        $collectionMock = $this->createMock(Collection::class);
        $collectionMock->method('getItems')->willReturn([$galleryImage]);
        $collectionMock->method('getFirstItem')->willReturn($galleryImage);

        $this->galleryBlockMock->method('getGalleryImages')->willReturn($collectionMock);

        $preloadMock = $this->createMock(Preload::class);
        $this->preloadFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($args) {
                return $args['href'] === 'https://example.com/media/first-image.jpg';
            }))
            ->willReturn($preloadMock);

        $this->linkStoreMock->expects($this->once())->method('add');

        $this->subject->execute($this->observerMock);
    }
}
