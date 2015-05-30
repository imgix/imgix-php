<?php

use Imgix\UrlHelper;

class UrlHelperTest extends PHPUnit_Framework_TestCase {

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
}

?>