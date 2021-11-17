<?php
class Swift_Mailer
{
    private $_transport;
    public function __construct(Swift_Transport $transport)
    {
        $this->_transport = $transport;
    }
    public static function newInstance(Swift_Transport $transport)
    {
        return new self($transport);
    }
    public function createMessage($service = 'message')
    {
        return Swift_DependencyContainer::getInstance()
            ->lookup('message.'.$service);
    }
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $failedRecipients = (array) $failedRecipients;
        if (!$this->_transport->isStarted()) {
            $this->_transport->start();
        }
        $sent = 0;
        try {
            $sent = $this->_transport->send($message, $failedRecipients);
        } catch (Swift_RfcComplianceException $e) {
            foreach ($message->getTo() as $address => $name) {
                $failedRecipients[] = $address;
            }
        }
        return $sent;
    }
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        $this->_transport->registerPlugin($plugin);
    }
    public function getTransport()
    {
        return $this->_transport;
    }
}
