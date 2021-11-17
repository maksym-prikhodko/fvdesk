<?php
class PHPUnit_Framework_AssertionFailedError extends PHPUnit_Framework_Exception implements PHPUnit_Framework_SelfDescribing
{
    public function toString()
    {
        return $this->getMessage();
    }
}
