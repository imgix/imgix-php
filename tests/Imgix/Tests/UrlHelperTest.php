<?php

use Imgix\UrlHelper;

class UrlHelperTest extends \PHPUnit\Framework\TestCase {

    /*--- formatPath() ---*/
    public function testHelperFormatPathWithSimplePath() {
        $path = "dog.jpg";
        $uh = new URLHelper("test.imgix.net", $path);

        $this->assertEquals($uh->formatPath($path), "/" . $path);
    }

    public function testHelperFormatPathWithLeadingSlash() {
        $path = "dog.jpg";
        $uh = new URLHelper("test.imgix.net", $path);

        $this->assertEquals($uh->formatPath("/" . $path), "/" . $path);
    }

    public function testHelperFormatPathWithUnencodedCharacters() {
        $path = "images/püg.jpg";
        $uh = new URLHelper("test.imgix.net", $path);

        $this->assertEquals($uh->formatPath($path), "/images/p%C3%BCg.jpg");
    }

    public function testHelperFormatPathWithFullUrl() {
        $path = "http://mywebsite.com/images/dog.jpg";
        $uh = new URLHelper("test.imgix.net", $path);

        $this->assertEquals($uh->formatPath($path), "/http%3A%2F%2Fmywebsite.com%2Fimages%2Fdog.jpg");
    }

    public function testHelperFormatPathWithFullHttpsUrl() {
        $path = "https://mywebsite.com/images/dog.jpg";
        $uh = new URLHelper("test.imgix.net", $path);

        $this->assertEquals($uh->formatPath($path), "/https%3A%2F%2Fmywebsite.com%2Fimages%2Fdog.jpg");
    }

    public function testHelperFormatPathWithFullUrlWithUnencodedCharacters() {
        $path = "http://mywebsite.com/images/püg.jpg";
        $uh = new URLHelper("test.imgix.net", $path);

        $this->assertEquals($uh->formatPath($path), "/http%3A%2F%2Fmywebsite.com%2Fimages%2Fp%C3%BCg.jpg");
    }

    public function testHelperFormatPathWithFullUrlWithLeadingSlash() {
        $path = "/http://mywebsite.com/images/dog.jpg";
        $uh = new URLHelper("test.imgix.net", $path);

        $this->assertEquals($uh->formatPath($path), "/http%3A%2F%2Fmywebsite.com%2Fimages%2Fdog.jpg");
    }

    public function testHelperFormatPathWithFullUrlWithEncodedCharacters() {
        $path = "http://mywebsite.com/images/p%C3%BCg.jpg";
        $uh = new URLHelper("test.imgix.net", $path);

        // The pre-encoded characters should now be *double* encoded
        $this->assertEquals($uh->formatPath($path), "/http%3A%2F%2Fmywebsite.com%2Fimages%2Fp%25C3%25BCg.jpg");
    }

    public function testHelperFormatPathHttpsURLAlreadyEncoded() {
        $path = "https%3A%2F%2Fmywebsite.com%2Fimages%2Ffoo.JPG";
        $uh = new URLHelper("test.imgix.net", $path);

        $this->assertEquals($uh->formatPath($path), "/https%3A%2F%2Fmywebsite.com%2Fimages%2Ffoo.JPG");
    }

    public function testHelperFormatPathHttspURLAlreadyEncodedWithSpecialCharacters() {
        $path = "http%3A%2F%2Fmywebsite.com%2Fimages%2Fpüg.jpg";
        $uh = new URLHelper("test.imgix.net", $path);

        $this->assertEquals($uh->formatPath($path), "/http%3A%2F%2Fmywebsite.com%2Fimages%2Fp%C3%BCg.jpg");
    }

    public function testHelperFormatPathHttpURLAlreadyEncoded() {
        $path = "http%3A%2F%2Fmywebsite.com%2Fimages%2Ffoo.JPG";
        $uh = new URLHelper("test.imgix.net", $path);

        $this->assertEquals($uh->formatPath($path), "/http%3A%2F%2Fmywebsite.com%2Fimages%2Ffoo.JPG");
    }

    public function testHelperFormatPathInvalidURLEncoded() {
        $path = "http%2F%2Fmywebsite.com%2Fimages%2FFoo.JPG";
        $uh = new URLHelper("test.imgix.net", $path);

        $this->assertEquals($uh->formatPath($path), "/http%252F%252Fmywebsite.com%252Fimages%252FFoo.JPG");
    }

    /*--- getURL() ---*/
    public function testHelperBuildSignedURLWithHashMapParams() {
        $params = array("w" => 500);
        $uh = new URLHelper("imgix-library-secure-test-source.imgix.net", "dog.jpg", "http", "EHFQXiZhxP4wA2c4", $params);

        $this->assertEquals("http://imgix-library-secure-test-source.imgix.net/dog.jpg?w=500&s=e4eb402d12bbdf267bf0fc5588170d56", $uh->getURL());
    }

    public function testHelperBuildSignedURLWithHashMapAndNoParams() {
        $params = array();
        $uh = new URLHelper("imgix-library-secure-test-source.imgix.net", "dog.jpg", "http", "EHFQXiZhxP4wA2c4", $params);

        $this->assertEquals("http://imgix-library-secure-test-source.imgix.net/dog.jpg?s=2b0bc99b1042e3c1c9aae6598acc3def", $uh->getURL());
    }

    public function testHelperBuildSignedURLWithHashSetterParams() {
        $uh = new URLHelper("imgix-library-secure-test-source.imgix.net", "dog.jpg", "http", "EHFQXiZhxP4wA2c4");
        $uh->setParameter("w", 500);
        $this->assertEquals("http://imgix-library-secure-test-source.imgix.net/dog.jpg?w=500&s=e4eb402d12bbdf267bf0fc5588170d56", $uh->getURL());
    }

    public function testHelperBuildSignedURLWithHashSetterParamsHttps() {
        $uh = new URLHelper("imgix-library-secure-test-source.imgix.net", "dog.jpg", "https", "EHFQXiZhxP4wA2c4");
        $uh->setParameter("w", 500);
        $this->assertEquals("https://imgix-library-secure-test-source.imgix.net/dog.jpg?w=500&s=e4eb402d12bbdf267bf0fc5588170d56", $uh->getURL());
    }
}

?>
