<?php
namespace Psy\Command\ListCommand;
use Symfony\Component\Console\Input\InputInterface;
class ConstantEnumerator extends Enumerator
{
    protected function listItems(InputInterface $input, \Reflector $reflector = null, $target = null)
    {
        if ($reflector !== null || $target !== null) {
            return;
        }
        if (!$input->getOption('constants')) {
            return;
        }
        $category  = $input->getOption('user') ? 'user' : $input->getOption('category');
        $label     = $category ? ucfirst($category) . ' Constants' : 'Constants';
        $constants = $this->prepareConstants($this->getConstants($category));
        if (empty($constants)) {
            return;
        }
        $ret = array();
        $ret[$label] = $constants;
        return $ret;
    }
    protected function getConstants($category = null)
    {
        if (!$category) {
            return get_defined_constants();
        }
        $consts = get_defined_constants(true);
        return isset($consts[$category]) ? $consts[$category] : array();
    }
    protected function prepareConstants(array $constants)
    {
        $ret = array();
        $names = array_keys($constants);
        natcasesort($names);
        foreach ($names as $name) {
            if ($this->showItem($name)) {
                $ret[$name] = array(
                    'name'  => $name,
                    'style' => self::IS_CONSTANT,
                    'value' => $this->presentRef($constants[$name]),
                );
            }
        }
        return $ret;
    }
}
