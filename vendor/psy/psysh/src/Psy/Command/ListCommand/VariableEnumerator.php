<?php
namespace Psy\Command\ListCommand;
use Psy\Context;
use Psy\Presenter\PresenterManager;
use Symfony\Component\Console\Input\InputInterface;
class VariableEnumerator extends Enumerator
{
    private static $specialVars = array('_', '_e');
    private $context;
    public function __construct(PresenterManager $presenterManager, Context $context)
    {
        $this->context = $context;
        parent::__construct($presenterManager);
    }
    protected function listItems(InputInterface $input, \Reflector $reflector = null, $target = null)
    {
        if ($reflector !== null || $target !== null) {
            return;
        }
        if (!$input->getOption('vars')) {
            return;
        }
        $showAll   = $input->getOption('all');
        $variables = $this->prepareVariables($this->getVariables($showAll));
        if (empty($variables)) {
            return;
        }
        return array(
            'Variables' => $variables,
        );
    }
    protected function getVariables($showAll)
    {
        $scopeVars = $this->context->getAll();
        uksort($scopeVars, function ($a, $b) {
            if ($a === '_e') {
                return 1;
            } elseif ($b === '_e') {
                return -1;
            } elseif ($a === '_') {
                return 1;
            } elseif ($b === '_') {
                return -1;
            } else {
                return strcasecmp($a, $b);
            }
        });
        $ret = array();
        foreach ($scopeVars as $name => $val) {
            if (!$showAll && in_array($name, self::$specialVars)) {
                continue;
            }
            $ret[$name] = $val;
        }
        return $ret;
    }
    protected function prepareVariables(array $variables)
    {
        $ret = array();
        foreach ($variables as $name => $val) {
            if ($this->showItem($name)) {
                $fname = '$' . $name;
                $ret[$fname] = array(
                    'name'  => $fname,
                    'style' => in_array($name, self::$specialVars) ? self::IS_PRIVATE : self::IS_PUBLIC,
                    'value' => $this->presentRef($val), 
                );
            }
        }
        return $ret;
    }
}
