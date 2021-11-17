<?php
namespace PhpSpec\Util;
use PhpSpec\Exception\Fracture\ClassNotFoundException;
class Instantiator
{
    public function instantiate($className)
    {
        if (!class_exists($className)) {
            throw new ClassNotFoundException("Class $className does not exist.", $className);
        }
        $instantiator = new \Doctrine\Instantiator\Instantiator();
        return $instantiator->instantiate($className);
    }
}
