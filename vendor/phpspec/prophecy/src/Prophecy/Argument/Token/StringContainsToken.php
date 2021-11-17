<?php
namespace Prophecy\Argument\Token;
class StringContainsToken implements TokenInterface
{
    private $value;
    public function __construct($value)
    {
        $this->value = $value;
    }
    public function scoreArgument($argument)
    {
        return strpos($argument, $this->value) !== false ? 6 : false;
    }
    public function getValue()
    {
        return $this->value;
    }
    public function isLast()
    {
        return false;
    }
    public function __toString()
    {
        return sprintf('contains("%s")', $this->value);
    }
}
