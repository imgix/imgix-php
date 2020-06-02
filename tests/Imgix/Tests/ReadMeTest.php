<?php

use Imgix\UrlBuilder;

class ReadMeTest extends \PHPUnit\Framework\TestCase {

    public function testFixedWithImages() {
        $builder = new UrlBuilder("demos.imgix.net", true, "my-key", false);
        $actual = $builder->createSrcSet("image.png", array("h"=>800, "ar"=>"3:2", "fit"=>"crop"));
        $expected = 
"https://demos.imgix.net/image.png?ar=3%3A2&dpr=1&fit=crop&h=800&q=75&s=b6b4a327a9e5a9ce5c9251b736c98633 1x,
https://demos.imgix.net/image.png?ar=3%3A2&dpr=2&fit=crop&h=800&q=50&s=4f96c2dffa682c081ba9b994c49222cc 2x,
https://demos.imgix.net/image.png?ar=3%3A2&dpr=3&fit=crop&h=800&q=35&s=7b2a069e769cfeaf9e6dbb4679aea2bc 3x,
https://demos.imgix.net/image.png?ar=3%3A2&dpr=4&fit=crop&h=800&q=23&s=af185a51455a8e97025728b8f303e038 4x,
https://demos.imgix.net/image.png?ar=3%3A2&dpr=5&fit=crop&h=800&q=20&s=f010a3d00e54153a36d3c27d9317bf8b 5x";

        $this->assertEquals($expected, $actual);
    }

    public function testFixedWidthVariableQualityEnabled() {
        $builder = new UrlBuilder("demos.imgix.net", true, "", false);
        $params = array("w" => 100);
        $actual = $builder->createSrcSet($path="image.jpg", $params=$params);

        $expected =
'https://demos.imgix.net/image.jpg?dpr=1&q=75&w=100 1x,
https://demos.imgix.net/image.jpg?dpr=2&q=50&w=100 2x,
https://demos.imgix.net/image.jpg?dpr=3&q=35&w=100 3x,
https://demos.imgix.net/image.jpg?dpr=4&q=23&w=100 4x,
https://demos.imgix.net/image.jpg?dpr=5&q=20&w=100 5x';
        $this->assertEquals($expected, $actual);
    }


    public function testFluidWidthCustomWidths() {
        $builder = new UrlBuilder("demos.imgix.net", true, "", false);
        $opts = array('widths' => array(144, 240, 320, 446, 640));
        $actual = $builder->createSrcSet($path="image.jpg", $params=array(), $options=$opts);
        $expected =
'https://demos.imgix.net/image.jpg?w=144 144w,
https://demos.imgix.net/image.jpg?w=240 240w,
https://demos.imgix.net/image.jpg?w=320 320w,
https://demos.imgix.net/image.jpg?w=446 446w,
https://demos.imgix.net/image.jpg?w=640 640w';

        $this->assertEquals($expected, $actual);
    }

    public function testFluidWidthRanges() {
        // Now test custom tolerances (also within a range).
        $builder = new UrlBuilder("demo.imgix.net", true, "", false);
        $opts = array('start' => 500, 'stop' => 2000);
        $actual = $builder->createSrcSet($path="image.jpg", $params=array(), $options=$opts);
        $expected = 
'https://demo.imgix.net/image.jpg?w=500 500w,
https://demo.imgix.net/image.jpg?w=580 580w,
https://demo.imgix.net/image.jpg?w=673 673w,
https://demo.imgix.net/image.jpg?w=780 780w,
https://demo.imgix.net/image.jpg?w=905 905w,
https://demo.imgix.net/image.jpg?w=1050 1050w,
https://demo.imgix.net/image.jpg?w=1218 1218w,
https://demo.imgix.net/image.jpg?w=1413 1413w,
https://demo.imgix.net/image.jpg?w=1639 1639w,
https://demo.imgix.net/image.jpg?w=1901 1901w,
https://demo.imgix.net/image.jpg?w=2000 2000w';

        $this->assertEquals($expected, $actual);
    }

    public function testFluidWidthRangesTolerance() {
        $builder = new UrlBuilder("demo.imgix.net", true, "", false);
        $opts = array('start' => 100, 'stop' => 384, 'tol' => 0.20);
        $actual = $builder->createSrcSet($path="image.jpg", $params=array(), $options=$opts);
        $expected = 
'https://demo.imgix.net/image.jpg?w=100 100w,
https://demo.imgix.net/image.jpg?w=140 140w,
https://demo.imgix.net/image.jpg?w=196 196w,
https://demo.imgix.net/image.jpg?w=274 274w,
https://demo.imgix.net/image.jpg?w=384 384w';

        $this->assertEquals($expected, $actual);
    }
}
?>