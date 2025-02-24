<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Model\Links;

class Prefetch implements \SamJUK\FetchPriority\Api\LinkInterface
{
    public const REL = 'prefetch';

    /**
     * @param string $href - Href of asset to prefetch
     */
    public function __construct(
        private readonly string $href
    ) { }

    /**
     * @return array{href: string, rel: string}
     */
    public function getAttrs() : array
    {
        return [
            'rel' => static::REL,
            'href' => $this->href
        ];
    }
}
