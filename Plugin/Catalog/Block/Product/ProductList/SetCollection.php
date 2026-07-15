<?php

declare(strict_types=1);

namespace SamJUK\FetchPriority\Plugin\Catalog\Block\Product\ProductList;

use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\View\Asset\ImageFactory as AssetImageFactory;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\View\ConfigInterface;
use Magento\Catalog\Helper\Image as ImageHelper;

class SetCollection
{
    /** @var object[] Keyed by spl_object_id() of already-processed collections */
    private array $processedCollections = [];

    public function __construct(
        private readonly \SamJUK\FetchPriority\Model\LinkStore $linkStore,
        private readonly \SamJUK\FetchPriority\Model\Links\PreloadFactory $preloadFactory,
        private readonly ConfigInterface $presentationConfig,
        private readonly ParamsBuilder $imageParamsBuilder,
        private readonly PlaceholderFactory $viewAssetPlaceholderFactory,
        private readonly AssetImageFactory $viewAssetImageFactory,
        private readonly \SamJUK\FetchPriority\Model\Config $config
    ) { }

    public function afterSetCollection(Toolbar $subject, $result, $collection)
    {
        if (!$this->config->isEnabled() || !$this->config->isCategoryProductPreloadEnabled()) {
            return $result;
        }

        if (is_object($collection)) {
            $collectionId = spl_object_id($collection);
            if (isset($this->processedCollections[$collectionId])) {
                return $result;
            }
            // Keep a reference to the collection itself (not just its id) so spl_object_id()
            // can't be recycled by an unrelated object for the rest of this request.
            $this->processedCollections[$collectionId] = $collection;
        }

        $this->preloadInitialProductImages($collection, $subject->getCurrentMode());
        return $result;
    }

    private function preloadInitialProductImages($collection, string $mode): void
    {
        $i = 0;
        $imageType = $this->getImageType($mode);
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

    private function getImageType(string $mode)
    {
        return $mode === 'grid' ? 'category_page_grid' : 'category_page_list';
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
