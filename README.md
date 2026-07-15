# SamJUK_FetchPriority

[![Supported Magento Versions](https://img.shields.io/badge/magento-2.4.6%E2%80%932.4.8-orange.svg?logo=magento)](https://github.com/SamJUK/m2-module-fetch-priority/actions/workflows/ci.yml)
[![CI Workflow Status](https://github.com/samjuk/m2-module-fetch-priority/actions/workflows/ci.yml/badge.svg)](https://github.com/SamJUK/m2-module-fetch-priority/actions/workflows/ci.yml)
[![GitHub Release](https://img.shields.io/github/v/release/SamJUK/m2-module-fetch-priority?label=Latest%20Release&logo=github)](https://github.com/SamJUK/m2-module-fetch-priority/releases)

This module adds options to set the fetch priority & lazy loading attribute on images added via the admin area.

It also provides a simple API to add preload & prefetch link tags to the header from other modules.

## Features

- Automatic `<link rel="preload">` for the product page main image, so the browser fetches it before it discovers the `<img>` tag.
- Automatic preload for the first 4 product images in a category grid (above-the-fold products).
- `fetch-priority` / `loading` / preload controls for PageBuilder image content type (desktop & mobile images).
- Public API (`LinkStore` + `Preload`/`Prefetch` factories) to add your own preload/prefetch links from any module.
- Every feature above can be toggled independently, or disabled globally, via admin config.

## Requirements

- Magento 2.4.6 - 2.4.8 (see CI matrix badge above)
- PHP 8.1+

## Installation
```sh
composer require samjuk/m2-module-fetch-priority
php bin/magento setup:upgrade && php bin/magento cache:flush
```

## Configuration

Settings are available at **Stores > Configuration > SamJUK > Fetch Priority**.

| Path | Field | Default | Description |
| --- | --- | --- | --- |
| `samjuk_fetch_priority/general/enabled` | Enable | Yes | Master switch, disables all preloads when off |
| `samjuk_fetch_priority/preloads/product_main` | Product Main Image | Yes | Preload the main image on product view pages |
| `samjuk_fetch_priority/preloads/category_grid` | First 4 Category Products | Yes | Preload the first 4 product images on category pages |
| `samjuk_fetch_priority/preloads/pagebuilder_content` | PageBuilder Content | Yes | Enable fetch-priority/loading/preload attributes on PageBuilder images |

Scope: default / website / store view.

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

## Contributing

Bug reports and PRs welcome — see [CONTRIBUTING.md](CONTRIBUTING.md).

## Security

See [SECURITY.md](SECURITY.md) for reporting vulnerabilities.

## License

[MIT](LICENSE)
