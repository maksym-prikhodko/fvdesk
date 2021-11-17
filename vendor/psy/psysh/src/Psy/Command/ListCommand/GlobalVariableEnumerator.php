<?php
namespace Psy\Command\ListCommand;
use Symfony\Component\Console\Input\InputInterface;
class GlobalVariableEnumerator extends Enumerator
{
    protected function listItems(InputInterface $input, \Reflector $reflector = null, $target = null)
    {
        if ($reflector !== null || $target !== null) {
            return;
        }
        if (!$input->getOption('globals')) {
            return;
        }
        $globals = $this->prepareGlobals($this->getGlobals());
        if (empty($globals)) {
            return;
        }
        return array(
            'Global Variables' => $globals,
        );
    }
    protected function getGlobals()
    {
        global $GLOBALS;
        $names = array_keys($GLOBALS);
        natcasesort($names);
        $ret = array();
        foreach ($names as $name) {
            $ret[$name] = $GLOBALS[$name];
        }
        return $ret;
    }
    protected function prepareGlobals($globals)
    {
        $ret = array();
        foreach ($globals as $name => $value) {
            if ($this->showItem($name)) {
                $fname = '$' . $name;
                $ret[$fname] = array(
                    'name'  => $fname,
                    'style' => self::IS_GLOBAL,
                    'value' => $this->presentRef($value),
                );
            }
        }
        return $ret;
    }
}
