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
        $this->path = substr($path, 0, 1) !== "/" ? ("/" . $path) : $path;
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
        ksort($this->params);
        $queryPairs = array();
        foreach ($this->params as $k => $v) {
            $queryPairs[] = $k . "=" . self::encodeURIComponent($v);
        }

        $query = join("&", $queryPairs);

        if ($this->signKey) {
            $delim = $query === "" ? "" : "?";
            $toSign = $this->signKey . $this->path . $delim . $query;
            $sig = md5($toSign);
            if ($query) {
                $query .= "&s=" . $sig;
            } else {
                $query = "?s=" . $sig;
            }
        }

        $url_parts = array('scheme' => $this->scheme, 'host' => $this->domain, 'path' => $this->path, 'query' => $query);

        return self::join_url($url_parts);
    }

    public static function encodeURIComponent($str) {
        $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
        return strtr(rawurlencode($str), $revert);
    }

    public static function join_url($parts, $encode=true) {
		$url = '';
		if (!empty($parts['scheme'])) {
			$url .= $parts['scheme'] . ':';
        }
		if (isset($parts['host'])) {
			$url .= '//';
			if (isset($parts['user'])) {
				$url .= $parts['user'];
				if (isset($parts['pass']))
					$url .= ':' . $parts['pass'];
				$url .= '@';
			}
			if (preg_match('!^[\da-f]*:[\da-f.:]+$!ui', $parts['host'])) {
				$url .= '[' . $parts['host'] . ']'; // IPv6
			} else {
				$url .= $parts['host'];				// IPv4 or name
            }
			if (isset($parts['port'])) {
				$url .= ':' . $parts['port'];
            }
			if (!empty($parts['path']) && $parts['path'][0] != '/') {
				$url .= '/';
            }
		}
		if (!empty($parts['path'])) {
			$url .= $parts['path'];
        }
		if (isset($parts['query']) && strlen($parts['query']) > 0) {
			$url .= '?' . $parts['query'];
        }
		if (isset($parts['fragment'])) {
			$url .= '#' . $parts['fragment'];
        }

        return $url;
	}

}