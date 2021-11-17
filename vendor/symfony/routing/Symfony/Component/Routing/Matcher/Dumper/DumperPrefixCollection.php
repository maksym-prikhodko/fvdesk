<?php
namespace Symfony\Component\Routing\Matcher\Dumper;
class DumperPrefixCollection extends DumperCollection
{
    private $prefix = '';
    public function getPrefix()
    {
        return $this->prefix;
    }
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }
    public function addPrefixRoute(DumperRoute $route)
    {
        $prefix = $route->getRoute()->compile()->getStaticPrefix();
        for ($collection = $this; null !== $collection; $collection = $collection->getParent()) {
            if ($collection->prefix === $prefix) {
                $collection->add($route);
                return $collection;
            }
            if ('' === $collection->prefix || 0 === strpos($prefix, $collection->prefix)) {
                $child = new DumperPrefixCollection();
                $child->setPrefix(substr($prefix, 0, strlen($collection->prefix) + 1));
                $collection->add($child);
                return $child->addPrefixRoute($route);
            }
        }
        throw new \LogicException('The collection root must not have a prefix');
    }
    public function mergeSlashNodes()
    {
        $children = array();
        foreach ($this as $child) {
            if ($child instanceof self) {
                $child->mergeSlashNodes();
                if ('/' === substr($child->prefix, -1)) {
                    $children = array_merge($children, $child->all());
                } else {
                    $children[] = $child;
                }
            } else {
                $children[] = $child;
            }
        }
        $this->setAll($children);
    }
}
