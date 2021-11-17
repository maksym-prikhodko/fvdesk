<?php
class Swift_Events_TransportExceptionEvent extends Swift_Events_EventObject
{
    private $_exception;
    public function __construct(Swift_Transport $transport, Swift_TransportException $ex)
    {
        parent::__construct($transport);
        $this->_exception = $ex;
    }
    public function getException()
    {
        return $this->_exception;
    }
}
