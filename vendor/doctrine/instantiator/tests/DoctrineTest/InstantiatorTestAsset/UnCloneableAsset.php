<?php
namespace DoctrineTest\InstantiatorTestAsset;
use BadMethodCallException;
class UnCloneableAsset
{
    public function __construct()
    {
        throw new BadMethodCallException('Not supposed to be called!');
    }
    public function __clone()
    {
        throw new BadMethodCallException('Not supposed to be called!');
    }
}
