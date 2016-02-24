<?php

namespace Imgix;

class UrlHelper {

    private $domain;
    private $path;
    private $scheme;
    private $signKey;
    private $params;

    public function __construct($domain, $path, $scheme = "http", $signKey = "", $params = array()) {
        $this->domain = $domain;
        $this->path = substr($path, 0, 4) === "http" ? urlencode($path) : $path;
        $this->path = substr($this->path, 0, 1) !== "/" ? ("/" . $this->path) : $this->path;
        $this->scheme = $scheme;
        $this->signKey = $signKey;
        $this->params = $params;
    }

    public function setParameter($key, $value) {
        if ($key && ($value || $value === 0)) {
            $this->params[$key] = $value;
        } else {
            if (array_key_exists($key, $this->params)) {
                unset($this->params[$key]);
            }
        }
    }

    public function deleteParamter($key) {
        $this->deleteParamter($key, "");
    }

    public function getURL() {
        $queryPairs = array();

        if ($this->params) {
            ksort($this->params);

            foreach ($this->params as $k => $v) {
                $queryPairs[] = rawurlencode($k) . "=" . rawurlencode($v);
            }
        }

        $query = join("&", $queryPairs);
        if ($query) {
            $query = '?' . $query;
        }

        if ($this->signKey) {
            $toSign = $this->signKey . $this->path . $query;
            $sig = md5($toSign);
            if ($query) {
                $query .= "&s=" . $sig;
            } else {
                $query = "?s=" . $sig;
            }
        }

        $url_parts = array('scheme' => $this->scheme, 'host' => $this->domain, 'path' => $this->path, 'query' => $query);

        return self::joinURL($url_parts);
    }

    private static function joinURL($parts) {
        $url = $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . $parts['query'];

        return $url;
    }
}
