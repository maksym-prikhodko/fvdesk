<?php
namespace SebastianBergmann\RecursionContext;
final class Context
{
    private $arrays;
    private $objects;
    public function __construct()
    {
        $this->arrays  = array();
        $this->objects = new \SplObjectStorage;
    }
    public function add(&$value)
    {
        if (is_array($value)) {
            return $this->addArray($value);
        }
        else if (is_object($value)) {
            return $this->addObject($value);
        }
        throw new InvalidArgumentException(
            'Only arrays and objects are supported'
        );
    }
    public function contains(&$value)
    {
        if (is_array($value)) {
            return $this->containsArray($value);
        }
        else if (is_object($value)) {
            return $this->containsObject($value);
        }
        throw new InvalidArgumentException(
            'Only arrays and objects are supported'
        );
    }
    private function addArray(array &$array)
    {
        $key = $this->containsArray($array);
        if ($key !== false) {
            return $key;
        }
        $this->arrays[] = &$array;
        return count($this->arrays) - 1;
    }
    private function addObject($object)
    {
        if (!$this->objects->contains($object)) {
            $this->objects->attach($object);
        }
        return spl_object_hash($object);
    }
    private function containsArray(array &$array)
    {
        $keys = array_keys($this->arrays, $array, true);
        $hash = '_Key_' . hash('sha512', microtime(true));
        foreach ($keys as $key) {
            $this->arrays[$key][$hash] = $hash;
            if (isset($array[$hash]) && $array[$hash] === $hash) {
                unset($this->arrays[$key][$hash]);
                return $key;
            }
            unset($this->arrays[$key][$hash]);
        }
        return false;
    }
    private function containsObject($value)
    {
        if ($this->objects->contains($value)) {
            return spl_object_hash($value);
        }
        return false;
    }
}
