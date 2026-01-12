<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Test\Unit\Observer;

use Magento\Framework\App\Area;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\State;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SamJUK\FetchPriority\Api\LinkInterface;
use SamJUK\FetchPriority\Model\LinkStore;
use SamJUK\FetchPriority\Observer\ResponseBefore;

class ResponseBeforeTest extends TestCase
{
    // phpcs:ignore Generic.Files.LineLength.TooLong
    private const SAMPLE_RESPONSE_HTML = '<html><head><title>Test</title></head><body><h1>HELLO WORLD</h1></body></html>';
    // phpcs:ignore Generic.Files.LineLength.TooLong
    private const SAMPLE_RESPONSE_HTML_MULTIPLE_HEADS = '<html><head><title>Test</title></head><body><div>header</div><head><meta charset="UTF-8"></head></body></html>';

    private ResponseBefore $subject;
    private LinkStore|MockObject $linkStoreMock;
    private SecureHtmlRenderer|MockObject $secureHtmlRendererMock;
    private State|MockObject $appStateMock;
    private Observer|MockObject $observerMock;
    private Event|MockObject $eventMock;
    private HttpResponse|MockObject $responseMock;

    protected function setUp(): void
    {
        $this->linkStoreMock = $this->createMock(LinkStore::class);
        $this->secureHtmlRendererMock = $this->createMock(SecureHtmlRenderer::class);
        $this->appStateMock = $this->createMock(State::class);
        $this->responseMock = $this->createMock(HttpResponse::class);
        $this->eventMock = $this->createMock(Event::class);
        $this->observerMock = $this->createMock(Observer::class);

        $this->observerMock->method('getEvent')->willReturn($this->eventMock);
        $this->eventMock->method('getData')->with('response')->willReturn($this->responseMock);

        $this->subject = new ResponseBefore(
            $this->linkStoreMock,
            $this->secureHtmlRendererMock,
            $this->appStateMock
        );
    }

    /**
     * Test that response body is NOT modified when link store is empty.
     * Regression test for: <head> replacement even when no links exist
     */
    public function testDoesNotModifyResponseWhenNoLinksExist(): void
    {
        $this->appStateMock->method('getAreaCode')->willReturn(Area::AREA_FRONTEND);
        $this->linkStoreMock->method('get')->willReturn([]);

        $this->responseMock->expects($this->never())->method('setBody');
        $this->responseMock->expects($this->never())->method('getBody');

        $this->subject->execute($this->observerMock);
    }

    /**
     * Test that response body is NOT modified when area is adminhtml.
     * Regression test for: <head> replacement happening on non frontend routes
     */
    public function testDoesNotModifyResponseInAdminhtmlArea(): void
    {
        $this->appStateMock->method('getAreaCode')->willReturn(Area::AREA_ADMINHTML);
        $this->linkStoreMock->method('get')->willReturn([$this->createMock(LinkInterface::class)]);

        $this->responseMock->expects($this->never())->method('setBody');
        $this->responseMock->expects($this->never())->method('getBody');

        $this->subject->execute($this->observerMock);
    }

    /**
     * Test that response body is NOT modified when area is webapi_rest.
     * Regression test for: <head> replacement happening on non frontend routes (REST API)
     */
    public function testDoesNotModifyResponseInRestApiArea(): void
    {
        $this->appStateMock->method('getAreaCode')->willReturn(Area::AREA_WEBAPI_REST);
        $this->linkStoreMock->method('get')->willReturn([$this->createMock(LinkInterface::class)]);

        $this->responseMock->expects($this->never())->method('setBody');
        $this->responseMock->expects($this->never())->method('getBody');

        $this->subject->execute($this->observerMock);
    }

    /**
     * Test that response body is NOT modified when area is graphql.
     * Regression test for: <head> replacement happening on non frontend routes (GraphQL)
     */
    public function testDoesNotModifyResponseInGraphqlArea(): void
    {
        $this->appStateMock->method('getAreaCode')->willReturn(Area::AREA_GRAPHQL);
        $this->linkStoreMock->method('get')->willReturn([$this->createMock(LinkInterface::class)]);

        $this->responseMock->expects($this->never())->method('setBody');
        $this->responseMock->expects($this->never())->method('getBody');

        $this->subject->execute($this->observerMock);
    }

    /**
     * Test that response is NOT modified when getAreaCode throws an exception.
     * This can happen when the area is not set yet.
     */
    public function testDoesNotModifyResponseWhenAreaCodeNotSet(): void
    {
        $this->appStateMock->method('getAreaCode')
            ->willThrowException(new \Exception('Area code is not set'));
        $this->linkStoreMock->method('get')->willReturn([$this->createMock(LinkInterface::class)]);

        $this->responseMock->expects($this->never())->method('setBody');
        $this->responseMock->expects($this->never())->method('getBody');

        $this->subject->execute($this->observerMock);
    }

    /**
     * Test that response body IS modified when in frontend area with links.
     */
    public function testModifiesResponseInFrontendAreaWithLinks(): void
    {
        $linkMock = $this->createMock(LinkInterface::class);
        $linkMock->method('getAttrs')->willReturn([
            'rel' => 'preload',
            'href' => '/media/image.jpg',
            'as' => 'image',
            'type' => 'image/jpeg'
        ]);

        $this->appStateMock->method('getAreaCode')->willReturn(Area::AREA_FRONTEND);
        $this->linkStoreMock->method('get')->willReturn([$linkMock]);

        $this->secureHtmlRendererMock->method('renderTag')
            ->with('link', $linkMock->getAttrs())
            ->willReturn('<link rel="preload" href="/media/image.jpg" as="image" type="image/jpeg">');

        $this->responseMock->method('getBody')->willReturn(self::SAMPLE_RESPONSE_HTML);

        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with($this->callback(function ($body) {
                return str_contains($body, 'SamJUK_FetchPriority:preload')
                    && str_contains($body, '<link rel="preload"');
            }));

        $this->subject->execute($this->observerMock);
    }

    /**
     * Test that only the first <head> tag is replaced.
     */
    public function testOnlyReplacesFirstHeadTag(): void
    {
        $linkMock = $this->createMock(LinkInterface::class);
        $linkMock->method('getAttrs')->willReturn(['rel' => 'preload', 'href' => '/test.jpg']);

        $this->appStateMock->method('getAreaCode')->willReturn(Area::AREA_FRONTEND);
        $this->linkStoreMock->method('get')->willReturn([$linkMock]);

        $this->secureHtmlRendererMock->method('renderTag')->willReturn('<link rel="preload">');

        $this->responseMock->method('getBody')->willReturn(self::SAMPLE_RESPONSE_HTML_MULTIPLE_HEADS);

        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with($this->callback(function ($body) {
                // Count occurrences of our comment marker - should only be 1
                return substr_count($body, 'SamJUK_FetchPriority:preload') === 1;
            }));

        $this->subject->execute($this->observerMock);
    }
}
