<?php
class Swift_Events_CommandEvent extends Swift_Events_EventObject
{
    private $_command;
    private $_successCodes = array();
    public function __construct(Swift_Transport $source, $command, $successCodes = array())
    {
        parent::__construct($source);
        $this->_command = $command;
        $this->_successCodes = $successCodes;
    }
    public function getCommand()
    {
        return $this->_command;
    }
    public function getSuccessCodes()
    {
        return $this->_successCodes;
    }
}
