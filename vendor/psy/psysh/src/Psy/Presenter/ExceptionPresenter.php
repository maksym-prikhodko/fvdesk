<?php
namespace Psy\Presenter;
class ExceptionPresenter extends ObjectPresenter
{
    public function canPresent($value)
    {
        return $value instanceof \Exception;
    }
    protected function getProperties($value, \ReflectionClass $class, $options = 0)
    {
        $props = array(
            '<protected>message</protected>' => $value->getMessage(),
            '<protected>code</protected>'    => $value->getCode(),
            '<protected>file</protected>'    => $value->getFile(),
            '<protected>line</protected>'    => $value->getLine(),
            '<private>previous</private>'    => $value->getPrevious(),
        );
        return array_merge(array_filter($props), parent::getProperties($value, $class, $options));
    }
}
