<?php
namespace Psy\Command\ListCommand;
use Symfony\Component\Console\Input\InputInterface;
class InterfaceEnumerator extends Enumerator
{
    protected function listItems(InputInterface $input, \Reflector $reflector = null, $target = null)
    {
        if ($reflector !== null || $target !== null) {
            return;
        }
        if (!$input->getOption('interfaces')) {
            return;
        }
        $interfaces = $this->prepareInterfaces(get_declared_interfaces());
        if (empty($interfaces)) {
            return;
        }
        return array(
            'Interfaces' => $interfaces,
        );
    }
    protected function prepareInterfaces(array $interfaces)
    {
        natcasesort($interfaces);
        $ret = array();
        foreach ($interfaces as $name) {
            if ($this->showItem($name)) {
                $ret[$name] = array(
                    'name'  => $name,
                    'style' => self::IS_CLASS,
                    'value' => $this->presentSignature($name),
                );
            }
        }
        return $ret;
    }
}
