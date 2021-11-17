<?php
class Swift_Plugins_ImpersonatePlugin implements Swift_Events_SendListener
{
    private $_sender;
    public function __construct($sender)
    {
        $this->_sender = $sender;
    }
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        $message = $evt->getMessage();
        $headers = $message->getHeaders();
        $headers->addPathHeader('X-Swift-Return-Path', $message->getReturnPath());
        $message->setReturnPath($this->_sender);
    }
    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
        $message = $evt->getMessage();
        $headers = $message->getHeaders();
        if ($headers->has('X-Swift-Return-Path')) {
            $message->setReturnPath($headers->get('X-Swift-Return-Path')->getAddress());
            $headers->removeAll('X-Swift-Return-Path');
        }
    }
}
