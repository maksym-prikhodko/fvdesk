<?php
namespace DoctrineTest\InstantiatorTestAsset;
use ArrayObject;
use BadMethodCallException;
class UnserializeExceptionArrayObjectAsset extends ArrayObject
{
    public function __wakeup()
    {
        throw new BadMethodCallException();
    }
}
