<?php
namespace DoctrineTest\InstantiatorTestAsset;
use BadMethodCallException;
use Phar;
class PharAsset extends Phar
{
    public function __construct()
    {
        throw new BadMethodCallException('Not supposed to be called!');
    }
}
