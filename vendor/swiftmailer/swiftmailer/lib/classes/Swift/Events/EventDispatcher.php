<?php
interface Swift_Events_EventDispatcher
{
    public function createSendEvent(Swift_Transport $source, Swift_Mime_Message $message);
    public function createCommandEvent(Swift_Transport $source, $command, $successCodes = array());
    public function createResponseEvent(Swift_Transport $source, $response, $valid);
    public function createTransportChangeEvent(Swift_Transport $source);
    public function createTransportExceptionEvent(Swift_Transport $source, Swift_TransportException $ex);
    public function bindEventListener(Swift_Events_EventListener $listener);
    public function dispatchEvent(Swift_Events_EventObject $evt, $target);
}
