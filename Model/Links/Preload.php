<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Model\Links;

class Preload implements \SamJUK\FetchPriority\Api\LinkInterface
{
    public const REL = 'preload';

    public function __construct(
        private readonly string $href,
        private readonly \SamJUK\FetchPriority\Enum\Preload\AsType $asType,
        private readonly \SamJUK\FetchPriority\Enum\Preload\MimeType $mimeType,
        private readonly ?\SamJUK\FetchPriority\Enum\FetchPriority $fetchPriority = null,
        private readonly bool $crossOrigin = false,
        private readonly ?string $media = null,
    ) { }

    /**
     * @return array{as: string, href: string, rel: string, type: string|null[]}
     */
    public function getAttrs() : array
    {
        $attrs = [
            'rel' => static::REL,
            'href' => $this->href,
            'as' => $this->asType->value,
            'type' => $this->mimeType->value
        ];

        if ($this->fetchPriority) {
            $attrs['fetchpriority'] = $this->fetchPriority->value;
        }

        if ($this->crossOrigin) {
            $attrs['crossorigin'] = "";
        }

        if ($this->media) {
            $attrs['media'] = $this->media;
        }

        return $attrs;
    }
}
