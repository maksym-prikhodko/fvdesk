<?php
class Swift_TransportException extends Swift_IoException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
