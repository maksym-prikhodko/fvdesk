<?php
class PHPUnit_Framework_Constraint_Exception extends PHPUnit_Framework_Constraint
{
    protected $className;
    public function __construct($className)
    {
        parent::__construct();
        $this->className = $className;
    }
    protected function matches($other)
    {
        return $other instanceof $this->className;
    }
    protected function failureDescription($other)
    {
        if ($other !== null) {
            $message = '';
            if ($other instanceof Exception) {
                $message = '. Message was: "' . $other->getMessage() . '" at'
                        . "\n" . $other->getTraceAsString();
            }
            return sprintf(
                'exception of type "%s" matches expected exception "%s"%s',
                get_class($other),
                $this->className,
                $message
            );
        }
        return sprintf(
            'exception of type "%s" is thrown',
            $this->className
        );
    }
    public function toString()
    {
        return sprintf(
            'exception of type "%s"',
            $this->className
        );
    }
}
