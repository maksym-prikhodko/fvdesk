<?php
namespace Psy\Command\ListCommand;
use Symfony\Component\Console\Input\InputInterface;
class ClassEnumerator extends Enumerator
{
    protected function listItems(InputInterface $input, \Reflector $reflector = null, $target = null)
    {
        if ($reflector !== null || $target !== null) {
            return;
        }
        if (!$input->getOption('classes')) {
            return;
        }
        $classes = $this->prepareClasses(get_declared_classes());
        if (empty($classes)) {
            return;
        }
        return array(
            'Classes' => $classes,
        );
    }
    protected function prepareClasses(array $classes)
    {
        natcasesort($classes);
        $ret = array();
        foreach ($classes as $name) {
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
