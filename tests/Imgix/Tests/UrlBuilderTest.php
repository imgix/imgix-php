<?php

use Imgix\UrlBuilder;

class UrlBuilderTest extends \PHPUnit\Framework\TestCase {

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
    
    public function testNoParametersGeneratesSrcsetPairs() {
        $srcset = $this->srcsetBuilder();
        $expectedNumberOfPairs = 31;
        $this->assertEquals($expectedNumberOfPairs, count(explode(",", $srcset)));
    }

    public function testSrcsetPairValues() {
        $srcset = $this->srcsetBuilder();
        $index = 0;
        // array of expected resolutions generated by srcset
        $resolutions = array(100, 116, 134, 156, 182, 210, 244, 282,
                             328, 380, 442, 512, 594, 688, 798, 926,
                             1074, 1246, 1446, 1678, 1946, 2258, 2618,
                             3038, 3524, 4088, 4742, 5500, 6380, 7400, 8192);
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
            
            $this->assertRegExp("/dpr=".$devicePixelRatio."/", $generatedURL);
            
            $devicePixelRatio += 1;
        }
    }

    public function testGivenWidthSignsURLs() {
        $srcset = $this->srcsetBuilder(array("w"=>300));
        $srclist = explode(",", $srcset);

        foreach ($srclist as $src) {
            $url = explode(" ", $src)[0];
            $this->assertRegExp("/s=/", $url);

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
        $expectedNumberOfPairs = 31;
        $this->assertEquals($expectedNumberOfPairs, count(explode(",", $srcset)));
    }

    public function testGivenHeightRespectsParameter() {
        $srcset = $this->srcsetBuilder(array("h"=>300));
        $srclist = explode(",", $srcset);

        foreach ($srclist as $src) {
            $this->assertRegExp("/h=300/", $src);
        }
    }

    public function testGivenHeightSrcsetPairsWithinBounds() {
        $srcset = $this->srcsetBuilder(array("h"=>300));
        $srclist = explode(",", $srcset);

        $minParsed = explode(" ", $srclist[0])[1];
        $maxParsed = explode(" ", $srclist[count($srclist)-1])[1];
        $min = $this->parseWidth($minParsed);
        $max = $this->parseWidth($maxParsed);

        $this->assertGreaterThanOrEqual(100, $min);
        $this->assertLessThanOrEqual(8192, $max);
    }

    public function testGivenHeightSrcsetIteratesEighteenPercent() {
        $incrementAllowed = .18;
        $srcset = $this->srcsetBuilder(array("h"=>300));
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

    public function testGivenHeightSrcsetSignsUrls() {
        $srcset = $this->srcsetBuilder(array("h"=>300));
        $srclist = explode(",", $srcset);

        $srcs = array_map(function ($src) {
            return explode(" ", $src)[0];
        }, $srclist);

        foreach ($srcs as $src) {
            $this->assertRegExp("/s=/", $src);

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
            
            $this->assertRegExp("/dpr=".$devicePixelRatio."/", $generatedURL);
            
            $devicePixelRatio += 1;
        }
    }

    public function testGivenWidthAndHeightSignsURLs() {
        $srcset = $this->srcsetBuilder(array("w"=>300, "h"=>"400"));
        $srclist = explode(",", $srcset);

        foreach ($srclist as $src) {
            $url = explode(" ", $src)[0];
            $this->assertRegExp("/s=/", $url);
            
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
            $this->assertRegExp("/s=/", $src);

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
            
            $this->assertRegExp("/dpr=".$devicePixelRatio."/", $generatedURL);
            
            $devicePixelRatio += 1;
        }
    }

    public function testGivenAspectRatioAndHeightSignsURLs() {
        $srcset = $this->srcsetBuilder(array("h"=>400,"ar"=>"3:2"));
        $srclist = explode(",", $srcset);

        foreach ($srclist as $src) {
            $url = explode(" ", $src)[0];
            $this->assertRegExp("/s=/", $url);
 
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
