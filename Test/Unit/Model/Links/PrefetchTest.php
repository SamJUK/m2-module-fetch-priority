<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Test\Unit\Model\Links;

use PHPUnit\Framework\TestCase;
use SamJUK\FetchPriority\Api\LinkInterface;
use SamJUK\FetchPriority\Model\Links\Prefetch;

class PrefetchTest extends TestCase
{
    public function testImplementsLinkInterface(): void
    {
        $prefetch = new Prefetch('/next-page.html');

        $this->assertInstanceOf(LinkInterface::class, $prefetch);
    }

    public function testGetAttrsReturnsCorrectAttributes(): void
    {
        $prefetch = new Prefetch('/next-page.html');

        $attrs = $prefetch->getAttrs();

        $this->assertSame('prefetch', $attrs['rel']);
        $this->assertSame('/next-page.html', $attrs['href']);
        $this->assertCount(2, $attrs);
    }

    public function testGetAttrsWithDifferentHrefs(): void
    {
        $testCases = [
            '/media/catalog/product/image.jpg',
            'https://example.com/resource.js',
            '/css/styles.css',
        ];

        foreach ($testCases as $href) {
            $prefetch = new Prefetch($href);
            $this->assertSame($href, $prefetch->getAttrs()['href']);
        }
    }
}
