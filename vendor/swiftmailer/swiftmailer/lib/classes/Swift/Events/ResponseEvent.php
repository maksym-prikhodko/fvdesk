<?php
class Swift_Events_ResponseEvent extends Swift_Events_EventObject
{
    private $_valid;
    private $_response;
    public function __construct(Swift_Transport $source, $response, $valid = false)
    {
        parent::__construct($source);
        $this->_response = $response;
        $this->_valid = $valid;
    }
    public function getResponse()
    {
        return $this->_response;
    }
    public function isValid()
    {
        return $this->_valid;
    }
}
