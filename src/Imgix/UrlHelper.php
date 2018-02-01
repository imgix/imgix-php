<?php

namespace Imgix;

class UrlHelper {

    private $domain;
    private $path;
    private $scheme;
    private $signKey;
    private $params;

    public function __construct($domain, $path, $scheme = "http", $signKey = "", $params = array(), $rawEncodePath = false) {
        $this->domain = $domain;
        $this->path = $this->formatPath( $path, $rawEncodePath );
        $this->scheme = $scheme;
        $this->signKey = $signKey;
        $this->params = $params;
    }

    public function formatPath($path, $rawUrlEncode ) {
        if (!is_string($path) || strlen($path) < 1)
            return '/';

        if (0 === strpos($path, "http"))
            $path = $rawUrlEncode ? rawurlencode($path) : urlencode($path);

        $path = ($path[0] === '/' ? '' : '/') . $path;
        return $path;
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

    public function deleteParameter($key) {
        unset($this->params[$key]);
    }

    public function getURL() {
        $queryPairs = array();

        if ($this->params) {
            ksort($this->params);

            foreach ($this->params as $key => $val) {
                if (substr($key, -2) == '64') {
                    $encodedVal = self::base64url_encode($val);
                } else {
                    $encodedVal = rawurlencode($val);
                }

                $queryPairs[] = rawurlencode($key) . "=" . $encodedVal;
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

    private static function base64url_encode($data) {
      return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function joinURL($parts) {
        $url = $parts['scheme'] . '://' . $parts['host'] . $parts['path'] . $parts['query'];

        return $url;
    }
}
