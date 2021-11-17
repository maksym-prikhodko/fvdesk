<?php
namespace Symfony\Component\Routing\Matcher\Dumper;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
class PhpMatcherDumper extends MatcherDumper
{
    private $expressionLanguage;
    private $expressionLanguageProviders = array();
    public function dump(array $options = array())
    {
        $options = array_replace(array(
            'class' => 'ProjectUrlMatcher',
            'base_class' => 'Symfony\\Component\\Routing\\Matcher\\UrlMatcher',
        ), $options);
        $interfaces = class_implements($options['base_class']);
        $supportsRedirections = isset($interfaces['Symfony\\Component\\Routing\\Matcher\\RedirectableUrlMatcherInterface']);
        return <<<EOF
<?php
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;
class {$options['class']} extends {$options['base_class']}
{
    public function __construct(RequestContext \$context)
    {
        \$this->context = \$context;
    }
{$this->generateMatchMethod($supportsRedirections)}
}
EOF;
    }
    public function addExpressionLanguageProvider(ExpressionFunctionProviderInterface $provider)
    {
        $this->expressionLanguageProviders[] = $provider;
    }
    private function generateMatchMethod($supportsRedirections)
    {
        $code = rtrim($this->compileRoutes($this->getRoutes(), $supportsRedirections), "\n");
        return <<<EOF
    public function match(\$pathinfo)
    {
        \$allow = array();
        \$pathinfo = rawurldecode(\$pathinfo);
        \$context = \$this->context;
        \$request = \$this->request;
$code
        throw 0 < count(\$allow) ? new MethodNotAllowedException(array_unique(\$allow)) : new ResourceNotFoundException();
    }
EOF;
    }
    private function compileRoutes(RouteCollection $routes, $supportsRedirections)
    {
        $fetchedHost = false;
        $groups = $this->groupRoutesByHostRegex($routes);
        $code = '';
        foreach ($groups as $collection) {
            if (null !== $regex = $collection->getAttribute('host_regex')) {
                if (!$fetchedHost) {
                    $code .= "        \$host = \$this->context->getHost();\n\n";
                    $fetchedHost = true;
                }
                $code .= sprintf("        if (preg_match(%s, \$host, \$hostMatches)) {\n", var_export($regex, true));
            }
            $tree = $this->buildPrefixTree($collection);
            $groupCode = $this->compilePrefixRoutes($tree, $supportsRedirections);
            if (null !== $regex) {
                $groupCode = preg_replace('/^.{2,}$/m', '    $0', $groupCode);
                $code .= $groupCode;
                $code .= "        }\n\n";
            } else {
                $code .= $groupCode;
            }
        }
        return $code;
    }
    private function compilePrefixRoutes(DumperPrefixCollection $collection, $supportsRedirections, $parentPrefix = '')
    {
        $code = '';
        $prefix = $collection->getPrefix();
        $optimizable = 1 < strlen($prefix) && 1 < count($collection->all());
        $optimizedPrefix = $parentPrefix;
        if ($optimizable) {
            $optimizedPrefix = $prefix;
            $code .= sprintf("    if (0 === strpos(\$pathinfo, %s)) {\n", var_export($prefix, true));
        }
        foreach ($collection as $route) {
            if ($route instanceof DumperCollection) {
                $code .= $this->compilePrefixRoutes($route, $supportsRedirections, $optimizedPrefix);
            } else {
                $code .= $this->compileRoute($route->getRoute(), $route->getName(), $supportsRedirections, $optimizedPrefix)."\n";
            }
        }
        if ($optimizable) {
            $code .= "    }\n\n";
            $code = preg_replace('/^.{2,}$/m', '    $0', $code);
        }
        return $code;
    }
    private function compileRoute(Route $route, $name, $supportsRedirections, $parentPrefix = null)
    {
        $code = '';
        $compiledRoute = $route->compile();
        $conditions = array();
        $hasTrailingSlash = false;
        $matches = false;
        $hostMatches = false;
        $methods = array();
        if ($req = $route->getRequirement('_method')) {
            $methods = explode('|', strtoupper($req));
            if (in_array('GET', $methods) && !in_array('HEAD', $methods)) {
                $methods[] = 'HEAD';
            }
        }
        $supportsTrailingSlash = $supportsRedirections && (!$methods || in_array('HEAD', $methods));
        if (!count($compiledRoute->getPathVariables()) && false !== preg_match('#^(.)\^(?P<url>.*?)\$\1#', $compiledRoute->getRegex(), $m)) {
            if ($supportsTrailingSlash && substr($m['url'], -1) === '/') {
                $conditions[] = sprintf("rtrim(\$pathinfo, '/') === %s", var_export(rtrim(str_replace('\\', '', $m['url']), '/'), true));
                $hasTrailingSlash = true;
            } else {
                $conditions[] = sprintf("\$pathinfo === %s", var_export(str_replace('\\', '', $m['url']), true));
            }
        } else {
            if ($compiledRoute->getStaticPrefix() && $compiledRoute->getStaticPrefix() !== $parentPrefix) {
                $conditions[] = sprintf("0 === strpos(\$pathinfo, %s)", var_export($compiledRoute->getStaticPrefix(), true));
            }
            $regex = $compiledRoute->getRegex();
            if ($supportsTrailingSlash && $pos = strpos($regex, '/$')) {
                $regex = substr($regex, 0, $pos).'/?$'.substr($regex, $pos + 2);
                $hasTrailingSlash = true;
            }
            $conditions[] = sprintf("preg_match(%s, \$pathinfo, \$matches)", var_export($regex, true));
            $matches = true;
        }
        if ($compiledRoute->getHostVariables()) {
            $hostMatches = true;
        }
        if ($route->getCondition()) {
            $conditions[] = $this->getExpressionLanguage()->compile($route->getCondition(), array('context', 'request'));
        }
        $conditions = implode(' && ', $conditions);
        $code .= <<<EOF
        if ($conditions) {
EOF;
        $gotoname = 'not_'.preg_replace('/[^A-Za-z0-9_]/', '', $name);
        if ($methods) {
            if (1 === count($methods)) {
                $code .= <<<EOF
            if (\$this->context->getMethod() != '$methods[0]') {
                \$allow[] = '$methods[0]';
                goto $gotoname;
            }
EOF;
            } else {
                $methods = implode("', '", $methods);
                $code .= <<<EOF
            if (!in_array(\$this->context->getMethod(), array('$methods'))) {
                \$allow = array_merge(\$allow, array('$methods'));
                goto $gotoname;
            }
EOF;
            }
        }
        if ($hasTrailingSlash) {
            $code .= <<<EOF
            if (substr(\$pathinfo, -1) !== '/') {
                return \$this->redirect(\$pathinfo.'/', '$name');
            }
EOF;
        }
        if ($schemes = $route->getSchemes()) {
            if (!$supportsRedirections) {
                throw new \LogicException('The "schemes" requirement is only supported for URL matchers that implement RedirectableUrlMatcherInterface.');
            }
            $schemes = str_replace("\n", '', var_export(array_flip($schemes), true));
            $code .= <<<EOF
            \$requiredSchemes = $schemes;
            if (!isset(\$requiredSchemes[\$this->context->getScheme()])) {
                return \$this->redirect(\$pathinfo, '$name', key(\$requiredSchemes));
            }
EOF;
        }
        if ($matches || $hostMatches) {
            $vars = array();
            if ($hostMatches) {
                $vars[] = '$hostMatches';
            }
            if ($matches) {
                $vars[] = '$matches';
            }
            $vars[] = "array('_route' => '$name')";
            $code .= sprintf(
                "            return \$this->mergeDefaults(array_replace(%s), %s);\n",
                implode(', ', $vars),
                str_replace("\n", '', var_export($route->getDefaults(), true))
            );
        } elseif ($route->getDefaults()) {
            $code .= sprintf("            return %s;\n", str_replace("\n", '', var_export(array_replace($route->getDefaults(), array('_route' => $name)), true)));
        } else {
            $code .= sprintf("            return array('_route' => '%s');\n", $name);
        }
        $code .= "        }\n";
        if ($methods) {
            $code .= "        $gotoname:\n";
        }
        return $code;
    }
    private function groupRoutesByHostRegex(RouteCollection $routes)
    {
        $groups = new DumperCollection();
        $currentGroup = new DumperCollection();
        $currentGroup->setAttribute('host_regex', null);
        $groups->add($currentGroup);
        foreach ($routes as $name => $route) {
            $hostRegex = $route->compile()->getHostRegex();
            if ($currentGroup->getAttribute('host_regex') !== $hostRegex) {
                $currentGroup = new DumperCollection();
                $currentGroup->setAttribute('host_regex', $hostRegex);
                $groups->add($currentGroup);
            }
            $currentGroup->add(new DumperRoute($name, $route));
        }
        return $groups;
    }
    private function buildPrefixTree(DumperCollection $collection)
    {
        $tree = new DumperPrefixCollection();
        $current = $tree;
        foreach ($collection as $route) {
            $current = $current->addPrefixRoute($route);
        }
        $tree->mergeSlashNodes();
        return $tree;
    }
    private function getExpressionLanguage()
    {
        if (null === $this->expressionLanguage) {
            if (!class_exists('Symfony\Component\ExpressionLanguage\ExpressionLanguage')) {
                throw new \RuntimeException('Unable to use expressions as the Symfony ExpressionLanguage component is not installed.');
            }
            $this->expressionLanguage = new ExpressionLanguage(null, $this->expressionLanguageProviders);
        }
        return $this->expressionLanguage;
    }
}
