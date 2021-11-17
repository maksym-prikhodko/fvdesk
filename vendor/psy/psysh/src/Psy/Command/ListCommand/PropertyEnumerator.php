<?php
namespace Psy\Command\ListCommand;
use Symfony\Component\Console\Input\InputInterface;
class PropertyEnumerator extends Enumerator
{
    protected function listItems(InputInterface $input, \Reflector $reflector = null, $target = null)
    {
        if ($reflector === null) {
            return;
        }
        if (!$reflector instanceof \ReflectionClass) {
            return;
        }
        if (!$input->getOption('properties')) {
            return;
        }
        $showAll    = $input->getOption('all');
        $properties = $this->prepareProperties($this->getProperties($showAll, $reflector), $target);
        if (empty($properties)) {
            return;
        }
        $ret = array();
        $ret[$this->getKindLabel($reflector)] = $properties;
        return $ret;
    }
    protected function getProperties($showAll, \Reflector $reflector)
    {
        $properties = array();
        foreach ($reflector->getProperties() as $property) {
            if ($showAll || $property->isPublic()) {
                $properties[$property->getName()] = $property;
            }
        }
        ksort($properties);
        return $properties;
    }
    protected function prepareProperties(array $properties, $target = null)
    {
        $ret = array();
        foreach ($properties as $name => $property) {
            if ($this->showItem($name)) {
                $fname = '$' . $name;
                $ret[$fname] = array(
                    'name'  => $fname,
                    'style' => $this->getVisibilityStyle($property),
                    'value' => $this->presentValue($property, $target),
                );
            }
        }
        return $ret;
    }
    protected function getKindLabel(\ReflectionClass $reflector)
    {
        if ($reflector->isInterface()) {
            return 'Interface Properties';
        } elseif (method_exists($reflector, 'isTrait') && $reflector->isTrait()) {
            return 'Trait Properties';
        } else {
            return 'Class Properties';
        }
    }
    private function getVisibilityStyle(\ReflectionProperty $property)
    {
        if ($property->isPublic()) {
            return self::IS_PUBLIC;
        } elseif ($property->isProtected()) {
            return self::IS_PROTECTED;
        } else {
            return self::IS_PRIVATE;
        }
    }
    protected function presentValue(\ReflectionProperty $property, $target)
    {
        if (!is_object($target)) {
            return '';
        }
        $property->setAccessible(true);
        $value = $property->getValue($target);
        return $this->presentRef($value);
    }
}
