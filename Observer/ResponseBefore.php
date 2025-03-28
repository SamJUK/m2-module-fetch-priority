<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Observer;

use Magento\Framework\Event\ObserverInterface;

class ResponseBefore implements ObserverInterface
{
    public function __construct(
        private readonly \SamJUK\FetchPriority\Model\LinkStore $linkStore,
        private readonly \Magento\Framework\View\Helper\SecureHtmlRenderer $secureHtmlRenderer
    ) { }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $response = $observer->getEvent()->getData('response');
        $response->setBody(preg_replace(
            '/<head.*?>/',
            "<head>
            <!-- SamJUK_FetchPriority:preload -->
            {$this->getPreloadsHTML()}
            <!-- / SamJUK_FetchPriority::preload -->",
            $response->getBody()
        ));
    }

    private function getPreloadsHTML()
    {
        $preloads = array_map(
            fn($link) => $this->secureHtmlRenderer->renderTag('link', $link->getAttrs()),
            $this->linkStore->get()
        );
        return implode("\r\n", $preloads);
    }
}
