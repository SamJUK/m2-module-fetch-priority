<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Test\Unit\Observer\View;

use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SamJUK\FetchPriority\Model\Config;
use SamJUK\FetchPriority\Model\Links\Preload;
use SamJUK\FetchPriority\Model\Links\PreloadFactory;
use SamJUK\FetchPriority\Model\LinkStore;
use SamJUK\FetchPriority\Observer\View\BlockToHtmlAfter;

class BlockToHtmlAfterTest extends TestCase
{
    private BlockToHtmlAfter $subject;
    private LinkStore|MockObject $linkStoreMock;
    private PreloadFactory|MockObject $preloadFactoryMock;
    private Config|MockObject $configMock;
    private Observer|MockObject $observerMock;
    private Event $eventMock;
    private DataObject $transport;

    protected function setUp(): void
    {
        $this->linkStoreMock = $this->createMock(LinkStore::class);
        $this->preloadFactoryMock = $this->createMock(PreloadFactory::class);
        $this->configMock = $this->createMock(Config::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->transport = new DataObject();

        // Create event with transport data - Event::getTransport() uses magic __call -> getData('transport')
        $this->eventMock = new Event(['transport' => $this->transport]);

        $this->observerMock->method('getEvent')->willReturn($this->eventMock);

        $this->subject = new BlockToHtmlAfter(
            $this->linkStoreMock,
            $this->preloadFactoryMock,
            $this->configMock
        );
    }

    public function testDoesNothingWhenBothConfigsDisabled(): void
    {
        // Note: The condition is: if (!isEnabled && !isPageBuilderPreloadEnabled)
        $this->configMock->method('isEnabled')->willReturn(false);
        $this->configMock->method('isPageBuilderPreloadEnabled')->willReturn(false);

        $this->linkStoreMock->expects($this->never())->method('add');

        $this->subject->execute($this->observerMock);
    }

    public function testDoesNothingWhenNoPreloadImagesFound(): void
    {
        $this->configMock->method('isEnabled')->willReturn(true);
        $this->configMock->method('isPageBuilderPreloadEnabled')->willReturn(true);

        $this->transport->setHtml('<div><img src="/image.jpg"></div>');

        $this->linkStoreMock->expects($this->never())->method('add');

        $this->subject->execute($this->observerMock);
    }

    public function testAddsPreloadForDesktopImage(): void
    {
        $this->configMock->method('isEnabled')->willReturn(true);
        $this->configMock->method('isPageBuilderPreloadEnabled')->willReturn(true);

        $html = '<img src="/media/hero-desktop.jpg" preload="Yes" data-element="desktop_image">';
        $this->transport->setHtml($html);

        $preloadMock = $this->createMock(Preload::class);
        $this->preloadFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($args) {
                return $args['href'] === '/media/hero-desktop.jpg'
                    && $args['media'] === '(min-width: 769px)';
            }))
            ->willReturn($preloadMock);

        $this->linkStoreMock->expects($this->once())->method('add')->with($preloadMock);

        $this->subject->execute($this->observerMock);
    }

    public function testAddsPreloadForMobileImage(): void
    {
        $this->configMock->method('isEnabled')->willReturn(true);
        $this->configMock->method('isPageBuilderPreloadEnabled')->willReturn(true);

        $html = '<img src="/media/hero-mobile.jpg" preload="Yes" data-element="mobile_image">';
        $this->transport->setHtml($html);

        $preloadMock = $this->createMock(Preload::class);
        $this->preloadFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($args) {
                return $args['href'] === '/media/hero-mobile.jpg'
                    && $args['media'] === '(max-width: 768px)';
            }))
            ->willReturn($preloadMock);

        $this->linkStoreMock->expects($this->once())->method('add')->with($preloadMock);

        $this->subject->execute($this->observerMock);
    }

    public function testAddsMultiplePreloadsForMultipleImages(): void
    {
        $this->configMock->method('isEnabled')->willReturn(true);
        $this->configMock->method('isPageBuilderPreloadEnabled')->willReturn(true);

        $html = '
            <img src="/media/hero-desktop.jpg" preload="Yes" data-element="desktop_image">
            <img src="/media/hero-mobile.jpg" preload="Yes" data-element="mobile_image">
        ';
        $this->transport->setHtml($html);

        $preloadMock = $this->createMock(Preload::class);
        $this->preloadFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($preloadMock);

        $this->linkStoreMock->expects($this->exactly(2))->method('add');

        $this->subject->execute($this->observerMock);
    }

    public function testIgnoresImagesWithoutPreloadAttribute(): void
    {
        $this->configMock->method('isEnabled')->willReturn(true);
        $this->configMock->method('isPageBuilderPreloadEnabled')->willReturn(true);

        $html = '
            <img src="/media/no-preload.jpg" data-element="desktop_image">
            <img src="/media/with-preload.jpg" preload="Yes" data-element="desktop_image">
            <img src="/media/preload-no.jpg" preload="No" data-element="mobile_image">
        ';
        $this->transport->setHtml($html);

        $preloadMock = $this->createMock(Preload::class);
        $this->preloadFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($args) {
                return $args['href'] === '/media/with-preload.jpg';
            }))
            ->willReturn($preloadMock);

        $this->linkStoreMock->expects($this->once())->method('add');

        $this->subject->execute($this->observerMock);
    }

    public function testExecutesWhenOnlyModuleEnabled(): void
    {
        $this->configMock->method('isEnabled')->willReturn(true);
        $this->configMock->method('isPageBuilderPreloadEnabled')->willReturn(false);

        $html = '<img src="/media/test.jpg" preload="Yes" data-element="desktop_image">';
        $this->transport->setHtml($html);

        $preloadMock = $this->createMock(Preload::class);
        $this->preloadFactoryMock->method('create')->willReturn($preloadMock);

        $this->linkStoreMock->expects($this->once())->method('add');

        $this->subject->execute($this->observerMock);
    }

    public function testExecutesWhenOnlyPageBuilderPreloadEnabled(): void
    {
        $this->configMock->method('isEnabled')->willReturn(false);
        $this->configMock->method('isPageBuilderPreloadEnabled')->willReturn(true);

        $html = '<img src="/media/test.jpg" preload="Yes" data-element="desktop_image">';
        $this->transport->setHtml($html);

        $preloadMock = $this->createMock(Preload::class);
        $this->preloadFactoryMock->method('create')->willReturn($preloadMock);

        $this->linkStoreMock->expects($this->once())->method('add');

        $this->subject->execute($this->observerMock);
    }
}
