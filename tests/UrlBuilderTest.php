<?php

namespace Imgix\Tests;

use Imgix\UrlBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UrlBuilderTest extends TestCase
{
    public const HOST = 'demos.imgix.net';

    public const TARGET_WIDTHS = [
        100, 116, 135, 156, 181, 210, 244, 283,
        328, 380, 441, 512, 594, 689, 799, 927,
        1075, 1247, 1446, 1678, 1946, 2257, 2619,
        3038, 3524, 4087, 4741, 5500, 6380, 7401, 8192,
    ];

    public function testURLBuilderRaisesExceptionOnNoDomain()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('UrlBuilder must be passed a string domain');

        new URLBuilder(null);
    }

    public function test_invalid_domain_append_slash()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Domain must be passed in as fully-qualified domain name and should not include a protocol or any path element, i.e. "example.imgix.net".');

        new UrlBuilder(self::HOST.'/');
    }

    public function test_invalid_domain_prepend_scheme()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Domain must be passed in as fully-qualified domain name and should not include a protocol or any path element, i.e. "example.imgix.net".');

        new UrlBuilder('https://'.self::HOST);
    }

    public function test_invalid_domain_append_dash()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Domain must be passed in as fully-qualified domain name and should not include a protocol or any path element, i.e. "example.imgix.net".');

        new UrlBuilder(self::HOST.'-');
    }

    public function testExamplePlain()
    {
        $builder = new UrlBuilder(self::HOST, false, '', false);

        $url = $builder->createURL('bridge.png', ['w' => 100, 'h' => 100]);

        $this->assertEquals('http://demos.imgix.net/bridge.png?h=100&w=100', $url);
    }

    public function testExamplePlainUsesHttpsByDefault()
    {
        // Construct the builder with a `$domain` __only__.
        $builder = new UrlBuilder(self::HOST, includeLibraryParam: false);

        $url = $builder->createURL('bridge.png', ['w' => 100, 'h' => 100]);

        $this->assertEquals('https://demos.imgix.net/bridge.png?h=100&w=100', $url);
    }

    public function testExamplePlainHttps()
    {
        $builder = new UrlBuilder(self::HOST, false, '', false);
        $builder->setUseHttps(true);

        $url = $builder->createURL('bridge.png', ['w' => 100, 'h' => 100]);

        $this->assertEquals('https://demos.imgix.net/bridge.png?h=100&w=100', $url);
    }

    public function testExamplePlainSecure()
    {
        $builder = new UrlBuilder(self::HOST, false, '', false);
        $builder->setSignKey('test1234');

        $url = $builder->createURL('bridge.png', ['w' => 100, 'h' => 100]);

        $this->assertEquals('http://demos.imgix.net/bridge.png?h=100&w=100&s=bb8f3a2ab832e35997456823272103a4', $url);
    }

    public function testParamKeysAreEscaped()
    {
        $builder = new UrlBuilder(self::HOST, includeLibraryParam: false);

        $url = $builder->createURL('demo.png', ['hello world' => 'interesting']);

        $this->assertEquals('https://demos.imgix.net/demo.png?hello%20world=interesting', $url);
    }

    public function testParamValuesAreEscaped()
    {
        $builder = new UrlBuilder(self::HOST, includeLibraryParam: false);

        $url = $builder->createURL('demo.png', ['hello_world' => '/foo"><script>alert("hacked")</script><']);

        $this->assertEquals('https://demos.imgix.net/demo.png?hello_world=%2Ffoo%22%3E%3Cscript%3Ealert%28%22hacked%22%29%3C%2Fscript%3E%3C', $url);
    }

    public function testZeroValue()
    {
        $builder = new UrlBuilder(self::HOST, includeLibraryParam: false);

        $url = $builder->createURL('bridge.png', ['foo' => 0]);

        $this->assertEquals('https://demos.imgix.net/bridge.png?foo=0', $url);
    }

    public function testBase64ParamVariantsAreBase64Encoded()
    {
        $builder = new UrlBuilder(self::HOST, includeLibraryParam: false);

        $url = $builder->createURL('~text', ['txt64' => 'I cannøt belîév∑ it wors! 😱']);

        $this->assertEquals('https://demos.imgix.net/~text?txt64=SSBjYW5uw7h0IGJlbMOuw6l24oiRIGl0IHdvcu-jv3MhIPCfmLE', $url);
    }

    public function testWithFullyQualifiedUrl()
    {
        $builder = new UrlBuilder(self::HOST, includeLibraryParam: false);
        $builder->setSignKey('test1234');

        $url = $builder->createUrl('http://media.giphy.com/media/jCMq0p94fgBIk/giphy.gif');

        $this->assertEquals('https://demos.imgix.net/http%3A%2F%2Fmedia.giphy.com%2Fmedia%2FjCMq0p94fgBIk%2Fgiphy.gif?s=54c35ea3a066357b06bc553ee9975ec9', $url);
    }

    public function testWithFullyQualifiedUrlWithSpaces()
    {
        $builder = new UrlBuilder(self::HOST, includeLibraryParam: false);
        $builder->setSignKey('test1234');

        $url = $builder->createUrl('https://my-demo-site.com/files/133467012/avatar icon.png');

        $this->assertEquals('https://demos.imgix.net/https%3A%2F%2Fmy-demo-site.com%2Ffiles%2F133467012%2Favatar%20icon.png?s=6a1d47f292194cfa7573da0e2bb6b0f4', $url);
    }

    public function testWithFullyQualifiedUrlWithParams()
    {
        $builder = new UrlBuilder(self::HOST, includeLibraryParam: false);
        $builder->setSignKey('test1234');

        $url = $builder->createUrl('https://my-demo-site.com/files/133467012/avatar icon.png?some=chill&params=1');

        $this->assertEquals('https://demos.imgix.net/https%3A%2F%2Fmy-demo-site.com%2Ffiles%2F133467012%2Favatar%20icon.png%3Fsome%3Dchill%26params%3D1?s=bbc73c61ebc739337b852ff8423a1da9', $url);
    }

    public function testInclusionOfLibraryVersionParam()
    {
        $builder = new UrlBuilder(self::HOST);

        $url = $builder->createUrl('https://my-demo-site.com/files/133467012/avatar icon.png?some=chill&params=1');

        $this->assertEquals('https://demos.imgix.net/https%3A%2F%2Fmy-demo-site.com%2Ffiles%2F133467012%2Favatar%20icon.png%3Fsome%3Dchill%26params%3D1?ixlib=php-'.UrlBuilder::VERSION, $url);

        $builder = new UrlBuilder(self::HOST);
        $builder->setIncludeLibraryParam(false);

        $url = $builder->createUrl('https://my-demo-site.com/files/133467012/avatar icon.png?some=chill&params=1');

        $this->assertEquals('https://demos.imgix.net/https%3A%2F%2Fmy-demo-site.com%2Ffiles%2F133467012%2Favatar%20icon.png%3Fsome%3Dchill%26params%3D1', $url);
    }

    public function testNestedParameters()
    {
        $builder = new UrlBuilder(self::HOST, includeLibraryParam: false);

        $url = $builder->createURL('bridge.png', ['auto' => ['compress', 'format']]);

        $this->assertEquals('https://demos.imgix.net/bridge.png?auto=compress%2Cformat', $url);
    }

    public function testSrcsetCustomTargetWidths100to7401()
    {
        $builder = new UrlBuilder(self::HOST, true, 'my-key', false);

        $expected = $builder->targetWidths(100, 7401);
        $actual = array_slice(self::TARGET_WIDTHS, 0, -1);

        $this->assertCount(count($expected), $actual);
        $this->assertEquals($expected, $actual);
    }

    public function testSrcsetCustomTargetWidths328to4088()
    {
        // Test that the correct number of target widths have been
        // generated and that they start and stop within the expected
        // range, inclusively.
        $builder = new UrlBuilder(self::HOST, true, 'my-key', false);

        $start = 328;
        $stop = 4087;
        $idx_328 = 8;
        $idx_4087 = 18;

        $expected = $builder->targetWidths($start, $stop);
        $actual = array_slice(self::TARGET_WIDTHS, $idx_328, $idx_4087);

        $this->assertCount(count($expected), $actual);
        $this->assertEquals($start, $actual[0]);
        $this->assertEquals($stop, end($actual));
    }

    public function testTargetWidthsMaxTolerance()
    {
        $builder = new UrlBuilder(self::HOST, true, 'my-key', false);

        $widths = $builder->targetWidths(100, 8192, 10_000_000);

        $this->assertEquals([0 => 100, 1 => 8192], $widths);
    }

    public function testNoParametersGeneratesSrcsetPairs()
    {
        $srcset = $this->srcsetBuilder();

        $this->assertCount(31, explode(',', $srcset));
    }

    public function testCustomSrcsetPairs()
    {
        // Test custom srcset pairs within ranges.
        $builder = new UrlBuilder(self::HOST);

        $actual = $builder->createSrcSet('image.jpg', [], ['start' => 328, 'stop' => 328]);
        $expected = 'https://demos.imgix.net/image.jpg?ixlib=php-'.UrlBuilder::VERSION.'&w=328 328w';

        $this->assertEquals($expected, $actual);

        $builder = new UrlBuilder(self::HOST);

        $actual = $builder->createSrcSet('image.jpg', [], ['start' => 720, 'stop' => 720]);
        $expected = 'https://demos.imgix.net/image.jpg?ixlib=php-'.UrlBuilder::VERSION.'&w=720 720w';

        $this->assertEquals($expected, $actual);

        $builder = new UrlBuilder(self::HOST);

        $actual = $builder->createSrcSet('image.jpg', [], ['start' => 640, 'stop' => 720]);
        $expected =
'https://demos.imgix.net/image.jpg?ixlib=php-'.UrlBuilder::VERSION.'&w=640 640w,
https://demos.imgix.net/image.jpg?ixlib=php-'.UrlBuilder::VERSION.'&w=720 720w';

        $this->assertEquals($expected, $actual);

        // Now test custom tolerances (also within a range).
        $builder = new UrlBuilder(self::HOST);

        $actual = $builder->createSrcSet('image.jpg', [], ['start' => 100, 'stop' => 108, 'tol' => 0.01]);
        $expected =
'https://demos.imgix.net/image.jpg?ixlib=php-'.UrlBuilder::VERSION.'&w=100 100w,
https://demos.imgix.net/image.jpg?ixlib=php-'.UrlBuilder::VERSION.'&w=102 102w,
https://demos.imgix.net/image.jpg?ixlib=php-'.UrlBuilder::VERSION.'&w=104 104w,
https://demos.imgix.net/image.jpg?ixlib=php-'.UrlBuilder::VERSION.'&w=106 106w,
https://demos.imgix.net/image.jpg?ixlib=php-'.UrlBuilder::VERSION.'&w=108 108w';

        $this->assertEquals($expected, $actual);
    }

    public function testSrcsetPairValues()
    {
        $srcset = $this->srcsetBuilder();
        $index = 0;
        // array of expected resolutions generated by srcset
        $resolutions = self::TARGET_WIDTHS;
        $srclist = explode(',', $srcset);
        $matches = [];

        foreach ($srclist as $src) {
            $width = explode(' ', $src)[1];

            // extract width int values
            preg_match("/\d+/", $width, $matches);
            $this->assertEquals($resolutions[$index], $matches[0]);
            $index++;
        }
    }

    public function testGivenWidthSrcsetIsDPR()
    {
        $srcset = $this->srcsetBuilder(['w' => 300]);
        $devicePixelRatio = 1;
        $srclist = explode(',', $srcset);

        foreach ($srclist as $src) {
            [$generatedURL, $generatedRatio] = explode(' ', $src);

            $dprStr = $devicePixelRatio.'x';
            $this->assertEquals($dprStr, $generatedRatio);

            $this->assertMatchesRegularExpression('/dpr='.$devicePixelRatio.'/', $generatedURL);

            $devicePixelRatio += 1;
        }
    }

    public function testDprSrcsetWithQ100()
    {
        $builder = new UrlBuilder(self::HOST, includeLibraryParam: false);

        $actual = $builder->createSrcSet('image.jpg', ['w' => 640, 'q' => 100]);
        $expected =
'https://demos.imgix.net/image.jpg?dpr=1&q=100&w=640 1x,
https://demos.imgix.net/image.jpg?dpr=2&q=100&w=640 2x,
https://demos.imgix.net/image.jpg?dpr=3&q=100&w=640 3x,
https://demos.imgix.net/image.jpg?dpr=4&q=100&w=640 4x,
https://demos.imgix.net/image.jpg?dpr=5&q=100&w=640 5x';

        $this->assertEquals($expected, $actual);
    }

    // Test that variable quality is enabled by default.
    public function testDprSrcsetWithDefaultQuality()
    {
        $builder = new UrlBuilder(self::HOST, includeLibraryParam: false);

        $actual = $builder->createSrcSet('image.jpg', ['w' => 740]);
        $expected =
'https://demos.imgix.net/image.jpg?dpr=1&q=75&w=740 1x,
https://demos.imgix.net/image.jpg?dpr=2&q=50&w=740 2x,
https://demos.imgix.net/image.jpg?dpr=3&q=35&w=740 3x,
https://demos.imgix.net/image.jpg?dpr=4&q=23&w=740 4x,
https://demos.imgix.net/image.jpg?dpr=5&q=20&w=740 5x';

        $this->assertEquals($expected, $actual);
    }

    // Test that `disableVariableQuality = true` disables `q` params.
    public function testDprVariableQualityDisabled()
    {
        $builder = new UrlBuilder(self::HOST, includeLibraryParam: false);

        $actual = $builder->createSrcSet('image.jpg', ['w' => 640], ['disableVariableQuality' => true]);
        $expected =
'https://demos.imgix.net/image.jpg?dpr=1&w=640 1x,
https://demos.imgix.net/image.jpg?dpr=2&w=640 2x,
https://demos.imgix.net/image.jpg?dpr=3&w=640 3x,
https://demos.imgix.net/image.jpg?dpr=4&w=640 4x,
https://demos.imgix.net/image.jpg?dpr=5&w=640 5x';

        $this->assertEquals($expected, $actual);
    }

    // Test that `q` overrides the default qualities when
    // `disableVariableQuality = false`.
    public function testDprSrcsetQOverridesEnabledVariableQuality()
    {
        $builder = new UrlBuilder(self::HOST, includeLibraryParam: false);

        $actual = $builder->createSrcSet('image.jpg', ['w' => 540, 'q' => 75], ['disableVariableQuality' => false]);
        $expected =
'https://demos.imgix.net/image.jpg?dpr=1&q=75&w=540 1x,
https://demos.imgix.net/image.jpg?dpr=2&q=75&w=540 2x,
https://demos.imgix.net/image.jpg?dpr=3&q=75&w=540 3x,
https://demos.imgix.net/image.jpg?dpr=4&q=75&w=540 4x,
https://demos.imgix.net/image.jpg?dpr=5&q=75&w=540 5x';

        $this->assertEquals($expected, $actual);
    }

    // Test that `q` overrides `disableVariableQuality = true`.
    public function testDprSrcsetQOverridesDisabledVariableQuality()
    {
        $builder = new UrlBuilder(self::HOST, includeLibraryParam: false);

        $actual = $builder->createSrcSet('image.jpg', ['w' => 440, 'q' => 99], ['disableVariableQuality' => true]);
        $expected =
'https://demos.imgix.net/image.jpg?dpr=1&q=99&w=440 1x,
https://demos.imgix.net/image.jpg?dpr=2&q=99&w=440 2x,
https://demos.imgix.net/image.jpg?dpr=3&q=99&w=440 3x,
https://demos.imgix.net/image.jpg?dpr=4&q=99&w=440 4x,
https://demos.imgix.net/image.jpg?dpr=5&q=99&w=440 5x';

        $this->assertEquals($expected, $actual);
    }

    public function testCreateSrcSetFromWidthsArray()
    {
        $builder = new UrlBuilder(self::HOST, includeLibraryParam: false);

        $actual = $builder->createSrcSet('image.jpg', [], ['widths' => [100, 200, 303, 404, 535]]);
        $expected =
'https://demos.imgix.net/image.jpg?w=100 100w,
https://demos.imgix.net/image.jpg?w=200 200w,
https://demos.imgix.net/image.jpg?w=303 303w,
https://demos.imgix.net/image.jpg?w=404 404w,
https://demos.imgix.net/image.jpg?w=535 535w';

        $this->assertEquals($expected, $actual);
    }

    public function testGivenWidthSignsURLs()
    {
        $srcset = $this->srcsetBuilder(['w' => 300]);
        $srclist = explode(',', $srcset);

        foreach ($srclist as $src) {
            $url = explode(' ', $src)[0];
            $this->assertMatchesRegularExpression('/s=/', $url);

            // parse out query params
            $params = substr($url, strrpos($url, '?'));
            $params = substr($params, 0, strrpos($params, 's=') - 1);

            // parse out sign parameter
            $generatedSignature = substr($url, strrpos($url, 's=') + 2);

            $signatureBase = 'my-key'.'/bridge.png'.$params;
            $expectSignature = md5($signatureBase);

            $this->assertEquals($expectSignature, $generatedSignature);
        }
    }

    public function testGivenHeightSrcsetGeneratesPairs()
    {
        $srcset = $this->srcsetBuilder(['h' => 300]);

        $this->assertCount(5, explode(',', $srcset));
    }

    public function testGivenHeightRespectsParameter()
    {
        $srcset = $this->srcsetBuilder(['h' => 300]);
        $srclist = explode(',', $srcset);

        foreach ($srclist as $src) {
            $this->assertMatchesRegularExpression('/h=300/', $src);
        }
    }

    public function testHeightBasedSrcsetHasDprValues()
    {
        $srcset = $this->srcsetBuilder(['h' => 300]);
        $srclist = explode(',', $srcset);

        foreach ($srclist as $src) {
            $dpr = explode(' ', $src)[1];
            $this->assertMatchesRegularExpression('/x/', $dpr);
        }
    }

    public function testHeightIncludesDPRParam()
    {
        $srcset = $this->srcsetBuilder(['h' => 300]);
        $srclist = explode(',', $srcset);

        foreach ($srclist as $src) {
            $dpr = explode(' ', $src)[0];
            $this->assertMatchesRegularExpression('/dpr=/', $dpr);
        }
    }

    public function testGivenHeightSrcsetSignsUrls()
    {
        $srcset = $this->srcsetBuilder(['h' => 300]);
        $srclist = explode(',', $srcset);

        $srcs = array_map(
            fn ($src) => explode(' ', $src)[0],
            $srclist,
        );

        foreach ($srcs as $src) {
            $this->assertMatchesRegularExpression('/s=/', $src);

            // parse out query params
            $params = substr($src, strrpos($src, '?'));
            $params = substr($params, 0, strrpos($params, 's=') - 1);

            // parse out sign parameter
            $generatedSignature = substr($src, strrpos($src, 's=') + 2);

            $signatureBase = 'my-key'.'/bridge.png'.$params;
            $expectSignature = md5($signatureBase);

            $this->assertEquals($expectSignature, $generatedSignature);
        }
    }

    public function testGivenWidthAndHeightSrcsetIsDPR()
    {
        $srcset = $this->srcsetBuilder(['w' => 300, 'h' => '400']);
        $devicePixelRatio = 1;
        $srclist = explode(',', $srcset);

        foreach ($srclist as $src) {
            [$generatedURL, $generatedRatio] = explode(' ', $src);

            $dprStr = $devicePixelRatio.'x';
            $this->assertEquals($dprStr, $generatedRatio);

            $this->assertMatchesRegularExpression('/dpr='.$devicePixelRatio.'/', $generatedURL);

            $devicePixelRatio += 1;
        }
    }

    public function testGivenWidthAndHeightSignsURLs()
    {
        $srcset = $this->srcsetBuilder(['w' => 300, 'h' => '400']);
        $srclist = explode(',', $srcset);

        foreach ($srclist as $src) {
            $url = explode(' ', $src)[0];
            $this->assertMatchesRegularExpression('/s=/', $url);

            // parse out query params
            $params = substr($url, strrpos($url, '?'));
            $params = substr($params, 0, strrpos($params, 's=') - 1);

            // parse out sign parameter
            $generatedSignature = substr($url, strrpos($url, 's=') + 2);

            $signatureBase = 'my-key'.'/bridge.png'.$params;
            $expectSignature = md5($signatureBase);

            $this->assertEquals($expectSignature, $generatedSignature);
        }
    }

    public function testGivenAspectRatioSrcsetGeneratesPairs()
    {
        $srcset = $this->srcsetBuilder(['ar' => '3:2']);

        $this->assertCount(31, explode(',', $srcset));
    }

    public function testGivenAspectRatioSrcsetPairsWithinBounds()
    {
        $srcset = $this->srcsetBuilder(['ar' => '3:2']);
        $srclist = explode(',', $srcset);

        $minParsed = explode(' ', $srclist[0])[1];
        $maxParsed = explode(' ', $srclist[count($srclist) - 1])[1];
        $min = $this->parseWidth($minParsed);
        $max = $this->parseWidth($maxParsed);

        $this->assertGreaterThanOrEqual(100, $min);
        $this->assertLessThanOrEqual(8192, $max);
    }

    public function testGivenAspectRatioSrcsetIteratesEighteenPercent()
    {
        $incrementAllowed = .18;
        $srcset = $this->srcsetBuilder(['ar' => '3:2']);
        $srclist = explode(',', $srcset);

        $widths = array_map(
            fn ($src) => $this->parseWidth(explode(' ', $src)[1]),
            $srclist,
        );

        $prev = $widths[0];
        $size = count($widths);
        for ($i = 1; $i < $size; $i++) {
            $width = $widths[$i];
            $this->assertLessThan((1 + $incrementAllowed), ($width / $prev));
            $prev = $width;
        }
    }

    public function testGivenAspectRatioSrcsetSignsUrls()
    {
        $srcset = $this->srcsetBuilder(['ar' => '3:2']);
        $srclist = explode(',', $srcset);

        $srcs = array_map(
            fn ($src) => explode(' ', $src)[0],
            $srclist,
        );

        foreach ($srcs as $src) {
            $this->assertMatchesRegularExpression('/s=/', $src);

            // parse out query params
            $params = substr($src, strrpos($src, '?'));
            $params = substr($params, 0, strrpos($params, 's=') - 1);

            // parse out sign parameter
            $generatedSignature = substr($src, strrpos($src, 's=') + 2);

            $signatureBase = 'my-key'.'/bridge.png'.$params;
            $expectSignature = md5($signatureBase);

            $this->assertEquals($expectSignature, $generatedSignature);
        }
    }

    public function testGivenAspectRatioAndHeightSrcsetIsDPR()
    {
        $srcset = $this->srcsetBuilder(['h' => 400, 'ar' => '3:2']);
        $devicePixelRatio = 1;
        $srclist = explode(',', $srcset);

        foreach ($srclist as $src) {
            [$generatedURL, $generatedRatio] = explode(' ', $src);

            $dprStr = $devicePixelRatio.'x';
            $this->assertEquals($dprStr, $generatedRatio);

            $this->assertMatchesRegularExpression('/dpr='.$devicePixelRatio.'/', $generatedURL);

            $devicePixelRatio += 1;
        }
    }

    public function testGivenAspectRatioAndHeightSignsURLs()
    {
        $srcset = $this->srcsetBuilder(['h' => 400, 'ar' => '3:2']);
        $srclist = explode(',', $srcset);

        foreach ($srclist as $src) {
            $url = explode(' ', $src)[0];
            $this->assertMatchesRegularExpression('/s=/', $url);

            // parse out query params
            $params = substr($url, strrpos($url, '?'));
            $params = substr($params, 0, strrpos($params, 's=') - 1);

            // parse out sign parameter
            $generatedSignature = substr($url, strrpos($url, 's=') + 2);

            $signatureBase = 'my-key'.'/bridge.png'.$params;
            $expectSignature = md5($signatureBase);

            $this->assertEquals($expectSignature, $generatedSignature);
        }
    }

    private function srcsetBuilder($params = [])
    {
        $builder = new UrlBuilder(self::HOST, true, 'my-key', false);

        return $builder->createSrcSet('bridge.png', $params);
    }

    // parse the width as an int, eg "100w" => 100
    private function parseWidth($width)
    {
        return (int) substr($width, 0, strlen($width) - 1);
    }
}
