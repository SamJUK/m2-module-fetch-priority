<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Observer;

use Magento\Framework\Event\ObserverInterface;

class ResponseBefore implements ObserverInterface
{
    public function __construct(
        private readonly \SamJUK\FetchPriority\Model\LinkStore $linkStore,
        private readonly \Magento\Framework\View\Helper\SecureHtmlRenderer $secureHtmlRenderer,
        private readonly \Magento\Framework\App\State $appState,
    ) { }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->isFrontendArea() || count($this->linkStore->get()) === 0) {
            return;
        }

        $response = $observer->getEvent()->getData('response');
        $response->setBody(preg_replace(
            '/<head.*?>/',
            "<head>
            <!-- SamJUK_FetchPriority:preload -->
            {$this->getPreloadsHTML()}
            <!-- / SamJUK_FetchPriority::preload -->",
            $response->getBody(),
            1
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

    private function isFrontendArea(): bool
    {
        try {
            return $this->appState->getAreaCode() === \Magento\Framework\App\Area::AREA_FRONTEND;
        } catch (\Exception $e) {
            return false;
        }
    }
}
