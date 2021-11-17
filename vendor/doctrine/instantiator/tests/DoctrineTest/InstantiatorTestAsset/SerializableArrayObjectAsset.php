<?php
namespace DoctrineTest\InstantiatorTestAsset;
use ArrayObject;
use BadMethodCallException;
use Serializable;
class SerializableArrayObjectAsset extends ArrayObject implements Serializable
{
    public function __construct()
    {
        throw new BadMethodCallException('Not supposed to be called!');
    }
    public function serialize()
    {
        return '';
    }
    public function unserialize($serialized)
    {
        throw new BadMethodCallException('Not supposed to be called!');
    }
}
