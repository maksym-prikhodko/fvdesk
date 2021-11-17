<?php
namespace Symfony\Component\Routing\Matcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Route;
abstract class RedirectableUrlMatcher extends UrlMatcher implements RedirectableUrlMatcherInterface
{
    public function match($pathinfo)
    {
        try {
            $parameters = parent::match($pathinfo);
        } catch (ResourceNotFoundException $e) {
            if ('/' === substr($pathinfo, -1) || !in_array($this->context->getMethod(), array('HEAD', 'GET'))) {
                throw $e;
            }
            try {
                parent::match($pathinfo.'/');
                return $this->redirect($pathinfo.'/', null);
            } catch (ResourceNotFoundException $e2) {
                throw $e;
            }
        }
        return $parameters;
    }
    protected function handleRouteRequirements($pathinfo, $name, Route $route)
    {
        if ($route->getCondition() && !$this->getExpressionLanguage()->evaluate($route->getCondition(), array('context' => $this->context, 'request' => $this->request))) {
            return array(self::REQUIREMENT_MISMATCH, null);
        }
        $scheme = $this->context->getScheme();
        $schemes = $route->getSchemes();
        if ($schemes && !$route->hasScheme($scheme)) {
            return array(self::ROUTE_MATCH, $this->redirect($pathinfo, $name, current($schemes)));
        }
        return array(self::REQUIREMENT_MATCH, null);
    }
}
