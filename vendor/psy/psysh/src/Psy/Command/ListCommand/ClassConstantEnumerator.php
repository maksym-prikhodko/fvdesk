<?php
namespace Psy\Command\ListCommand;
use Psy\Reflection\ReflectionConstant;
use Symfony\Component\Console\Input\InputInterface;
class ClassConstantEnumerator extends Enumerator
{
    protected function listItems(InputInterface $input, \Reflector $reflector = null, $target = null)
    {
        if ($reflector === null) {
            return;
        }
        if (!$reflector instanceof \ReflectionClass) {
            return;
        }
        if (!$input->getOption('constants')) {
            return;
        }
        $constants = $this->prepareConstants($this->getConstants($reflector));
        if (empty($constants)) {
            return;
        }
        $ret = array();
        $ret[$this->getKindLabel($reflector)] = $constants;
        return $ret;
    }
    protected function getConstants(\Reflector $reflector)
    {
        $constants = array();
        foreach ($reflector->getConstants() as $name => $constant) {
            $constants[$name] = new ReflectionConstant($reflector, $name);
        }
        ksort($constants);
        return $constants;
    }
    protected function prepareConstants(array $constants)
    {
        $ret = array();
        foreach ($constants as $name => $constant) {
            if ($this->showItem($name)) {
                $ret[$name] = array(
                    'name'  => $name,
                    'style' => self::IS_CONSTANT,
                    'value' => $this->presentRef($constant->getValue()),
                );
            }
        }
        return $ret;
    }
    protected function getKindLabel(\ReflectionClass $reflector)
    {
        if ($reflector->isInterface()) {
            return 'Interface Constants';
        } elseif (method_exists($reflector, 'isTrait') && $reflector->isTrait()) {
            return 'Trait Constants';
        } else {
            return 'Class Constants';
        }
    }
}
