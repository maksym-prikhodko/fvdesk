<?php
namespace Symfony\Component\HttpKernel;
class UriSigner
{
    private $secret;
    public function __construct($secret)
    {
        $this->secret = $secret;
    }
    public function sign($uri)
    {
        $url = parse_url($uri);
        if (isset($url['query'])) {
            parse_str($url['query'], $params);
        } else {
            $params = array();
        }
        $uri = $this->buildUrl($url, $params);
        return $uri.(false === (strpos($uri, '?')) ? '?' : '&').'_hash='.$this->computeHash($uri);
    }
    public function check($uri)
    {
        $url = parse_url($uri);
        if (isset($url['query'])) {
            parse_str($url['query'], $params);
        } else {
            $params = array();
        }
        if (empty($params['_hash'])) {
            return false;
        }
        $hash = urlencode($params['_hash']);
        unset($params['_hash']);
        return $this->computeHash($this->buildUrl($url, $params)) === $hash;
    }
    private function computeHash($uri)
    {
        return urlencode(base64_encode(hash_hmac('sha256', $uri, $this->secret, true)));
    }
    private function buildUrl(array $url, array $params = array())
    {
        ksort($params);
        $url['query'] = http_build_query($params, '', '&');
        $scheme   = isset($url['scheme']) ? $url['scheme'].':
        $host     = isset($url['host']) ? $url['host'] : '';
        $port     = isset($url['port']) ? ':'.$url['port'] : '';
        $user     = isset($url['user']) ? $url['user'] : '';
        $pass     = isset($url['pass']) ? ':'.$url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($url['path']) ? $url['path'] : '';
        $query    = isset($url['query']) && $url['query'] ? '?'.$url['query'] : '';
        $fragment = isset($url['fragment']) ? '#'.$url['fragment'] : '';
        return $scheme.$user.$pass.$host.$port.$path.$query.$fragment;
    }
}
