<?php

use Imgix\UrlBuilder;

class PathEncodingTest extends \PHPUnit\Framework\TestCase {
  // NOTE: all the expected urls bellow resolve to an actual image.
    const HOST = "sdk-test.imgix.net";

    public function testBracketEncoding() {
    $builder = new UrlBuilder(self::HOST, true, "",  false);
    $url = $builder->createURL("/ <>[]{}|\\^%.jpg");
    $this->assertEquals(
      "https://sdk-test.imgix.net/%20%3C%3E%5B%5D%7B%7D%7C%5C%5E%25.jpg",
       $url);
    }

    public function testSpecialCharsEncoding() {
    $builder = new UrlBuilder(self::HOST, true, "",  false);
    $url = $builder->createURL("&$+,:;=?@#.jpg");
    $this->assertEquals(
      "https://sdk-test.imgix.net/%26%24%2B%2C:%3B%3D%3F@%23.jpg",
       $url);
    }

    public function testUnicodeEncoding() {
    $builder = new UrlBuilder(self::HOST, true, "",  false);
    $url = $builder->createURL("/ساندویچ.jpg");
    $this->assertEquals(
      "https://sdk-test.imgix.net/%D8%B3%D8%A7%D9%86%D8%AF%D9%88%DB%8C%DA%86.jpg",
       $url);
    }

  }
?>
