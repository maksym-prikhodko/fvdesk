<?php
class Swift_SwiftException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
