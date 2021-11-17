<?php
namespace Psy\Command\ListCommand;
use Symfony\Component\Console\Input\InputInterface;
class TraitEnumerator extends Enumerator
{
    protected function listItems(InputInterface $input, \Reflector $reflector = null, $target = null)
    {
        if (!function_exists('trait_exists')) {
            return;
        }
        if ($reflector !== null || $target !== null) {
            return;
        }
        if (!$input->getOption('traits')) {
            return;
        }
        $traits = $this->prepareTraits(get_declared_traits());
        if (empty($traits)) {
            return;
        }
        return array(
            'Traits' => $traits,
        );
    }
    protected function prepareTraits(array $traits)
    {
        natcasesort($traits);
        $ret = array();
        foreach ($traits as $name) {
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
