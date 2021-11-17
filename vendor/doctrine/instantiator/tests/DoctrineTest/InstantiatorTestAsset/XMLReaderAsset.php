<?php
namespace DoctrineTest\InstantiatorTestAsset;
use BadMethodCallException;
use XMLReader;
class XMLReaderAsset extends XMLReader
{
    public function __construct()
    {
        throw new BadMethodCallException('Not supposed to be called!');
    }
}
