<?php
namespace Symfony\Component\Routing\Matcher;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
class ApacheUrlMatcher extends UrlMatcher
{
    public function match($pathinfo)
    {
        $parameters = array();
        $defaults = array();
        $allow = array();
        $route = null;
        foreach ($this->denormalizeValues($_SERVER) as $key => $value) {
            $name = $key;
            if (false === strpos($name, '_ROUTING_')) {
                continue;
            }
            while (0 === strpos($name, 'REDIRECT_')) {
                $name = substr($name, 9);
            }
            if (0 !== strpos($name, '_ROUTING_')) {
                continue;
            }
            if (false !== $pos = strpos($name, '_', 9)) {
                $type = substr($name, 9, $pos - 9);
                $name = substr($name, $pos + 1);
            } else {
                $type = substr($name, 9);
            }
            if ('param' === $type) {
                if ('' !== $value) {
                    $parameters[$name] = $value;
                }
            } elseif ('default' === $type) {
                $defaults[$name] = $value;
            } elseif ('route' === $type) {
                $route = $value;
            } elseif ('allow' === $type) {
                $allow[] = $name;
            }
            unset($_SERVER[$key]);
        }
        if (null !== $route) {
            $parameters['_route'] = $route;
            return $this->mergeDefaults($parameters, $defaults);
        } elseif (0 < count($allow)) {
            throw new MethodNotAllowedException($allow);
        } else {
            return parent::match($pathinfo);
        }
    }
    private function denormalizeValues(array $values)
    {
        $normalizedValues = array();
        foreach ($values as $key => $value) {
            if (preg_match('~^(.*)\[(\d+)\]$~', $key, $matches)) {
                if (!isset($normalizedValues[$matches[1]])) {
                    $normalizedValues[$matches[1]] = array();
                }
                $normalizedValues[$matches[1]][(int) $matches[2]] = $value;
            } else {
                $normalizedValues[$key] = $value;
            }
        }
        return $normalizedValues;
    }
}
