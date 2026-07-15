<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use SamJUK\FetchPriority\Api\LinkInterface;
use SamJUK\FetchPriority\Model\LinkStore;

class LinkStoreTest extends TestCase
{
    private LinkStore $subject;

    protected function setUp(): void
    {
        $this->subject = new LinkStore();
    }

    public function testGetReturnsEmptyArrayByDefault(): void
    {
        $this->assertSame([], $this->subject->get());
    }

    public function testAddStoresLink(): void
    {
        $link = $this->createMock(LinkInterface::class);
        $link->method('getAttrs')->willReturn(['href' => '/a.jpg']);

        $this->subject->add($link);

        $this->assertCount(1, $this->subject->get());
        $this->assertSame($link, $this->subject->get()[0]);
    }

    public function testAddReturnsInstanceForChaining(): void
    {
        $link = $this->createMock(LinkInterface::class);
        $link->method('getAttrs')->willReturn(['href' => '/a.jpg']);

        $result = $this->subject->add($link);

        $this->assertSame($this->subject, $result);
    }

    public function testMultipleLinksCanBeAdded(): void
    {
        $link1 = $this->createMock(LinkInterface::class);
        $link1->method('getAttrs')->willReturn(['href' => '/a.jpg']);
        $link2 = $this->createMock(LinkInterface::class);
        $link2->method('getAttrs')->willReturn(['href' => '/b.jpg']);
        $link3 = $this->createMock(LinkInterface::class);
        $link3->method('getAttrs')->willReturn(['href' => '/c.jpg']);

        $this->subject->add($link1)->add($link2)->add($link3);

        $links = $this->subject->get();
        $this->assertCount(3, $links);
        $this->assertSame($link1, $links[0]);
        $this->assertSame($link2, $links[1]);
        $this->assertSame($link3, $links[2]);
    }

    public function testAddingLinkWithIdenticalAttrsIsDeduped(): void
    {
        $link1 = $this->createMock(LinkInterface::class);
        $link1->method('getAttrs')->willReturn(['href' => '/same.jpg', 'as' => 'image']);
        $link2 = $this->createMock(LinkInterface::class);
        $link2->method('getAttrs')->willReturn(['href' => '/same.jpg', 'as' => 'image']);

        $this->subject->add($link1)->add($link2);

        $links = $this->subject->get();
        $this->assertCount(1, $links);
        $this->assertSame(['href' => '/same.jpg', 'as' => 'image'], $links[0]->getAttrs());
    }

    public function testAddingLinkWithDifferentAttrsIsNotDeduped(): void
    {
        $link1 = $this->createMock(LinkInterface::class);
        $link1->method('getAttrs')->willReturn(['href' => '/one.jpg']);
        $link2 = $this->createMock(LinkInterface::class);
        $link2->method('getAttrs')->willReturn(['href' => '/two.jpg']);

        $this->subject->add($link1)->add($link2);

        $this->assertCount(2, $this->subject->get());
    }
}
