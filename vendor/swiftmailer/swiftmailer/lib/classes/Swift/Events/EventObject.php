<?php
class Swift_Events_EventObject implements Swift_Events_Event
{
    private $_source;
    private $_bubbleCancelled = false;
    public function __construct($source)
    {
        $this->_source = $source;
    }
    public function getSource()
    {
        return $this->_source;
    }
    public function cancelBubble($cancel = true)
    {
        $this->_bubbleCancelled = $cancel;
    }
    public function bubbleCancelled()
    {
        return $this->_bubbleCancelled;
    }
}
