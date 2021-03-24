<?php

use Imgix\UrlBuilder;

class UrlBuilderTest extends \PHPUnit\Framework\TestCase {


    const TARGET_WIDTHS = array(
        100, 116, 135, 156, 181, 210, 244, 283,
        328, 380, 441, 512, 594, 689, 799, 927,
        1075, 1247, 1446, 1678, 1946, 2257, 2619,
        3038, 3524, 4087, 4741, 5500, 6380, 7401, 8192);

    public function testURLBuilderRaisesExceptionOnNoDomain() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("UrlBuilder must be passed a string domain");
        $domain = null;
        $ub = new URLBuilder($domain);
    }

    public function testExamplePlain() {
        $builder = new UrlBuilder("demos.imgix.net", false, "",  false);

        $params = array("w" => 100, "h" => 100);
        $url = $builder->createURL("bridge.png", $params);

        $this->assertEquals("http://demos.imgix.net/bridge.png?h=100&w=100", $url);
    }

    public function testExamplePlainUsesHttpsByDefault() {
        // Test default `UrlBuilder` uses https by default.
        // Construct the builder with a `$domain` __only__.
        $builder = new UrlBuilder("demos.imgix.net");
        // Use `setIncludeLibraryParam`.
        $builder->setIncludeLibraryParam(false);
        // Construct a url in accordance with the other tests.
        $params = array("w" => 100, "h" => 100);
        // Create the url with the specified `$path` and `$params`.
        $url = $builder->createURL("bridge.png", $params); 
        $this->assertEquals("https://demos.imgix.net/bridge.png?h=100&w=100", $url);
    }

    public function testExamplePlainHttps() {
        $builder = new UrlBuilder("demos.imgix.net", false, "",  false);

        $builder->setUseHttps(true);
        $params = array("w" => 100, "h" => 100);
        $url = $builder->createURL("bridge.png", $params);

        $this->assertEquals("https://demos.imgix.net/bridge.png?h=100&w=100", $url);
    }

    public function testExamplePlainSecure() {
        $builder = new UrlBuilder("demos.imgix.net", false, "",  false);
        $builder->setSignKey("test1234");
        $params = array("w" => 100, "h" => 100);
        $url = $builder->createURL("bridge.png", $params);

        $this->assertEquals("http://demos.imgix.net/bridge.png?h=100&w=100&s=bb8f3a2ab832e35997456823272103a4", $url);
    }

    public function testParamKeysAreEscaped() {
        $builder = new UrlBuilder("demo.imgix.net", true, "",  false);
        $params = array("hello world" => "interesting");
        $url = $builder->createURL("demo.png", $params);

        $this->assertEquals("https://demo.imgix.net/demo.png?hello%20world=interesting", $url);
    }

    public function testParamValuesAreEscaped() {
        $builder = new UrlBuilder("demo.imgix.net", true, "",  false);
        $params = array("hello_world" => '/foo"><script>alert("hacked")</script><');
        $url = $builder->createURL("demo.png", $params);

        $this->assertEquals("https://demo.imgix.net/demo.png?hello_world=%2Ffoo%22%3E%3Cscript%3Ealert%28%22hacked%22%29%3C%2Fscript%3E%3C", $url);
    }

    public function testZeroValue() {
        $builder = new UrlBuilder("demos.imgix.net", true, "",  false);

        $params = array("foo" => 0);
        $url = $builder->createURL("bridge.png", $params);

        $this->assertEquals("https://demos.imgix.net/bridge.png?foo=0", $url);
    }

    public function testBase64ParamVariantsAreBase64Encoded() {
        $builder = new UrlBuilder("demo.imgix.net", true, "",  false);
        $params = array("txt64" => 'I cannøt belîév∑ it wors! 😱');
        $url = $builder->createURL("~text", $params);

        $this->assertEquals("https://demo.imgix.net/~text?txt64=SSBjYW5uw7h0IGJlbMOuw6l24oiRIGl0IHdvcu-jv3MhIPCfmLE", $url);
    }

    public function testWithFullyQualifiedUrl() {
        $builder = new UrlBuilder("demos.imgix.net", true, "",  false);
        $builder->setSignKey("test1234");
        $url = $builder->createUrl("http://media.giphy.com/media/jCMq0p94fgBIk/giphy.gif");

        $this->assertEquals("https://demos.imgix.net/http%3A%2F%2Fmedia.giphy.com%2Fmedia%2FjCMq0p94fgBIk%2Fgiphy.gif?s=54c35ea3a066357b06bc553ee9975ec9", $url);
    }

    public function testWithFullyQualifiedUrlWithSpaces() {
        $builder = new UrlBuilder("demos.imgix.net", true, "",  false);
        $builder->setSignKey("test1234");
        $url = $builder->createUrl("https://my-demo-site.com/files/133467012/avatar icon.png");

        $this->assertEquals("https://demos.imgix.net/https%3A%2F%2Fmy-demo-site.com%2Ffiles%2F133467012%2Favatar%20icon.png?s=6a1d47f292194cfa7573da0e2bb6b0f4", $url);
    }

    public function testWithFullyQualifiedUrlWithParams() {
        $builder = new UrlBuilder("demos.imgix.net", true, "",  false);
        $builder->setSignKey("test1234");
        $url = $builder->createUrl("https://my-demo-site.com/files/133467012/avatar icon.png?some=chill&params=1");

        $this->assertEquals("https://demos.imgix.net/https%3A%2F%2Fmy-demo-site.com%2Ffiles%2F133467012%2Favatar%20icon.png%3Fsome%3Dchill%26params%3D1?s=bbc73c61ebc739337b852ff8423a1da9", $url);
    }

    public function testInclusionOfLibraryVersionParam() {
        $builder = new UrlBuilder("demos.imgix.net", true);
        $url = $builder->createUrl("https://my-demo-site.com/files/133467012/avatar icon.png?some=chill&params=1");
        $composerFileJson = json_decode(file_get_contents("./composer.json"), true);
        $version = $composerFileJson['version'];

        $this->assertEquals("https://demos.imgix.net/https%3A%2F%2Fmy-demo-site.com%2Ffiles%2F133467012%2Favatar%20icon.png%3Fsome%3Dchill%26params%3D1?ixlib=php-" . $version, $url);
    }

    public function testNestedParameters() {
        $builder = new UrlBuilder("demos.imgix.net", true, "",  false);
        $params = array("auto" => array("compress","format"));
        $url = $builder->createURL("bridge.png", $params);

        $this->assertEquals("https://demos.imgix.net/bridge.png?auto=compress%2Cformat", $url);
    }

    public function test_invalid_domain_append_slash() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Domain must be passed in as fully-qualified ' . 
        'domain name and should not include a protocol or any path element, i.e. ' .
        '"example.imgix.net".');

        $builder = new UrlBuilder("demos.imgix.net/", true, "",  false);
    }

    public function test_invalid_domain_prepend_scheme() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Domain must be passed in as fully-qualified ' . 
        'domain name and should not include a protocol or any path element, i.e. ' .
        '"example.imgix.net".');
        
        $builder = new UrlBuilder("https://demos.imgix.net", true, "",  false);
    }
    
    public function test_invalid_domain_append_dash() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Domain must be passed in as fully-qualified ' . 
        'domain name and should not include a protocol or any path element, i.e. ' .
        '"example.imgix.net".');
        
        $builder = new UrlBuilder("demos.imgix.net-", true, "",  false);
    }

    private function srcsetBuilder($params=array()) {
        $builder = new UrlBuilder("demos.imgix.net", true, "my-key", false);
        return $builder->createSrcSet("bridge.png", $params); 
    }

    // parse the width as an int, eg "100w" => 100
    private function parseWidth($width) {
        return (int)substr($width, 0, strlen($width)-1);
    }

    public function testSrcsetCustomTargetWidths100to7401() {
        $builder = new UrlBuilder("demos.imgix.net", true, "my-key", false);
        $expected = $builder->targetWidths($start=100, $stop=7401);
        $actual = array_slice(self::TARGET_WIDTHS, 0, -1);
        $this->assertEquals(count($expected), count($actual));
        $this->assertEquals($expected, $actual);
    }

    public function testSrcsetCustomTargetWidths328to4088() {
        // Test that the correct number of target widths have been
        // generated and that they start and stop within the expected
        // range, inclusively.
        $builder = new UrlBuilder("demos.imgix.net", true, "my-key", false);

        $start = 328; $stop = 4087;
        $idx_328 = 8; $idx_4087 = 18;

        $expected = $builder->targetWidths($start=$start, $stop=$stop);
        $actual = array_slice(self::TARGET_WIDTHS, $idx_328, $idx_4087);
        $this->assertEquals(count($actual), count($expected));
        $this->assertEquals($start, $actual[0]);
        $this->assertEquals($stop, end($actual));
    }

    public function testTargetWidthsMaxTolerance() {
        $builder = new UrlBuilder("demos.imgix.net", true, "my-key", false);
        $expected = array(0 => 100, 1 => 8192); 
        $actual = $builder->targetWidths($start=100, $stop=8192, $tol=10000000);
        $this->assertEquals($expected, $actual);
    }
    
    public function testNoParametersGeneratesSrcsetPairs() {
        $srcset = $this->srcsetBuilder();
        $expectedNumberOfPairs = 31;
        $this->assertEquals($expectedNumberOfPairs, count(explode(",", $srcset)));
    }

    public function testCustomSrcsetPairs() {
        // Test custom srcset pairs within ranges.
        $builder = new UrlBuilder("demos.imgix.net", true, false);
        $opts = array('start' => 328, 'stop' => 328);
        $actual = $builder->createSrcSet($path="image.jpg", $params=array(), $options=$opts);
        $expected = 'https://demos.imgix.net/image.jpg?ixlib=php-3.3.1&w=328 328w';
        $this->assertEquals($expected, $actual);

        $builder = new UrlBuilder("demos.imgix.net", true, false);
        $actual = $builder->createSrcSet(
            $path="image.jpg", 
            $params=array(), 
            $options=array('start' => 720, 'stop' => 720));

        $expected = 'https://demos.imgix.net/image.jpg?ixlib=php-3.3.1&w=720 720w';
        $this->assertEquals($expected, $actual);

        $builder = new UrlBuilder("demos.imgix.net", true, false);
        $opts = array('start' => 640, 'stop' => 720);
        $actual = $builder->createSrcSet($path="image.jpg", $params=array(), $options=$opts);
        $expected = // Raw string literal
'https://demos.imgix.net/image.jpg?ixlib=php-3.3.1&w=640 640w,
https://demos.imgix.net/image.jpg?ixlib=php-3.3.1&w=720 720w';

        $this->assertEquals($expected, $actual);

        // Now test custom tolerances (also within a range).
        $builder = new UrlBuilder("demos.imgix.net", true, false);
        $opts = array('start' => 100, 'stop' => 108, 'tol' => 0.01);
        $actual = $builder->createSrcSet($path="image.jpg", $params=array(), $options=$opts);
        $expected = // Raw string literal
'https://demos.imgix.net/image.jpg?ixlib=php-3.3.1&w=100 100w,
https://demos.imgix.net/image.jpg?ixlib=php-3.3.1&w=102 102w,
https://demos.imgix.net/image.jpg?ixlib=php-3.3.1&w=104 104w,
https://demos.imgix.net/image.jpg?ixlib=php-3.3.1&w=106 106w,
https://demos.imgix.net/image.jpg?ixlib=php-3.3.1&w=108 108w';

        $this->assertEquals($expected, $actual);
    }

    public function testSrcsetPairValues() {
        $srcset = $this->srcsetBuilder();
        $index = 0;
        // array of expected resolutions generated by srcset
        $resolutions = self::TARGET_WIDTHS;
        $srclist = explode(",", $srcset);
        $matches = array();
        
        foreach ($srclist as $src) {
            $width = explode(" ", $src)[1];
            
            // extract width int values
            preg_match("/\d+/", $width, $matches);
            $this->assertEquals($resolutions[$index], $matches[0]);
            $index ++;
        }
    }

    public function testGivenWidthSrcsetIsDPR() {
        $srcset = $this->srcsetBuilder(array("w"=>300));
        $devicePixelRatio = 1;
        $srclist = explode(",", $srcset);

        foreach ($srclist as $src) {
            list($generatedURL, $generatedRatio) = explode(" ", $src);
            
            $dprStr = $devicePixelRatio . "x";
            $this->assertEquals($dprStr, $generatedRatio);
            
            $this->assertMatchesRegularExpression("/dpr=".$devicePixelRatio."/", $generatedURL);
            
            $devicePixelRatio += 1;
        }
    }

    public function testDprSrcsetWithQ100() {
        $builder = new UrlBuilder("demos.imgix.net", true, false);
        $actual = $builder->createSrcSet(
            $path="image.jpg", $params=array("w" => 640, "q" => 100));
        $expected =
'https://demos.imgix.net/image.jpg?dpr=1&ixlib=php-3.3.1&q=100&w=640 1x,
https://demos.imgix.net/image.jpg?dpr=2&ixlib=php-3.3.1&q=100&w=640 2x,
https://demos.imgix.net/image.jpg?dpr=3&ixlib=php-3.3.1&q=100&w=640 3x,
https://demos.imgix.net/image.jpg?dpr=4&ixlib=php-3.3.1&q=100&w=640 4x,
https://demos.imgix.net/image.jpg?dpr=5&ixlib=php-3.3.1&q=100&w=640 5x';
        $this->assertEquals($expected, $actual);
    }

    // Test that variable quality is enabled by default.
    public function testDprSrcsetWithDefaultQuality() {
        $builder = new UrlBuilder("demos.imgix.net", true, false);
        $actual = $builder->createSrcSet($path="image.jpg", $params=array("w" => 740));

        $expected =
'https://demos.imgix.net/image.jpg?dpr=1&ixlib=php-3.3.1&q=75&w=740 1x,
https://demos.imgix.net/image.jpg?dpr=2&ixlib=php-3.3.1&q=50&w=740 2x,
https://demos.imgix.net/image.jpg?dpr=3&ixlib=php-3.3.1&q=35&w=740 3x,
https://demos.imgix.net/image.jpg?dpr=4&ixlib=php-3.3.1&q=23&w=740 4x,
https://demos.imgix.net/image.jpg?dpr=5&ixlib=php-3.3.1&q=20&w=740 5x';
        $this->assertEquals($expected, $actual);
    }

    // Test that `disableVariableQuality = true` disables `q` params.
    public function testDprVariableQualityDisabled() {
        $builder = new UrlBuilder("demos.imgix.net", true, false);
        $params = array("w" => 640);
        $opts = array('disableVariableQuality' => true);
        $actual = $builder->createSrcSet($path="image.jpg", $params=$params, $opts=$opts);

        $expected =
'https://demos.imgix.net/image.jpg?dpr=1&ixlib=php-3.3.1&w=640 1x,
https://demos.imgix.net/image.jpg?dpr=2&ixlib=php-3.3.1&w=640 2x,
https://demos.imgix.net/image.jpg?dpr=3&ixlib=php-3.3.1&w=640 3x,
https://demos.imgix.net/image.jpg?dpr=4&ixlib=php-3.3.1&w=640 4x,
https://demos.imgix.net/image.jpg?dpr=5&ixlib=php-3.3.1&w=640 5x';
        $this->assertEquals($expected, $actual);
    }

    // Test that `q` overrides the default qualities when
    // `disableVariableQuality = false`.
    public function testDprSrcsetQOverridesEnabledVariableQuality() {
        $builder = new UrlBuilder("demos.imgix.net", true, false);
        $params = array("w" => 540, "q" => 75);
        $opts = array('disableVariableQuality' => false); // Enabled.
        $actual = $builder->createSrcSet($path="image.jpg", $params, $opts);

        $expected =
'https://demos.imgix.net/image.jpg?dpr=1&ixlib=php-3.3.1&q=75&w=540 1x,
https://demos.imgix.net/image.jpg?dpr=2&ixlib=php-3.3.1&q=75&w=540 2x,
https://demos.imgix.net/image.jpg?dpr=3&ixlib=php-3.3.1&q=75&w=540 3x,
https://demos.imgix.net/image.jpg?dpr=4&ixlib=php-3.3.1&q=75&w=540 4x,
https://demos.imgix.net/image.jpg?dpr=5&ixlib=php-3.3.1&q=75&w=540 5x';
        $this->assertEquals($expected, $actual);
    }

    // Test that `q` overrides `disableVariableQuality = true`.
    public function testDprSrcsetQOverridesDisabledVariableQuality() {
        $builder = new UrlBuilder("demos.imgix.net", true, false);
        $opts = array('disableVariableQuality' => true); // Disabled.
        $actual = $builder->createSrcSet(
            $path="image.jpg",
            $params=array("w" => 440, "q" => 99),
            $options=$opts);

        $expected =
'https://demos.imgix.net/image.jpg?dpr=1&ixlib=php-3.3.1&q=99&w=440 1x,
https://demos.imgix.net/image.jpg?dpr=2&ixlib=php-3.3.1&q=99&w=440 2x,
https://demos.imgix.net/image.jpg?dpr=3&ixlib=php-3.3.1&q=99&w=440 3x,
https://demos.imgix.net/image.jpg?dpr=4&ixlib=php-3.3.1&q=99&w=440 4x,
https://demos.imgix.net/image.jpg?dpr=5&ixlib=php-3.3.1&q=99&w=440 5x';
        $this->assertEquals($expected, $actual);
    }

    public function testCreateSrcSetFromWidthsArray() {
        $builder = new UrlBuilder("demos.imgix.net", true, false);
        $opts = array('widths' => array(100, 200, 303, 404, 535));
        $actual = $builder->createSrcSet($path="image.jpg", $params=array(), $options=$opts);
        $expected =
'https://demos.imgix.net/image.jpg?ixlib=php-3.3.1&w=100 100w,
https://demos.imgix.net/image.jpg?ixlib=php-3.3.1&w=200 200w,
https://demos.imgix.net/image.jpg?ixlib=php-3.3.1&w=303 303w,
https://demos.imgix.net/image.jpg?ixlib=php-3.3.1&w=404 404w,
https://demos.imgix.net/image.jpg?ixlib=php-3.3.1&w=535 535w';

        $this->assertEquals($expected, $actual);
    }

    public function testGivenWidthSignsURLs() {
        $srcset = $this->srcsetBuilder(array("w"=>300));
        $srclist = explode(",", $srcset);

        foreach ($srclist as $src) {
            $url = explode(" ", $src)[0];
            $this->assertMatchesRegularExpression("/s=/", $url);

            // parse out query params
            $params = substr($url, strrpos($url, "?"));
            $params = substr($params, 0, strrpos($params, "s=")-1);

            // parse out sign parameter
            $generatedSignature = substr($url, strrpos($url, "s=")+2);

            $signatureBase = "my-key" . "/bridge.png" . $params;
            $expectSignature = md5($signatureBase);

            $this->assertEquals($expectSignature, $generatedSignature);
        }
    }

    public function testGivenHeightSrcsetGeneratesPairs() {
        $srcset = $this->srcsetBuilder(array("h"=>300));
        $expectedNumberOfPairs = 5;
        $this->assertEquals($expectedNumberOfPairs, count(explode(",", $srcset)));
    }

    public function testGivenHeightRespectsParameter() {
        $srcset = $this->srcsetBuilder(array("h"=>300));
        $srclist = explode(",", $srcset);

        foreach ($srclist as $src) {
            $this->assertMatchesRegularExpression("/h=300/", $src);
        }
    }

    public function testHeightBasedSrcsetHasDprValues() {
        $srcset = $this->srcsetBuilder(array("h"=>300));
        $srclist = explode(",", $srcset);

        foreach ($srclist as $i=>$src) {
            $dpr = explode(" ", $src)[1];
            $this->assertMatchesRegularExpression("/x/", $dpr);
        }
    }

    public function testHeightIncludesDPRParam() {
        $srcset = $this->srcsetBuilder(array("h"=>300));
        $srclist = explode(",", $srcset);

        foreach ($srclist as $i=>$src) {
            $dpr = explode(" ", $src)[0];
            $this->assertMatchesRegularExpression("/dpr=/", $dpr);
        }
    }

    public function testGivenHeightSrcsetSignsUrls() {
        $srcset = $this->srcsetBuilder(array("h"=>300));
        $srclist = explode(",", $srcset);

        $srcs = array_map(function ($src) {
            return explode(" ", $src)[0];
        }, $srclist);

        foreach ($srcs as $src) {
            $this->assertMatchesRegularExpression("/s=/", $src);

            // parse out query params
            $params = substr($src, strrpos($src, "?"));
            $params = substr($params, 0, strrpos($params, "s=")-1);

            // parse out sign parameter
            $generatedSignature = substr($src, strrpos($src, "s=")+2);

            $signatureBase = "my-key" . "/bridge.png" . $params;
            $expectSignature = md5($signatureBase);

            $this->assertEquals($expectSignature, $generatedSignature);
        }
    }
    
    public function testGivenWidthAndHeightSrcsetIsDPR() {
        $srcset = $this->srcsetBuilder(array("w"=>300, "h"=>"400"));
        $devicePixelRatio = 1;
        $srclist = explode(",", $srcset);

        foreach ($srclist as $src) {
            list($generatedURL, $generatedRatio) = explode(" ", $src);
            
            $dprStr = $devicePixelRatio . "x";
            $this->assertEquals($dprStr, $generatedRatio);
            
            $this->assertMatchesRegularExpression("/dpr=".$devicePixelRatio."/", $generatedURL);
            
            $devicePixelRatio += 1;
        }
    }

    public function testGivenWidthAndHeightSignsURLs() {
        $srcset = $this->srcsetBuilder(array("w"=>300, "h"=>"400"));
        $srclist = explode(",", $srcset);

        foreach ($srclist as $src) {
            $url = explode(" ", $src)[0];
            $this->assertMatchesRegularExpression("/s=/", $url);
            
            // parse out query params
            $params = substr($url, strrpos($url, "?"));
            $params = substr($params, 0, strrpos($params, "s=")-1);

            // parse out sign parameter
            $generatedSignature = substr($url, strrpos($url, "s=")+2);

            $signatureBase = "my-key" . "/bridge.png" . $params;
            $expectSignature = md5($signatureBase);

            $this->assertEquals($expectSignature, $generatedSignature);
        }
    }

    public function testGivenAspectRatioSrcsetGeneratesPairs() {
        $srcset = $this->srcsetBuilder(array("ar"=>"3:2"));
        $expectedNumberOfPairs = 31;
        $this->assertEquals($expectedNumberOfPairs, count(explode(",", $srcset)));
    }

    public function testGivenAspectRatioSrcsetPairsWithinBounds() {
        $srcset = $this->srcsetBuilder(array("ar"=>"3:2"));
        $srclist = explode(",", $srcset);

        $minParsed = explode(" ", $srclist[0])[1];
        $maxParsed = explode(" ", $srclist[count($srclist)-1])[1];
        $min = $this->parseWidth($minParsed);
        $max = $this->parseWidth($maxParsed);

        $this->assertGreaterThanOrEqual(100, $min);
        $this->assertLessThanOrEqual(8192, $max);
    }

    public function testGivenAspectRatioSrcsetIteratesEighteenPercent() {
        $incrementAllowed = .18;
        $srcset = $this->srcsetBuilder(array("ar"=>"3:2"));
        $srclist = explode(",", $srcset);

        $widths = array_map(function ($src) {
            return $this->parseWidth(explode(" ", $src)[1]);
        }, $srclist);

        $prev = $widths[0];
        $size = count($widths);
        for ($i = 1; $i < $size; $i++) {
            $width = $widths[$i];
            $this->assertLessThan((1 + $incrementAllowed), ($width / $prev));
            $prev = $width;
        }
    }

    public function testGivenAspectRatioSrcsetSignsUrls() {
        $srcset = $this->srcsetBuilder(array("ar"=>"3:2"));
        $srclist = explode(",", $srcset);

        $srcs = array_map(function ($src) {
            return explode(" ", $src)[0];
        }, $srclist);

        foreach ($srcs as $src) {
            $this->assertMatchesRegularExpression("/s=/", $src);

            // parse out query params
            $params = substr($src, strrpos($src, "?"));
            $params = substr($params, 0, strrpos($params, "s=")-1);

            // parse out sign parameter
            $generatedSignature = substr($src, strrpos($src, "s=")+2);

            $signatureBase = "my-key" . "/bridge.png" . $params;
            $expectSignature = md5($signatureBase);

            $this->assertEquals($expectSignature, $generatedSignature);
        }
    }

    public function testGivenAspectRatioAndHeightSrcsetIsDPR() {
        $srcset = $this->srcsetBuilder(array("h"=>400,"ar"=>"3:2"));
        $devicePixelRatio = 1;
        $srclist = explode(",", $srcset);

        foreach ($srclist as $src) {
            list($generatedURL, $generatedRatio) = explode(" ", $src);
            
            $dprStr = $devicePixelRatio . "x";
            $this->assertEquals($dprStr, $generatedRatio);
            
            $this->assertMatchesRegularExpression("/dpr=".$devicePixelRatio."/", $generatedURL);
            
            $devicePixelRatio += 1;
        }
    }

    public function testGivenAspectRatioAndHeightSignsURLs() {
        $srcset = $this->srcsetBuilder(array("h"=>400,"ar"=>"3:2"));
        $srclist = explode(",", $srcset);

        foreach ($srclist as $src) {
            $url = explode(" ", $src)[0];
            $this->assertMatchesRegularExpression("/s=/", $url);
 
            // parse out query params
            $params = substr($url, strrpos($url, "?"));
            $params = substr($params, 0, strrpos($params, "s=")-1);

            // parse out sign parameter
            $generatedSignature = substr($url, strrpos($url, "s=")+2);

            $signatureBase = "my-key" . "/bridge.png" . $params;
            $expectSignature = md5($signatureBase);

            $this->assertEquals($expectSignature, $generatedSignature);
        }
    }

  }
?>
