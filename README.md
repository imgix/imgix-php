<!-- ix-docs-ignore -->
![imgix logo](https://assets.imgix.net/sdk-imgix-logo.svg)

`imgix-php` is a client library for generating image URLs with [imgix](https://www.imgix.com/). It is tested under PHP versions `5.6`, `7.0`, `7.1`, and `7.2`

[![Version](https://img.shields.io/packagist/v/imgix/imgix-php.svg)](https://packagist.org/packages/imgix/imgix-php)
[![Build Status](https://travis-ci.com/imgix/imgix-php.svg?branch=main)](https://travis-ci.com/imgix/imgix-php)
[![Downloads](https://img.shields.io/packagist/dt/imgix/imgix-php)](https://packagist.org/packages/imgix/imgix-php)
[![License](https://img.shields.io/github/license/imgix/imgix-php)](https://github.com/imgix/imgix-php/blob/main/LICENSE)
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Fimgix%2Fimgix-php.svg?type=shield)](https://app.fossa.com/projects/git%2Bgithub.com%2Fimgix%2Fimgix-php?ref=badge_shield)

---
<!-- /ix-docs-ignore -->

- [Installation](#installation)
    * [Using Composer](#using-composer)
    * [Standalone](#standalone)
- [Usage](#usage)
- [Signed URLs](#signed-urls)
- [Srcset Generation](#srcset-generation)
    * [Fixed-Width Images](#fixed-width-images)
        + [Variable Quality](#variable-quality)
    * [Fluid-Width Images](#fluid-width-images)
        + [Custom Widths](#custom-widths)
        + [Width Ranges](#width-ranges)
        + [Width Tolerance](#width-tolerance)
- [The `ixlib` Parameter](#the-ixlib-parameter)
- [License](#license)

## Installation

### Using Composer

Define the following requirement in your `composer.json` file:

```json
{
  "require": {
    "imgix/imgix-php": "dev-main"
  }
}
```

And include the global `vendor/autoload.php` autoloader.

### Standalone

Just copy the files to your project, and include the `src/autoload.php` file. We recommend using Composer if at all possible.

## Usage

To begin creating imgix URLs programmatically, add the php files to your project (an example autoloader is also provided). The URL builder can be reused to create URLs for any
images on the domains it is provided.

```php
use Imgix\UrlBuilder;

$builder = new UrlBuilder("demos.imgix.net");
$params = array("w" => 100, "h" => 100);
echo $builder->createURL("bridge.png", $params);
// 'https://demos.imgix.net/bridge.png?h=100&w=100'
```

HTTPS support is available _by default_. However, if you need HTTP support, call `setUseHttps` on the builder:

```php
use Imgix\UrlBuilder;

$builder = new UrlBuilder("demos.imgix.net");
$builder->setUseHttps(false);
$params = array("w" => 100, "h" => 100);
echo $builder->createURL("bridge.png", $params);
// 'http://demos.imgix.net/bridge.png?h=100&w=100'
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
// 'http://demos.imgix.net/bridge.png?h=100&w=100&s=bb8f3a2ab832e35997456823272103a4'
```

## Srcset Generation

The imgix-php package allows for generation of custom srcset attributes, which can be invoked through the `createSrcSet` method. By default, the generated srcset will allow for responsive size switching by building a list of image-width mappings.

```php
$builder = new UrlBuilder("demos.imgix.net", true, "my-key", false);
echo $builder->createSrcSet("image.png");
```

The above will produce the following srcset attribute value which can then be served to the client: 

``` html
https://demos.imgix.net/image.png?w=100&s=e415797545a77a9d2842dedcfe539c9a 100w,
https://demos.imgix.net/image.png?w=116&s=b2da46f5c23ef13d5da30f0a4545f33f 116w,
https://demos.imgix.net/image.png?w=135&s=b61422dead929f893c04b8ff839bb088 135w,
                                        ...
https://demos.imgix.net/image.png?w=7401&s=ad671301ed4663c3ce6e84cb646acb96 7401w,
https://demos.imgix.net/image.png?w=8192&s=a0fed46e2bbcc70ded13dc629aee5398 8192w
```

### Fixed-Width Images

In cases where enough information is provided about an image's dimensions, `createSrcSet` will instead build a srcset that will allow for an image to be served at different resolutions. The parameters taken into consideration when determining if an image is fixed-width are `w` and `h`.

By invoking `createSrcSet` with either a width **or** height provided, a different srcset will be generated for a fixed-width image instead.

```php
$builder = new UrlBuilder("demos.imgix.net", true, "my-key", false);
echo $builder->createSrcSet("image.png", array("h"=>800, "ar"=>"3:2", "fit"=>"crop"));
```

Will produce the following attribute value:

``` html
https://demos.imgix.net/image.png?ar=3%3A2&dpr=1&fit=crop&h=800&s=6cf5c443d1eb98bc3d96ea569fcef088 1x,
https://demos.imgix.net/image.png?ar=3%3A2&dpr=2&fit=crop&h=800&s=d60a61a5f34545922bd8dff4e53a0555 2x,
https://demos.imgix.net/image.png?ar=3%3A2&dpr=3&fit=crop&h=800&s=590f96aa426f8589eb7e449ebbeb66e7 3x,
https://demos.imgix.net/image.png?ar=3%3A2&dpr=4&fit=crop&h=800&s=c89c2fd3148957647e86cfc32ba20517 4x,
https://demos.imgix.net/image.png?ar=3%3A2&dpr=5&fit=crop&h=800&s=3d73af69d78d49eef0f81b4b5d718a2c 5x
```

For more information to better understand srcset, we highly recommend
[Eric Portis' "Srcset and sizes" article](https://ericportis.com/posts/2014/srcset-sizes/) which goes into depth about the subject.

#### Variable Quality

This library will automatically append a variable `q` parameter mapped to each `dpr` parameter when generating a [fixed-width image](#fixed-width-images) srcset. This technique is commonly used to compensate for the increased file size of high-DPR images.

Since high-DPR images are displayed at a higher pixel density on devices, image quality can be lowered to reduce overall file size––without sacrificing perceived visual quality. For more information and examples of this technique in action, see [this blog post](https://blog.imgix.com/2016/03/30/dpr-quality).

This behavior will respect any overriding `q` value passed in as a parameter. Additionally, it can be disabled altogether by passing `$disableVariableQuality = true` to `createSrcSet()`'s `$options`.

This behavior specifically occurs when a [fixed-width image](#fixed-width-images) is rendered, for example:

```php
// Note that `params=array("w" => 100)` allows `createSrcSet` to _infer_ the creation
// of a DPR based srcset attribute for fixed-width images.
$builder = new UrlBuilder("demos.imgix.net", true, "", false);
$params = array("w" => 100);
$srcset = $builder->createSrcSet($path="image.jpg", $params=$params);
```

The above will generate a srcset with the following `q` to `dpr` query `params`:

```html
https://demos.imgix.net/image.jpg?dpr=1&q=75&w=100 1x,
https://demos.imgix.net/image.jpg?dpr=2&q=50&w=100 2x,
https://demos.imgix.net/image.jpg?dpr=3&q=35&w=100 3x,
https://demos.imgix.net/image.jpg?dpr=4&q=23&w=100 4x,
https://demos.imgix.net/image.jpg?dpr=5&q=20&w=100 5x'
```

### Fluid-Width Images

#### Custom Widths

In situations where specific widths are desired when generating `srcset` pairs, a user can specify them by passing an array of positive integers as `'widths'` within the `$options` array:

``` php
$builder = new UrlBuilder("demos.imgix.net", true, "", false);
$opts = array('widths' => array(144, 240, 320, 446, 640));
$srcset = $builder->createSrcSet($path="image.jpg", $params=array(), $options=$opts);
```

```html
https://demos.imgix.net/image.jpg?w=144 144w,
https://demos.imgix.net/image.jpg?w=240 240w,
https://demos.imgix.net/image.jpg?w=320 320w,
https://demos.imgix.net/image.jpg?w=446 446w,
https://demos.imgix.net/image.jpg?w=640 640w
```

**Note**: in situations where a `srcset` is being rendered as a [fixed-width](#fixed-width-images) srcset, any custom `widths` passed in will be ignored.

Additionally, if both `widths` and a width `tol`erance are passed to the `createSrcSet` method, the custom widths list will take precedence.

#### Width Ranges

In certain circumstances, you may want to limit the minimum or maximum value of the non-fixed `srcset` generated by the `createSrcSet` method. To do this, you can specify the widths at which a srcset should `start` and `stop`:

```php
$builder = new UrlBuilder("demo.imgix.net", true, "", false);
$opts = array('start' => 500, 'stop' => 2000);
$srcset = $builder->createSrcSet($path="image.jpg", $params=array(), $options=$opts);
```

Formatted version of the above srcset attribute:

``` html
https://demo.imgix.net/image.jpg?w=500 500w,
https://demo.imgix.net/image.jpg?w=580 580w,
https://demo.imgix.net/image.jpg?w=673 673w,
https://demo.imgix.net/image.jpg?w=780 780w,
https://demo.imgix.net/image.jpg?w=905 905w,
https://demo.imgix.net/image.jpg?w=1050 1050w,
https://demo.imgix.net/image.jpg?w=1218 1218w,
https://demo.imgix.net/image.jpg?w=1413 1413w,
https://demo.imgix.net/image.jpg?w=1639 1639w,
https://demo.imgix.net/image.jpg?w=1901 1901w,
https://demo.imgix.net/image.jpg?w=2000 2000w
```

#### Width Tolerance

The `srcset` width `tol`erance dictates the maximum `tol`erated difference between an image's downloaded size and its rendered size.

For example, setting this value to `10` means that an image will not render more than 10% larger or smaller than its native size. In practice, the image URLs generated for a width-based srcset attribute will grow by twice this rate.

A lower tolerance means images will render closer to their native size (thereby increasing perceived image quality), but a large srcset list will be generated and consequently users may experience lower rates of cache-hit for pre-rendered images on your site.

By default, srcset width `tol`erance is set to 8 percent, which we consider to be the ideal rate for maximizing cache hits without sacrificing visual quality. Users can specify their own width tolerance by providing a positive scalar value as width `tol`erance:

```php
$builder = new UrlBuilder("demo.imgix.net", true, "", false);
$opts = array('start' => 100, 'stop' => 384, 'tol' => 0.20);
$srcset = $builder->createSrcSet($path="image.jpg", $params=array(), $options=$opts);
```

In this case, the width `tol`erance is set to 20 percent, which will be reflected in the difference between subsequent widths in a srcset pair:

```html
https://demo.imgix.net/image.jpg?w=100 100w,
https://demo.imgix.net/image.jpg?w=140 140w,
https://demo.imgix.net/image.jpg?w=196 196w,
https://demo.imgix.net/image.jpg?w=274 274w,
https://demo.imgix.net/image.jpg?w=384 384w
```

## The `ixlib` Parameter

For security and diagnostic purposes, we sign all requests with the language and version of library used to generate the URL.

This can be disabled by setting `setIncludeLibraryParam` to `False` like so:

``` php
$builder = new UrlBuilder("demo.imgix.net", true, "", false);
// Or by calling `setIncludeLibraryParam`
$builder->setIncludeLibraryParam(false);
```

## License
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Fimgix%2Fimgix-php.svg?type=large)](https://app.fossa.com/projects/git%2Bgithub.com%2Fimgix%2Fimgix-php?ref=badge_large)
