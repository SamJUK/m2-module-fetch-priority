<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Plugin\Catalog\Category;

use Magento\Catalog\Model\View\Asset\ImageFactory as AssetImageFactory;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\View\ConfigInterface;
use Magento\Catalog\Helper\Image as ImageHelper;

class View
{
    public function __construct(
        private readonly \SamJUK\FetchPriority\Model\LinkStore $linkStore,
        private readonly \SamJUK\FetchPriority\Model\Links\PreloadFactory $preloadFactory,
        private readonly \Magento\Catalog\Block\Product\ListProduct $listProductBlock,
        private readonly ConfigInterface $presentationConfig,
        private readonly ParamsBuilder $imageParamsBuilder,
        private readonly PlaceholderFactory $viewAssetPlaceholderFactory,
        private readonly AssetImageFactory $viewAssetImageFactory
    ) { }

    public function afterExecute($subject, $result)
    {
        $this->preloadInitialProductImages();
        return $result;
    }

    public function preloadInitialProductImages()
    {
        $i = 0;
        $imageType = $this->getImageType();
        $collection = $this->listProductBlock->getLoadedProductCollection();
        foreach ($collection as $product) {
            if (++$i > 4) {
                return;
            }
            $image = $this->getProductImage($product, $imageType);
            $this->preload($image);
        }
    }

    private function getProductImage($product, $imageType)
    {
        $viewImageConfig = $this->presentationConfig->getViewConfig()->getMediaAttributes(
            'Magento_Catalog',
            ImageHelper::MEDIA_TYPE_CONFIG_NODE,
            $imageType
        );

        $imageMiscParams = $this->imageParamsBuilder->build($viewImageConfig);
        $originalFilePath = $product->getData($imageMiscParams['image_type']);

        if ($originalFilePath === null || $originalFilePath === 'no_selection') {
            $imageAsset = $this->viewAssetPlaceholderFactory->create(
                [
                    'type' => $imageMiscParams['image_type']
                ]
            );
        } else {
            $imageAsset = $this->viewAssetImageFactory->create(
                [
                    'miscParams' => $imageMiscParams,
                    'filePath' => $originalFilePath,
                ]
            );
        }

        return $imageAsset->getUrl();
    }

    private function getImageType()
    {
        return $this->listProductBlock->getMode() === 'grid'
            ? 'category_page_grid'
            : 'category_page_list';
    }

    private function preload(string $imageUrl)
    {
        $preload = $this->preloadFactory->create([
            'href' => $imageUrl,
            'mimeType' => \SamJUK\FetchPriority\Enum\Preload\MimeType::ImageJPEG,
            'asType' => \SamJUK\FetchPriority\Enum\Preload\AsType::Image,
            'fetchPriority' => \SamJUK\FetchPriority\Enum\FetchPriority::High
        ]);
        $this->linkStore->add($preload);
    }
}
