<?php

use Imgix\UrlBuilder;
use Imgix\ShardStrategy;

class UrlBuilderTest extends PHPUnit_Framework_TestCase {

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage UrlBuilder requires at least one domain
     */
    public function testURLBuilderRaisesExceptionOnNoDomains() {
        $domains = array();
        $ub = new URLBuilder($domains);
    }

    public function testUrlBuilderCycleShard() {
        // generate a url for the number of domains in use ensure they're cycled through...

        $domains = array("jackangers.imgix.net", "jackangers2.imgix.net", "jackangers3.imgix.net");

        $ub = new URLBuilder($domains);
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
        $builder = new UrlBuilder("demos.imgix.net");

        $params = array("w" => 100, "h" => 100);
        $url = $builder->createURL("bridge.png", $params);

        $this->assertEquals($url, "http://demos.imgix.net/bridge.png?h=100&w=100");
    }

    public function testExamplePlainHttps() {
        $builder = new UrlBuilder("demos.imgix.net");

        $builder->setUseHttps(true);
        $params = array("w" => 100, "h" => 100);
        $url = $builder->createURL("bridge.png", $params);

        $this->assertEquals($url, "https://demos.imgix.net/bridge.png?h=100&w=100");
    }

    public function testExamplePlainSecure() {
        $builder = new UrlBuilder("demos.imgix.net");
        $builder->setSignKey("test1234");
        $params = array("w" => 100, "h" => 100);
        $url = $builder->createURL("bridge.png", $params);

        $this->assertEquals($url, "http://demos.imgix.net/bridge.png?h=100&w=100&s=bb8f3a2ab832e35997456823272103a4");
    }
  }
?>