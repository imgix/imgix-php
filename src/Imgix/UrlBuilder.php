<?php

namespace Imgix;

class UrlBuilder {

    private $currentVersion = "2.3.0";
    private $domain;
    private $useHttps;
    private $signKey;

    public function __construct($domain, $useHttps = false, $signKey = "", $includeLibraryParam = true) {

        if (!is_string($domain)) {
            throw new \InvalidArgumentException("UrlBuilder must be passed a valid string domain");
        }

        $this->domain = $domain;
        $this->validateDomain($this->domain);        

        $this->useHttps = $useHttps;
        $this->signKey = $signKey;
        $this->includeLibraryParam = $includeLibraryParam;
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
}
