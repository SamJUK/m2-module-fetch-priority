<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Observer\Catalog\Controller\Product;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class View implements ObserverInterface
{
    public function __construct(
        private readonly \SamJUK\FetchPriority\Model\LinkStore $linkStore,
        private readonly \SamJUK\FetchPriority\Model\Links\PreloadFactory $preloadFactory,
        private readonly \Magento\Catalog\Block\Product\View\Gallery $galleryBlock
    ) { }

    public function execute(Observer $observer)
    {
        $preload = $this->preloadFactory->create([
            'href' => $this->getMainImage($observer->getData('product')),
            'mimeType' => \SamJUK\FetchPriority\Enum\Preload\MimeType::ImageJPEG,
            'asType' => \SamJUK\FetchPriority\Enum\Preload\AsType::Image,
            'fetchPriority' => \SamJUK\FetchPriority\Enum\FetchPriority::High
        ]);
        $this->linkStore->add($preload);
    }

    private function getMainImage($product)
    {
        $images = $this->galleryBlock->getGalleryImages()->getItems();
        $mainImage = current(array_filter(
            $images,
            static fn($img) => $product->getImage() == $img->getFile()
        ));

        if (!empty($images) && empty($mainImage)) {
            $mainImage = $this->galleryBlock->getGalleryImages()->getFirstItem();
        }

        return $mainImage
            ? $mainImage->getData('medium_image_url')
            : $this->galleryBlock->getData('imageHelper')->getDefaultPlaceholderUrl('image');
    }
}
