<?php

namespace Imgix;

class UrlBuilder {

    private $currentVersion = "3.1.0";
    private $domain;
    private $useHttps;
    private $signKey;

    // define class constants
    // should be private; but visibility modifiers are not supported php version <7.1
    const TARGETRATIOS = array(1, 2, 3, 4, 5);
    // constants cannot be dynamically assigned; keeping as a class variable instead
    private $targetWidths;

    public function __construct($domain, $useHttps = false, $signKey = "", $includeLibraryParam = true) {

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

    public function createSrcSet($path, $params=array()) {
        $width = array_key_exists('w', $params) ? $params['w'] : NULL;
        $height = array_key_exists('h', $params) ? $params['h'] : NULL;
        $aspectRatio = array_key_exists('ar', $params) ? $params['ar'] : NULL;

        if (($width) || ($height && $aspectRatio)) {
            return $this->createDPRSrcSet($path, $params);
        }
        else {
            return $this->createSrcSetPairs($path, $params);
        }
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

    private function createSrcSetPairs($path, $params) {
        $srcset = "";
        $currentWidth = NULL;
        $currentParams = NULL;

        $size = count($this->targetWidths);
        for ($i = 0; $i < $size; $i++) {
            $currentWidth = $this->targetWidths[$i];
            $currentParams = $params;
            $currentParams['w'] = $currentWidth;
            $srcset .= $this->createURL($path, $currentParams) . " " . $currentWidth . "w,\n";
        }

        return substr($srcset, 0, strlen($srcset) - 2);
    }

    private function targetWidths() {
        $resolutions = array();
        $prev = 100;
        $INCREMENT_PERCENTAGE = 8;
        $MAX_SIZE = 8192;

        $ensureEven = function($n) {
            return 2 * round($n / 2);
        };

        while ($prev <= $MAX_SIZE) {
            array_push($resolutions, $ensureEven($prev));
            $prev *= 1 + ($INCREMENT_PERCENTAGE / 100) * 2;
        }

        array_push($resolutions, $MAX_SIZE);
        return $resolutions;
    }
}
