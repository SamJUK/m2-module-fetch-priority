<?php

namespace SamJUK\FetchPriority\Observer\View;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class BlockToHtmlAfter implements ObserverInterface
{
    public function __construct(
        private readonly \SamJUK\FetchPriority\Model\LinkStore $linkStore,
        private readonly \SamJUK\FetchPriority\Model\Links\PreloadFactory $preloadFactory,
        private readonly \SamJUK\FetchPriority\Model\Config $config
    ) { }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isEnabled() && !$this->config->isPageBuilderPreloadEnabled()) {
            return;
        }

        /** @var DataObject */
        $transport = $observer->getEvent()->getTransport();

        $matches = [];
        // @TODO: Only preload images with the `preload` attribute set
        preg_match_all(
            '/<img.*?src="(.*?)".*?preload="Yes".*?data-element="(.*?)".*?>/',
            $transport->getHtml(),
            $matches
        );

        $i = 0;
        foreach ($matches[1] as $url) {
            if ($url) {
                $preload = $this->preloadFactory->create([
                    'href' => $url,
                    'mimeType' => \SamJUK\FetchPriority\Enum\Preload\MimeType::ImageJPEG,
                    'asType' => \SamJUK\FetchPriority\Enum\Preload\AsType::Image,
                    'fetchPriority' => \SamJUK\FetchPriority\Enum\FetchPriority::High,
                    'media' => $this->getMediaQuery($matches[2][$i])
                ]);
                $this->linkStore->add($preload);
            }
            $i++;
        }
    }

    private function getMediaQuery($type)
    {
        if ($type === 'desktop_image') {
            return '(min-width: 769px)';
        }
        return '(max-width: 768px)';
    }
}
