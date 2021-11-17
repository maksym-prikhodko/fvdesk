<?php
namespace Symfony\Component\VarDumper\Exception;
class ThrowingCasterException extends \Exception
{
    public function __construct($caster, \Exception $prev)
    {
        parent::__construct('Unexpected exception thrown from a caster: '.get_class($prev), 0, $prev);
    }
}
