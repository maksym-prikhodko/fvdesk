<?php
namespace Psy\Presenter;
use PhpParser\Node;
class PHPParserPresenter extends ObjectPresenter
{
    const FMT = '<object>\\<<class>%s</class>></object>';
    public function canPresent($value)
    {
        return $value instanceof Node;
    }
    public function presentRef($value)
    {
        return sprintf(self::FMT, get_class($value));
    }
    protected function getProperties($value, \ReflectionClass $class, $propertyFilter)
    {
        $props = array();
        $props['type']       = $value->getType();
        $props['attributes'] = $value->getAttributes();
        foreach ($value->getSubNodeNames() as $name) {
            $props[$name] = $value->$name;
        }
        return $props;
    }
}
