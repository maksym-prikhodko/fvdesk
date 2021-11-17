<?php
namespace Psy\Presenter;
class ObjectPresenter extends RecursivePresenter
{
    const FMT = '<object>\\<<class>%s</class> <strong>#%s</strong>></object>';
    public function canPresent($value)
    {
        return is_object($value);
    }
    public function presentRef($value)
    {
        return sprintf(self::FMT, get_class($value), spl_object_hash($value));
    }
    protected function presentValue($value, $depth = null, $options = 0)
    {
        if ($depth === 0) {
            return $this->presentRef($value);
        }
        $class = new \ReflectionObject($value);
        $propertyFilter = \ReflectionProperty::IS_PUBLIC;
        if ($options & Presenter::VERBOSE) {
            $propertyFilter |= \ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_PROTECTED;
        }
        $props = $this->getProperties($value, $class, $propertyFilter);
        return sprintf('%s %s', $this->presentRef($value), $this->formatProperties($props));
    }
    protected function formatProperties($props)
    {
        if (empty($props)) {
            return '{}';
        }
        $formatted = array();
        foreach ($props as $name => $value) {
            $formatted[] = sprintf('%s: %s', $name, $this->indentValue($this->presentSubValue($value)));
        }
        $template = sprintf('{%s%s%%s%s}', PHP_EOL, self::INDENT, PHP_EOL);
        $glue     = sprintf(',%s%s', PHP_EOL, self::INDENT);
        return sprintf($template, implode($glue, $formatted));
    }
    protected function getProperties($value, \ReflectionClass $class, $propertyFilter)
    {
        $deprecated = false;
        set_error_handler(function ($errno, $errstr) use (&$deprecated) {
            if (in_array($errno, array(E_DEPRECATED, E_USER_DEPRECATED))) {
                $deprecated = true;
            } else {
                return false;
            }
        });
        $props = array();
        foreach ($class->getProperties($propertyFilter) as $prop) {
            $deprecated = false;
            $prop->setAccessible(true);
            $val = $prop->getValue($value);
            if (!$deprecated) {
                $props[$this->propertyKey($prop)] = $val;
            }
        }
        restore_error_handler();
        return $props;
    }
    protected function propertyKey(\ReflectionProperty $prop)
    {
        $key = $prop->getName();
        if ($prop->isProtected()) {
            return sprintf('<protected>%s</protected>', $key);
        } elseif ($prop->isPrivate()) {
            return sprintf('<private>%s</private>', $key);
        }
        return $key;
    }
}
