<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\ViewModel;

class Links implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    public function __construct(
        private readonly \SamJUK\FetchPriority\Model\LinkStore $linkStore,
        private readonly \SamJUK\FetchPriority\Model\Links\PreloadFactory $preloadFactory
    ) {
        $preload = $this->preloadFactory->create([
            'href' => 'https://app.magento2.test/media/my_custom_entity/image1.jpg',
            'mimeType' => \SamJUK\FetchPriority\Enum\Preload\MimeType::ImageJPEG,
            'asType' => \SamJUK\FetchPriority\Enum\Preload\AsType::Image,
            'fetchPriority' => \SamJUK\FetchPriority\Enum\FetchPriority::High
        ]);
        $this->linkStore->add($preload);
    }

    /**
     * @return \SamJUK\FetchPriority\Api\LinkInterface[]
     */
    public function getLinks(): array
    {
        return $this->linkStore->get();
    }
}
