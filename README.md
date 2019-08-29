![imgix logo](https://assets.imgix.net/imgix-logo-web-2014.pdf?page=2&fm=png&w=200&h=200)

[![Build Status](https://travis-ci.org/imgix/imgix-php.png?branch=master)](https://travis-ci.org/imgix/imgix-php)

A PHP client library for generating URLs with imgix. imgix is a high-performance
distributed image processing service. More information can be found at
[http://www.imgix.com](http://www.imgix.com).


## Dependencies

The tests have a few external dependencies. To install those:

```bash
phpunit --bootstrap src/autoload.php tests/
```

## Installation

### Standalone

Just copy the files to your project, and include the `src/autoload.php` file. We recommend using Composer if at all possible.

### Using Composer

Define the following requirement in your `composer.json` file:

```json
{
  "require": {
    "imgix/imgix-php": "dev-master"
  }
}
```

And include the global `vendor/autoload.php` autoloader.

## Basic Usage

To begin creating imgix URLs programmatically, simply add the php files to your project (an example autoloader is also provided). The URL builder can be reused to create URLs for any
images on the domains it is provided.

```php
use Imgix\UrlBuilder;

$builder = new UrlBuilder("demos.imgix.net");
$params = array("w" => 100, "h" => 100);
echo $builder->createURL("bridge.png", $params);

// Prints out:
// http://demos.imgix.net/bridge.png?h=100&w=100
```

For HTTPS support, simply use the setter `setUseHttps` on the builder

```php
use Imgix\UrlBuilder;

$builder = new UrlBuilder("demos.imgix.net");
$builder->setUseHttps(true);
$params = array("w" => 100, "h" => 100);
echo $builder->createURL("bridge.png", $params);

// Prints out
// https://demos.imgix.net/bridge.png?h=100&w=100
```

## Signed URLs

To produce a signed URL, you must enable secure URLs on your source and then
provide your signature key to the URL builder.

```php
use Imgix\UrlBuilder;

$builder = new UrlBuilder("demos.imgix.net");
$builder->setSignKey("test1234");
$params = array("w" => 100, "h" => 100);
echo $builder->createURL("bridge.png", $params);

// Prints out:
// http://demos.imgix.net/bridge.png?h=100&w=100&s=bb8f3a2ab832e35997456823272103a4
```

## Srcset Generation

The imgix library allows for generation of custom `srcset` attributes, which can be invoked through `createSrcSet()`. By default, the `srcset` generated will allow for responsive size switching by building a list of image-width mappings.

```php
$builder = new UrlBuilder("demos.imgix.net", true, "my-key", false);
echo $builder->createSrcSet("bridge.png");
```

Will produce the following attribute value, which can then be served to the client:

```html
https://demos.imgix.net/bridge.png?w=100&s=ac331d314510e3039a33aa1a7ebc23ee 100w,
https://demos.imgix.net/bridge.png?w=116&s=aac0667a00791c8c8801a2fef134e78a 116w,
https://demos.imgix.net/bridge.png?w=134&s=2fcd42d984155efe26cc1e36e16b2897 134w,
                                    ...
https://demos.imgix.net/bridge.png?w=7400&s=6a6cbe01416dc4e0c65d1a2f87b868ac 7400w,
https://demos.imgix.net/bridge.png?w=8192&s=9e6b0a94e81e929ad71829fcccf4d2d8 8192w
```

In cases where enough information is provided about an image's dimensions, `createSrcSet()` will instead build a `srcset` that will allow for an image to be served at different resolutions. The parameters taken into consideration when determining if an image is fixed-width are `w`, `h`, and `ar`. By invoking `createSrcSet()` with either a width **or** the height and aspect ratio (along with `fit=crop`, typically) provided, a different `srcset` will be generated for a fixed-size image instead.

```php
$builder = new UrlBuilder("demos.imgix.net", true, "my-key", false);
echo $builder->createSrcSet("bridge.png", array("h"=>800, "ar"=>"3:2", "fit"=>"crop"));
```

Will produce the following attribute value:

```html
https://demos.imgix.net/bridge.png?ar=3%3A2&dpr=1&fit=crop&h=800&s=39eb37ad41acf7170343aa463424ae49 1x,
https://demos.imgix.net/bridge.png?ar=3%3A2&dpr=2&fit=crop&h=800&s=a8ab13a2c7a17b91db42cb86e45f7c9d 2x,
https://demos.imgix.net/bridge.png?ar=3%3A2&dpr=3&fit=crop&h=800&s=8fefe5daf312f04fb6912a101afbf704 3x,
https://demos.imgix.net/bridge.png?ar=3%3A2&dpr=4&fit=crop&h=800&s=74a6167d6ef8ba410109feda814b9ac0 4x,
https://demos.imgix.net/bridge.png?ar=3%3A2&dpr=5&fit=crop&h=800&s=4449b7f44ba7d6d0527a16d9a10b6e39 5x
```

For more information to better understand `srcset`, we highly recommend [Eric Portis' "Srcset and sizes" article](https://ericportis.com/posts/2014/srcset-sizes/) which goes into depth about the subject.
