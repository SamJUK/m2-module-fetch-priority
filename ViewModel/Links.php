<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\ViewModel;

class Links implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    public function __construct(
        private readonly \SamJUK\FetchPriority\Model\LinkStore $linkStore
    ) { }

    /**
     * @return \SamJUK\FetchPriority\Api\LinkInterface[]
     */
    public function getLinks(): array
    {
        return $this->linkStore->get();
    }
}
