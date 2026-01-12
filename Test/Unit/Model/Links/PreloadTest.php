<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Test\Unit\Model\Links;

use PHPUnit\Framework\TestCase;
use SamJUK\FetchPriority\Api\LinkInterface;
use SamJUK\FetchPriority\Enum\FetchPriority;
use SamJUK\FetchPriority\Enum\Preload\AsType;
use SamJUK\FetchPriority\Enum\Preload\MimeType;
use SamJUK\FetchPriority\Model\Links\Preload;

class PreloadTest extends TestCase
{
    public function testImplementsLinkInterface(): void
    {
        $preload = new Preload(
            '/media/test.jpg',
            AsType::Image,
            MimeType::ImageJPEG
        );

        $this->assertInstanceOf(LinkInterface::class, $preload);
    }

    public function testGetAttrsReturnsRequiredAttributes(): void
    {
        $preload = new Preload(
            '/media/test.jpg',
            AsType::Image,
            MimeType::ImageJPEG
        );

        $attrs = $preload->getAttrs();

        $this->assertSame('preload', $attrs['rel']);
        $this->assertSame('/media/test.jpg', $attrs['href']);
        $this->assertSame('image', $attrs['as']);
        $this->assertSame('image/jpeg', $attrs['type']);
        $this->assertArrayNotHasKey('fetchpriority', $attrs);
        $this->assertArrayNotHasKey('crossorigin', $attrs);
        $this->assertArrayNotHasKey('media', $attrs);
    }

    public function testGetAttrsIncludesFetchPriorityWhenSet(): void
    {
        $preload = new Preload(
            '/media/test.jpg',
            AsType::Image,
            MimeType::ImageJPEG,
            FetchPriority::High
        );

        $attrs = $preload->getAttrs();

        $this->assertArrayHasKey('fetchpriority', $attrs);
        $this->assertSame('high', $attrs['fetchpriority']);
    }

    public function testGetAttrsIncludesCrossOriginWhenEnabled(): void
    {
        $preload = new Preload(
            '/media/test.jpg',
            AsType::Image,
            MimeType::ImageJPEG,
            null,
            true
        );

        $attrs = $preload->getAttrs();

        $this->assertArrayHasKey('crossorigin', $attrs);
        $this->assertSame('', $attrs['crossorigin']);
    }

    public function testGetAttrsIncludesMediaQueryWhenSet(): void
    {
        $preload = new Preload(
            '/media/test.jpg',
            AsType::Image,
            MimeType::ImageJPEG,
            null,
            false,
            '(min-width: 768px)'
        );

        $attrs = $preload->getAttrs();

        $this->assertArrayHasKey('media', $attrs);
        $this->assertSame('(min-width: 768px)', $attrs['media']);
    }

    public function testGetAttrsWithAllOptionsEnabled(): void
    {
        $preload = new Preload(
            '/media/hero.png',
            AsType::Image,
            MimeType::ImagePNG,
            FetchPriority::High,
            true,
            '(max-width: 1024px)'
        );

        $attrs = $preload->getAttrs();

        $this->assertSame('preload', $attrs['rel']);
        $this->assertSame('/media/hero.png', $attrs['href']);
        $this->assertSame('image', $attrs['as']);
        $this->assertSame('image/png', $attrs['type']);
        $this->assertSame('high', $attrs['fetchpriority']);
        $this->assertSame('', $attrs['crossorigin']);
        $this->assertSame('(max-width: 1024px)', $attrs['media']);
    }

    /**
     * @dataProvider asTypeProvider
     */
    public function testDifferentAsTypes(AsType $asType, string $expectedValue): void
    {
        $preload = new Preload(
            '/media/test',
            $asType,
            MimeType::ImageJPEG
        );

        $this->assertSame($expectedValue, $preload->getAttrs()['as']);
    }

    public static function asTypeProvider(): array
    {
        return [
            'image' => [AsType::Image, 'image'],
            'font' => [AsType::Font, 'font'],
            'script' => [AsType::Script, 'script'],
            'style' => [AsType::Style, 'style'],
            'video' => [AsType::Video, 'video'],
            'audio' => [AsType::Audio, 'audio'],
        ];
    }

    /**
     * @dataProvider fetchPriorityProvider
     */
    public function testDifferentFetchPriorities(FetchPriority $priority, string $expectedValue): void
    {
        $preload = new Preload(
            '/media/test.jpg',
            AsType::Image,
            MimeType::ImageJPEG,
            $priority
        );

        $this->assertSame($expectedValue, $preload->getAttrs()['fetchpriority']);
    }

    public static function fetchPriorityProvider(): array
    {
        return [
            'high' => [FetchPriority::High, 'high'],
            'medium' => [FetchPriority::Medium, 'medium'],
            'low' => [FetchPriority::Low, 'low'],
        ];
    }
}
