<?php
class Swift_Plugins_MessageLogger implements Swift_Events_SendListener
{
    private $messages;
    public function __construct()
    {
        $this->messages = array();
    }
    public function getMessages()
    {
        return $this->messages;
    }
    public function countMessages()
    {
        return count($this->messages);
    }
    public function clear()
    {
        $this->messages = array();
    }
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        $this->messages[] = clone $evt->getMessage();
    }
    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
    }
}
