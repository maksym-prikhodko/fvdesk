<?php namespace SuperClosure;
use SuperClosure\Exception\ClosureUnserializationException;
interface SerializerInterface
{
    public function serialize(\Closure $closure);
    public function unserialize($serialized);
    public function getData(\Closure $closure, $forSerialization = false);
}
