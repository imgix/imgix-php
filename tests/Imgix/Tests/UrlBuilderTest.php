<?php

use Imgix\UrlBuilder;
use Imgix\ShardStrategy;

class UrlBuilderTest extends \PHPUnit\Framework\TestCase {

    public function testURLBuilderRaisesExceptionOnNoDomains() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("UrlBuilder requires at least one domain");
        $domains = array();
        $ub = new URLBuilder($domains);
    }

    public function testUrlBuilderCycleShard() {
        // generate a url for the number of domains in use ensure they're cycled through...

        $domains = array("jackangers.imgix.net", "jackangers2.imgix.net", "jackangers3.imgix.net");

        $ub = new URLBuilder($domains, false, "", ShardStrategy::CRC, false);
        $ub->setShardStrategy(ShardStrategy::CYCLE);

        for ($i = 0; $i < 100; $i++) {
          $used = array();
          foreach ($domains as $domain) {
            $url = $ub->createURL("chester.png");
            $curDomain = parse_url($url)["host"];
            $this->assertFalse(in_array($curDomain, $used));
            $used[] = $curDomain;
          }
        }
    }

    public function testExamplePlain() {
        $builder = new UrlBuilder("demos.imgix.net", false, "", ShardStrategy::CRC, false);

        $params = array("w" => 100, "h" => 100);
        $url = $builder->createURL("bridge.png", $params);

        $this->assertEquals("http://demos.imgix.net/bridge.png?h=100&w=100", $url);
    }

    public function testExamplePlainHttps() {
        $builder = new UrlBuilder("demos.imgix.net", false, "", ShardStrategy::CRC, false);

        $builder->setUseHttps(true);
        $params = array("w" => 100, "h" => 100);
        $url = $builder->createURL("bridge.png", $params);

        $this->assertEquals("https://demos.imgix.net/bridge.png?h=100&w=100", $url);
    }

    public function testExamplePlainSecure() {
        $builder = new UrlBuilder("demos.imgix.net", false, "", ShardStrategy::CRC, false);
        $builder->setSignKey("test1234");
        $params = array("w" => 100, "h" => 100);
        $url = $builder->createURL("bridge.png", $params);

        $this->assertEquals("http://demos.imgix.net/bridge.png?h=100&w=100&s=bb8f3a2ab832e35997456823272103a4", $url);
    }

    public function testParamKeysAreEscaped() {
        $builder = new UrlBuilder("demo.imgix.net", true, "", ShardStrategy::CRC, false);
        $params = array("hello world" => "interesting");
        $url = $builder->createURL("demo.png", $params);

        $this->assertEquals("https://demo.imgix.net/demo.png?hello%20world=interesting", $url);
    }

    public function testParamValuesAreEscaped() {
        $builder = new UrlBuilder("demo.imgix.net", true, "", ShardStrategy::CRC, false);
        $params = array("hello_world" => '/foo"><script>alert("hacked")</script><');
        $url = $builder->createURL("demo.png", $params);

        $this->assertEquals("https://demo.imgix.net/demo.png?hello_world=%2Ffoo%22%3E%3Cscript%3Ealert%28%22hacked%22%29%3C%2Fscript%3E%3C", $url);
    }

    public function testZeroValue() {
        $builder = new UrlBuilder("demos.imgix.net", true, "", ShardStrategy::CRC, false);

        $params = array("foo" => 0);
        $url = $builder->createURL("bridge.png", $params);

        $this->assertEquals("https://demos.imgix.net/bridge.png?foo=0", $url);
    }

    public function testBase64ParamVariantsAreBase64Encoded() {
        $builder = new UrlBuilder("demo.imgix.net", true, "", ShardStrategy::CRC, false);
        $params = array("txt64" => 'I cannøt belîév∑ it wors! 😱');
        $url = $builder->createURL("~text", $params);

        $this->assertEquals("https://demo.imgix.net/~text?txt64=SSBjYW5uw7h0IGJlbMOuw6l24oiRIGl0IHdvcu-jv3MhIPCfmLE", $url);
    }

    public function testWithFullyQualifiedUrl() {
        $builder = new UrlBuilder("demos.imgix.net", true, "", ShardStrategy::CRC, false);
        $builder->setSignKey("test1234");
        $url = $builder->createUrl("http://media.giphy.com/media/jCMq0p94fgBIk/giphy.gif");

        $this->assertEquals("https://demos.imgix.net/http%3A%2F%2Fmedia.giphy.com%2Fmedia%2FjCMq0p94fgBIk%2Fgiphy.gif?s=54c35ea3a066357b06bc553ee9975ec9", $url);
    }

    public function testWithFullyQualifiedUrlWithSpaces() {
        $builder = new UrlBuilder("demos.imgix.net", true, "", ShardStrategy::CRC, false);
        $builder->setSignKey("test1234");
        $url = $builder->createUrl("https://my-demo-site.com/files/133467012/avatar icon.png");

        $this->assertEquals("https://demos.imgix.net/https%3A%2F%2Fmy-demo-site.com%2Ffiles%2F133467012%2Favatar%20icon.png?s=6a1d47f292194cfa7573da0e2bb6b0f4", $url);
    }

    public function testWithFullyQualifiedUrlWithParams() {
        $builder = new UrlBuilder("demos.imgix.net", true, "", ShardStrategy::CRC, false);
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
        $builder = new UrlBuilder("demos.imgix.net", true, "", ShardStrategy::CRC, false);
        $params = array("auto" => array("compress","format"));
        $url = $builder->createURL("bridge.png", $params);

        $this->assertEquals("https://demos.imgix.net/bridge.png?auto=compress%2Cformat", $url);
    }
    public function test_invalid_domain_append_slash() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Domains must be passed in as fully-qualified ' . 
        'domain names and should not include a protocol or any path element, i.e. ' .
        '"example.imgix.net".');

        $builder = new UrlBuilder("demos.imgix.net/", true, "", ShardStrategy::CRC, false);
    }
    public function test_invalid_domain_prepend_scheme() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Domains must be passed in as fully-qualified ' . 
        'domain names and should not include a protocol or any path element, i.e. ' .
        '"example.imgix.net".');
        
        $builder = new UrlBuilder("https://demos.imgix.net", true, "", ShardStrategy::CRC, false);
    }
    public function test_invalid_domain_append_dash() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Domains must be passed in as fully-qualified ' . 
        'domain names and should not include a protocol or any path element, i.e. ' .
        '"example.imgix.net".');
        
        $builder = new UrlBuilder("demos.imgix.net-", true, "", ShardStrategy::CRC, false);
    }
    public function test_invalid_domain_array() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Domains must be passed in as fully-qualified ' . 
        'domain names and should not include a protocol or any path element, i.e. ' .
        '"example.imgix.net".');
        
        $builder = new UrlBuilder(array("demos.imgix.net","demos.imgix.net-"), true, "", ShardStrategy::CYCLE, false);
    }
    public function test_deprecation_warning() {
        # Tests for deprecation warning using a custom error handler
        # as the warning is typically suppressed to prevent polluting 
        # error logs
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            $warning_message = "Warning: Domain sharding has been deprecated and will be removed in the next major version.";
            $this->assertEquals($warning_message, $errstr);
            $this->assertEquals(E_USER_DEPRECATED, $errno);
        }, E_USER_DEPRECATED);

        $builder = new UrlBuilder(array("demos.imgix.net","demos.imgix.net"), true, "", ShardStrategy::CYCLE, false);
    }
  }
?>
