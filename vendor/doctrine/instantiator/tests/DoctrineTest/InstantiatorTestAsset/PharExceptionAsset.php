<?php
namespace DoctrineTest\InstantiatorTestAsset;
use BadMethodCallException;
use PharException;
class PharExceptionAsset extends PharException
{
    public function __construct()
    {
        throw new BadMethodCallException('Not supposed to be called!');
    }
}
