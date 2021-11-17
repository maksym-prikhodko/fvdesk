<?php
namespace PhpSpec\Exception\Example;
class SkippingException extends ExampleException
{
    public function __construct($text)
    {
        parent::__construct(sprintf('skipped: %s', $text));
    }
}
