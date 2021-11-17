<?php
namespace Symfony\Component\Routing\Matcher\Dumper;
use Symfony\Component\Routing\Route;
class ApacheMatcherDumper extends MatcherDumper
{
    public function dump(array $options = array())
    {
        $options = array_merge(array(
            'script_name' => 'app.php',
            'base_uri' => '',
        ), $options);
        $options['script_name'] = self::escape($options['script_name'], ' ', '\\');
        $rules = array("# skip \"real\" requests\nRewriteCond %{REQUEST_FILENAME} -f\nRewriteRule .* - [QSA,L]");
        $methodVars = array();
        $hostRegexUnique = 0;
        $prevHostRegex = '';
        foreach ($this->getRoutes()->all() as $name => $route) {
            if ($route->getCondition()) {
                throw new \LogicException(sprintf('Unable to dump the routes for Apache as route "%s" has a condition.', $name));
            }
            $compiledRoute = $route->compile();
            $hostRegex = $compiledRoute->getHostRegex();
            if (null !== $hostRegex && $prevHostRegex !== $hostRegex) {
                $prevHostRegex = $hostRegex;
                $hostRegexUnique++;
                $rule = array();
                $regex = $this->regexToApacheRegex($hostRegex);
                $regex = self::escape($regex, ' ', '\\');
                $rule[] = sprintf('RewriteCond %%{HTTP:Host} %s', $regex);
                $variables = array();
                $variables[] = sprintf('E=__ROUTING_host_%s:1', $hostRegexUnique);
                foreach ($compiledRoute->getHostVariables() as $i => $variable) {
                    $variables[] = sprintf('E=__ROUTING_host_%s_%s:%%%d', $hostRegexUnique, $variable, $i + 1);
                }
                $variables = implode(',', $variables);
                $rule[] = sprintf('RewriteRule .? - [%s]', $variables);
                $rules[] = implode("\n", $rule);
            }
            $rules[] = $this->dumpRoute($name, $route, $options, $hostRegexUnique);
            if ($req = $route->getRequirement('_method')) {
                $methods = explode('|', strtoupper($req));
                $methodVars = array_merge($methodVars, $methods);
            }
        }
        if (0 < count($methodVars)) {
            $rule = array('# 405 Method Not Allowed');
            $methodVars = array_values(array_unique($methodVars));
            if (in_array('GET', $methodVars) && !in_array('HEAD', $methodVars)) {
                $methodVars[] = 'HEAD';
            }
            foreach ($methodVars as $i => $methodVar) {
                $rule[] = sprintf('RewriteCond %%{ENV:_ROUTING__allow_%s} =1%s', $methodVar, isset($methodVars[$i + 1]) ? ' [OR]' : '');
            }
            $rule[] = sprintf('RewriteRule .* %s [QSA,L]', $options['script_name']);
            $rules[] = implode("\n", $rule);
        }
        return implode("\n\n", $rules)."\n";
    }
    private function dumpRoute($name, $route, array $options, $hostRegexUnique)
    {
        $compiledRoute = $route->compile();
        $regex = $this->regexToApacheRegex($compiledRoute->getRegex());
        $regex = '^'.self::escape(preg_quote($options['base_uri']).substr($regex, 1), ' ', '\\');
        $methods = $this->getRouteMethods($route);
        $hasTrailingSlash = (!$methods || in_array('HEAD', $methods)) && '/$' === substr($regex, -2) && '^/$' !== $regex;
        $variables = array('E=_ROUTING_route:'.$name);
        foreach ($compiledRoute->getHostVariables() as $variable) {
            $variables[] = sprintf('E=_ROUTING_param_%s:%%{ENV:__ROUTING_host_%s_%s}', $variable, $hostRegexUnique, $variable);
        }
        foreach ($compiledRoute->getPathVariables() as $i => $variable) {
            $variables[] = 'E=_ROUTING_param_'.$variable.':%'.($i + 1);
        }
        foreach ($this->normalizeValues($route->getDefaults()) as $key => $value) {
            $variables[] = 'E=_ROUTING_default_'.$key.':'.strtr($value, array(
                ':' => '\\:',
                '=' => '\\=',
                '\\' => '\\\\',
                ' ' => '\\ ',
            ));
        }
        $variables = implode(',', $variables);
        $rule = array("# $name");
        if (0 < count($methods)) {
            $allow = array();
            foreach ($methods as $method) {
                $allow[] = 'E=_ROUTING_allow_'.$method.':1';
            }
            if ($compiledRoute->getHostRegex()) {
                $rule[] = sprintf('RewriteCond %%{ENV:__ROUTING_host_%s} =1', $hostRegexUnique);
            }
            $rule[] = "RewriteCond %{REQUEST_URI} $regex";
            $rule[] = sprintf('RewriteCond %%{REQUEST_METHOD} !^(%s)$ [NC]', implode('|', $methods));
            $rule[] = sprintf('RewriteRule .* - [S=%d,%s]', $hasTrailingSlash ? 2 : 1, implode(',', $allow));
        }
        if ($hasTrailingSlash) {
            if ($compiledRoute->getHostRegex()) {
                $rule[] = sprintf('RewriteCond %%{ENV:__ROUTING_host_%s} =1', $hostRegexUnique);
            }
            $rule[] = 'RewriteCond %{REQUEST_URI} '.substr($regex, 0, -2).'$';
            $rule[] = 'RewriteRule .* $0/ [QSA,L,R=301]';
        }
        if ($compiledRoute->getHostRegex()) {
            $rule[] = sprintf('RewriteCond %%{ENV:__ROUTING_host_%s} =1', $hostRegexUnique);
        }
        $rule[] = "RewriteCond %{REQUEST_URI} $regex";
        $rule[] = "RewriteRule .* {$options['script_name']} [QSA,L,$variables]";
        return implode("\n", $rule);
    }
    private function getRouteMethods(Route $route)
    {
        $methods = array();
        if ($req = $route->getRequirement('_method')) {
            $methods = explode('|', strtoupper($req));
            if (in_array('GET', $methods) && !in_array('HEAD', $methods)) {
                $methods[] = 'HEAD';
            }
        }
        return $methods;
    }
    private function regexToApacheRegex($regex)
    {
        $regexPatternEnd = strrpos($regex, $regex[0]);
        return preg_replace('/\?P<.+?>/', '', substr($regex, 1, $regexPatternEnd - 1));
    }
    private static function escape($string, $char, $with)
    {
        $escaped = false;
        $output = '';
        foreach (str_split($string) as $symbol) {
            if ($escaped) {
                $output .= $symbol;
                $escaped = false;
                continue;
            }
            if ($symbol === $char) {
                $output .= $with.$char;
                continue;
            }
            if ($symbol === $with) {
                $escaped = true;
            }
            $output .= $symbol;
        }
        return $output;
    }
    private function normalizeValues(array $values)
    {
        $normalizedValues = array();
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $index => $bit) {
                    $normalizedValues[sprintf('%s[%s]', $key, $index)] = $bit;
                }
            } else {
                $normalizedValues[$key] = (string) $value;
            }
        }
        return $normalizedValues;
    }
}
