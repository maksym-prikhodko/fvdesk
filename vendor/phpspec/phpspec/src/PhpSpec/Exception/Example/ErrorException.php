<?php
namespace PhpSpec\Exception\Example;
class ErrorException extends ExampleException
{
    private $levels = array(
        E_WARNING           => 'warning',
        E_NOTICE            => 'notice',
        E_USER_ERROR        => 'error',
        E_USER_WARNING      => 'warning',
        E_USER_NOTICE       => 'notice',
        E_STRICT            => 'notice',
        E_RECOVERABLE_ERROR => 'error',
    );
    public function __construct($level, $message, $file, $line)
    {
        parent::__construct(sprintf(
            '%s: %s in %s line %d',
            isset($this->levels[$level]) ? $this->levels[$level] : $level,
            $message,
            $file,
            $line
        ));
    }
}
