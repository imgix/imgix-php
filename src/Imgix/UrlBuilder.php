<?php

namespace Imgix;

class UrlBuilder {

    private $currentVersion = "3.2.0";
    private $domain;
    private $useHttps;
    private $signKey;

    const TARGETWIDTHS = array(
        100, 116, 134, 156, 182, 210, 244, 282,
        328, 380, 442, 512, 594, 688, 798, 926,
        1074, 1246, 1446, 1678, 1946, 2258, 2618,
        3038, 3524, 4088, 4742, 5500, 6380, 7400, 8192);

    // define class constants
    // should be private; but visibility modifiers are not supported php version <7.1
    const TARGETRATIOS = array(1, 2, 3, 4, 5);
    // constants cannot be dynamically assigned; keeping as a class variable instead
    private $targetWidths;

    public function __construct($domain, $useHttps = true, $signKey = "", $includeLibraryParam = true) {

        if (!is_string($domain)) {
            throw new \InvalidArgumentException("UrlBuilder must be passed a string domain");
        }

        $this->domain = $domain;
        $this->validateDomain($this->domain);        

        $this->useHttps = $useHttps;
        $this->signKey = $signKey;
        $this->includeLibraryParam = $includeLibraryParam;
        $this->targetWidths = $this->targetWidths();
    }

    private function validateDomain($domain) {
        $DOMAIN_PATTERN = "/^(?:[a-z\d\-_]{1,62}\.){0,125}(?:[a-z\d](?:\-(?=\-*[a-z\d])|[a-z]|\d){0,62}\.)[a-z\d]{1,63}$/";

        if(!preg_match($DOMAIN_PATTERN, $domain)) {
            throw new \InvalidArgumentException('Domain must be passed in as fully-qualified ' . 
            'domain name and should not include a protocol or any path element, i.e. ' .
            '"example.imgix.net".'); 
        }
    }

    public function setSignKey($key) {
        $this->signKey = $key;
    }

    public function setUseHttps($useHttps) {
        $this->useHttps = $useHttps;
    }

    public function setIncludeLibraryParam($includeLibraryParam) {
        $this->includeLibraryParam = $includeLibraryParam;
    }

    public function createURL($path, $params=array()) {
        $scheme = $this->useHttps ? "https" : "http";
        $domain = $this->domain;

        if ($this->includeLibraryParam) {
            $params['ixlib'] = "php-" . $this->currentVersion;
        }

        $uh = new UrlHelper($domain, $path, $scheme, $this->signKey, $params);

        return $uh->getURL();
    }

    public function createSrcSet($path, $params=array(), $start=100, $stop=8192, $tol=8) {
        $width = array_key_exists('w', $params) ? $params['w'] : NULL;
        $height = array_key_exists('h', $params) ? $params['h'] : NULL;
        $aspectRatio = array_key_exists('ar', $params) ? $params['ar'] : NULL;

        if (($width) || ($height && $aspectRatio)) {
            return $this->createDPRSrcSet($path, $params);
        }
        else {
            $targets = $this->targetWidths($start=$start, $stop=$stop, $tol=$tol);
            return $this->createSrcSetPairs($path, $params, $targets=$targets);
        }
    }

        
    /**
     * Generate a list of target widths.
     * 
     * This function generates an array of target widths used to
     * width-describe image candidate strings (URLs) within a
     * srcset attribute.
     * 
     * This function returns an array of even integer values that
     * denote image target widths. This array begins with `$start`
     * and ends with `$stop`. The `$tol` or tolerance value dictates
     * how many values lie between `$start` and `$stop`.
     * 
     * @param  int $start Starting minimum width value.
     * @param  int $stop Stopping maximum width value.
     * @param  int $tol Tolerable amount of width variation.
     * @return int[] $resolutions An array of even integer values.
     */
    public function targetWidths($start=100, $stop=8192, $tol=8) {
        $resolutions = array();

        $ensureEven = function($n) {
            return intval(2 * round($n / 2.0));
        };

        while ($start < $stop && $start < 8192) {
            array_push($resolutions, $ensureEven($start));
            $start *= 1 + ($tol / 100.0) * 2;
        }

        if (end($resolutions) < $stop) {
            array_push($resolutions, $stop);
        }
        
        return $resolutions;
    }

    private function createDPRSrcSet($path, $params) {
        $srcset = "";

        $size = count(self::TARGETRATIOS);
        for ($i = 0; $i < $size; $i++) {
            $currentRatio = self::TARGETRATIOS[$i];
            $currentParams = $params;
            $currentParams['dpr'] = $i+1;
            $srcset .= $this->createURL($path, $currentParams) . " " . $currentRatio . "x,\n";
        }

        return substr($srcset, 0, strlen($srcset) - 2);
    }

    private function createSrcSetPairs($path, $params, $targets=self::TARGETWIDTHS) {
        $srcset = "";
        $currentWidth = NULL;
        $currentParams = NULL;

        $size = count($targets);
        for ($i = 0; $i < $size; $i++) {
            $currentWidth = $targets[$i];
            $currentParams = $params;
            $currentParams['w'] = $currentWidth;
            $srcset .= $this->createURL($path, $currentParams) . " " . $currentWidth . "w,\n";
        }

        return substr($srcset, 0, strlen($srcset) - 2);
    }
}
