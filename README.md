# SamJUK_FetchPriority

[![Supported Magento Versions](https://img.shields.io/badge/magento-2.4.4%E2%80%932.4.7-orange.svg?logo=magento)](https://github.com/SamJUK/m2-module-fetch-priority/actions/workflows/ci.yml)
[![CI Workflow Status](https://github.com/samjuk/m2-module-fetch-priority/actions/workflows/ci.yml/badge.svg)](https://github.com/SamJUK/m2-module-fetch-priority/actions/workflows/ci.yml)
[![GitHub Release](https://img.shields.io/github/v/release/SamJUK/m2-module-fetch-priority?label=Latest%20Release&logo=github)](https://github.com/SamJUK/m2-module-fetch-priority/releases)

This module adds options to set the fetch priority & lazy loading attribute on images added via the admin area.

It also provides a simple API to add preload & prefetch link tags to the header from other modules.

## Installation
```sh
composer require samjuk/m2-module-fetch-priority
php bin/magento setup:upgrade && php bin/magento cache:flush
```

## Link Tag Usage

To use this module to add preload/prefetch link tags

### Example Preload Usage
```php
class MyClassToAddPreloads
{
     public function __construct(
          private readonly \SamJUK\FetchPriority\Model\LinkStore $linkStore,
          private readonly \SamJUK\FetchPriority\Model\Links\PreloadFactory $preloadFactory
     ) { }

     public function execute()
     {
          // Do Stuff
          $preload = $this->preloadFactory->create([
               'href' => 'https://app.magento2.test/media/my_custom_entity/image1.jpg',
               'mimeType' => \SamJUK\FetchPriority\Enum\Preload\MimeType::ImageJPEG,
               'asType' => \SamJUK\FetchPriority\Enum\Preload\AsType::Image,
               'fetchPriority' => \SamJUK\FetchPriority\Enum\FetchPriority::High
          ]);
          $this->linkStore->add($preload);
     }
}
```


### Example Prefetch Usage
```php
class MyClassToAddPrefetches
{
     public function __construct(
          private readonly \SamJUK\FetchPriority\Model\LinkStore $linkStore,
          private readonly \SamJUK\FetchPriority\Model\Links\PrefetchFactory $preloadFactory
     ) { }

     public function execute()
     {
          // Do Stuff
          $prefetch = $this->preloadFactory->create([
               'href' => 'https://app.magento2.test/media/my_custom_entity/image1.jpg',
          ]);
          $this->linkStore->add($prefetch);
     }
}
```
