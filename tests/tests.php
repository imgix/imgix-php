<?php

use Imgix\UrlBuilder;
use Imgix\UrlHelper;
use Imgix\ShardStrategy;

class ImgixTest extends PHPUnit_Framework_TestCase {

     /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage UrlBuilder requires at least one domain
     */
    public function testURLBuilderRaisesExceptionOnNoDomains() {
        $domains = array();
        $ub = new URLBuilder($domains);
    }

    public function testHelperBuildSignedURLWithHashMapParams() {
        $params = array("w" => 500);
        $uh = new URLHelper("securejackangers.imgix.net", "chester.png", "http", "Q61NvXIy", $params);

        $this->assertEquals($uh->getURL(), "http://securejackangers.imgix.net/chester.png?w=500&s=0ddf97bf1a266a1da6c30c6ce327f917");
    }

    public function testHelperBuildSignedURLWithHashMapAndNoParams() {
        $params = array();
        $uh = new URLHelper("securejackangers.imgix.net", "chester.png", "http", "Q61NvXIy", $params);

        $this->assertEquals($uh->getURL(), "http://securejackangers.imgix.net/chester.png?s=cff7bdfd1b32d82e6b516f7fd3b4f1f4");
    }

    public function testHelperBuildSignedURLWithHashSetterParams() {
        $uh = new URLHelper("securejackangers.imgix.net", "chester.png", "http", "Q61NvXIy");
        $uh->setParameter("w", 500);
        $this->assertEquals($uh->getURL(), "http://securejackangers.imgix.net/chester.png?w=500&s=0ddf97bf1a266a1da6c30c6ce327f917");
    }

    public function testHelperBuildSignedURLWithHashSetterParamsHttps() {
        $uh = new URLHelper("securejackangers.imgix.net", "chester.png", "https", "Q61NvXIy");
        $uh->setParameter("w", 500);
        $this->assertEquals($uh->getURL(), "https://securejackangers.imgix.net/chester.png?w=500&s=0ddf97bf1a266a1da6c30c6ce327f917");
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

	public function testUrlBuilderCRCShard() {

        $domains = array("jackangers.imgix.net", "jackangers2.imgix.net", "jackangers3.imgix.net");

        $ub = new URLBuilder($domains);

        $paths = array("chester.png", "chester1.png", "chester2.png");

        foreach ($paths as $path) {
            $testDomain = parse_url($ub->createURL($path))['host'];

            for ($i = 0; $i < 20; $i++) {
                $this->assertEquals($testDomain, parse_url($ub->createURL($path))['host']);
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