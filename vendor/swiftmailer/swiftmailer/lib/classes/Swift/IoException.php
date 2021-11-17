<?php
class Swift_IoException extends Swift_SwiftException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
