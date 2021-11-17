<?php
namespace DoctrineTest\InstantiatorTestAsset;
use ArrayObject;
use BadMethodCallException;
class ArrayObjectAsset extends ArrayObject
{
    public function __construct()
    {
        throw new BadMethodCallException('Not supposed to be called!');
    }
}
