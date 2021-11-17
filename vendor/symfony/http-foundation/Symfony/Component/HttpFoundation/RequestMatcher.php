<?php
namespace Symfony\Component\HttpFoundation;
class RequestMatcher implements RequestMatcherInterface
{
    private $path;
    private $host;
    private $methods = array();
    private $ips = array();
    private $attributes = array();
    private $schemes = array();
    public function __construct($path = null, $host = null, $methods = null, $ips = null, array $attributes = array(), $schemes = null)
    {
        $this->matchPath($path);
        $this->matchHost($host);
        $this->matchMethod($methods);
        $this->matchIps($ips);
        $this->matchScheme($schemes);
        foreach ($attributes as $k => $v) {
            $this->matchAttribute($k, $v);
        }
    }
    public function matchScheme($scheme)
    {
        $this->schemes = array_map('strtolower', (array) $scheme);
    }
    public function matchHost($regexp)
    {
        $this->host = $regexp;
    }
    public function matchPath($regexp)
    {
        $this->path = $regexp;
    }
    public function matchIp($ip)
    {
        $this->matchIps($ip);
    }
    public function matchIps($ips)
    {
        $this->ips = (array) $ips;
    }
    public function matchMethod($method)
    {
        $this->methods = array_map('strtoupper', (array) $method);
    }
    public function matchAttribute($key, $regexp)
    {
        $this->attributes[$key] = $regexp;
    }
    public function matches(Request $request)
    {
        if ($this->schemes && !in_array($request->getScheme(), $this->schemes)) {
            return false;
        }
        if ($this->methods && !in_array($request->getMethod(), $this->methods)) {
            return false;
        }
        foreach ($this->attributes as $key => $pattern) {
            if (!preg_match('{'.$pattern.'}', $request->attributes->get($key))) {
                return false;
            }
        }
        if (null !== $this->path && !preg_match('{'.$this->path.'}', rawurldecode($request->getPathInfo()))) {
            return false;
        }
        if (null !== $this->host && !preg_match('{'.$this->host.'}i', $request->getHost())) {
            return false;
        }
        if (IpUtils::checkIp($request->getClientIp(), $this->ips)) {
            return true;
        }
        return count($this->ips) === 0;
    }
}
