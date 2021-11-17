<?php
namespace DoctrineTest\InstantiatorTestAsset;
use BadMethodCallException;
use Serializable;
class SimpleSerializableAsset implements Serializable
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
