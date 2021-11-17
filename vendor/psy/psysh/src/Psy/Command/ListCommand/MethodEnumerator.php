<?php
namespace Psy\Command\ListCommand;
use Symfony\Component\Console\Input\InputInterface;
class MethodEnumerator extends Enumerator
{
    protected function listItems(InputInterface $input, \Reflector $reflector = null, $target = null)
    {
        if ($reflector === null) {
            return;
        }
        if (!$reflector instanceof \ReflectionClass) {
            return;
        }
        if (!$input->getOption('methods')) {
            return;
        }
        $showAll = $input->getOption('all');
        $methods = $this->prepareMethods($this->getMethods($showAll, $reflector));
        if (empty($methods)) {
            return;
        }
        $ret = array();
        $ret[$this->getKindLabel($reflector)] = $methods;
        return $ret;
    }
    protected function getMethods($showAll, \Reflector $reflector)
    {
        $methods = array();
        foreach ($reflector->getMethods() as $name => $method) {
            if ($showAll || $method->isPublic()) {
                $methods[$method->getName()] = $method;
            }
        }
        ksort($methods);
        return $methods;
    }
    protected function prepareMethods(array $methods)
    {
        $ret = array();
        foreach ($methods as $name => $method) {
            if ($this->showItem($name)) {
                $ret[$name] = array(
                    'name'  => $name,
                    'style' => $this->getVisibilityStyle($method),
                    'value' => $this->presentSignature($method),
                );
            }
        }
        return $ret;
    }
    protected function getKindLabel(\ReflectionClass $reflector)
    {
        if ($reflector->isInterface()) {
            return 'Interface Methods';
        } elseif (method_exists($reflector, 'isTrait') && $reflector->isTrait()) {
            return 'Trait Methods';
        } else {
            return 'Class Methods';
        }
    }
    private function getVisibilityStyle(\ReflectionMethod $method)
    {
        if ($method->isPublic()) {
            return self::IS_PUBLIC;
        } elseif ($method->isProtected()) {
            return self::IS_PROTECTED;
        } else {
            return self::IS_PRIVATE;
        }
    }
}
