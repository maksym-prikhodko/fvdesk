<?php
namespace Symfony\Component\Routing\Loader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Util\XmlUtils;
class XmlFileLoader extends FileLoader
{
    const NAMESPACE_URI = 'http:
    const SCHEME_PATH = '/schema/routing/routing-1.0.xsd';
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);
        $xml = $this->loadFile($path);
        $collection = new RouteCollection();
        $collection->addResource(new FileResource($path));
        foreach ($xml->documentElement->childNodes as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }
            $this->parseNode($collection, $node, $path, $file);
        }
        return $collection;
    }
    protected function parseNode(RouteCollection $collection, \DOMElement $node, $path, $file)
    {
        if (self::NAMESPACE_URI !== $node->namespaceURI) {
            return;
        }
        switch ($node->localName) {
            case 'route':
                $this->parseRoute($collection, $node, $path);
                break;
            case 'import':
                $this->parseImport($collection, $node, $path, $file);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown tag "%s" used in file "%s". Expected "route" or "import".', $node->localName, $path));
        }
    }
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'xml' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'xml' === $type);
    }
    protected function parseRoute(RouteCollection $collection, \DOMElement $node, $path)
    {
        if ('' === ($id = $node->getAttribute('id')) || (!$node->hasAttribute('pattern') && !$node->hasAttribute('path'))) {
            throw new \InvalidArgumentException(sprintf('The <route> element in file "%s" must have an "id" and a "path" attribute.', $path));
        }
        if ($node->hasAttribute('pattern')) {
            if ($node->hasAttribute('path')) {
                throw new \InvalidArgumentException(sprintf('The <route> element in file "%s" cannot define both a "path" and a "pattern" attribute. Use only "path".', $path));
            }
            $node->setAttribute('path', $node->getAttribute('pattern'));
            $node->removeAttribute('pattern');
        }
        $schemes = preg_split('/[\s,\|]++/', $node->getAttribute('schemes'), -1, PREG_SPLIT_NO_EMPTY);
        $methods = preg_split('/[\s,\|]++/', $node->getAttribute('methods'), -1, PREG_SPLIT_NO_EMPTY);
        list($defaults, $requirements, $options, $condition) = $this->parseConfigs($node, $path);
        $route = new Route($node->getAttribute('path'), $defaults, $requirements, $options, $node->getAttribute('host'), $schemes, $methods, $condition);
        $collection->add($id, $route);
    }
    protected function parseImport(RouteCollection $collection, \DOMElement $node, $path, $file)
    {
        if ('' === $resource = $node->getAttribute('resource')) {
            throw new \InvalidArgumentException(sprintf('The <import> element in file "%s" must have a "resource" attribute.', $path));
        }
        $type = $node->getAttribute('type');
        $prefix = $node->getAttribute('prefix');
        $host = $node->hasAttribute('host') ? $node->getAttribute('host') : null;
        $schemes = $node->hasAttribute('schemes') ? preg_split('/[\s,\|]++/', $node->getAttribute('schemes'), -1, PREG_SPLIT_NO_EMPTY) : null;
        $methods = $node->hasAttribute('methods') ? preg_split('/[\s,\|]++/', $node->getAttribute('methods'), -1, PREG_SPLIT_NO_EMPTY) : null;
        list($defaults, $requirements, $options, $condition) = $this->parseConfigs($node, $path);
        $this->setCurrentDir(dirname($path));
        $subCollection = $this->import($resource, ('' !== $type ? $type : null), false, $file);
        $subCollection->addPrefix($prefix);
        if (null !== $host) {
            $subCollection->setHost($host);
        }
        if (null !== $condition) {
            $subCollection->setCondition($condition);
        }
        if (null !== $schemes) {
            $subCollection->setSchemes($schemes);
        }
        if (null !== $methods) {
            $subCollection->setMethods($methods);
        }
        $subCollection->addDefaults($defaults);
        $subCollection->addRequirements($requirements);
        $subCollection->addOptions($options);
        $collection->addCollection($subCollection);
    }
    protected function loadFile($file)
    {
        return XmlUtils::loadFile($file, __DIR__.static::SCHEME_PATH);
    }
    private function parseConfigs(\DOMElement $node, $path)
    {
        $defaults = array();
        $requirements = array();
        $options = array();
        $condition = null;
        foreach ($node->getElementsByTagNameNS(self::NAMESPACE_URI, '*') as $n) {
            switch ($n->localName) {
                case 'default':
                    if ($this->isElementValueNull($n)) {
                        $defaults[$n->getAttribute('key')] = null;
                    } else {
                        $defaults[$n->getAttribute('key')] = trim($n->textContent);
                    }
                    break;
                case 'requirement':
                    $requirements[$n->getAttribute('key')] = trim($n->textContent);
                    break;
                case 'option':
                    $options[$n->getAttribute('key')] = trim($n->textContent);
                    break;
                case 'condition':
                    $condition = trim($n->textContent);
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Unknown tag "%s" used in file "%s". Expected "default", "requirement" or "option".', $n->localName, $path));
            }
        }
        return array($defaults, $requirements, $options, $condition);
    }
    private function isElementValueNull(\DOMElement $element)
    {
        $namespaceUri = 'http:
        if (!$element->hasAttributeNS($namespaceUri, 'nil')) {
            return false;
        }
        return 'true' === $element->getAttributeNS($namespaceUri, 'nil') || '1' === $element->getAttributeNS($namespaceUri, 'nil');
    }
}
