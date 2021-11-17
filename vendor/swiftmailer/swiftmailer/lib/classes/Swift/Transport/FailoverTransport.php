<?php
class Swift_Transport_FailoverTransport extends Swift_Transport_LoadBalancedTransport
{
    private $_currentTransport;
    public function __construct()
    {
        parent::__construct();
    }
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $maxTransports = count($this->_transports);
        $sent = 0;
        for ($i = 0; $i < $maxTransports
            && $transport = $this->_getNextTransport(); ++$i) {
            try {
                if (!$transport->isStarted()) {
                    $transport->start();
                }
                return $transport->send($message, $failedRecipients);
            } catch (Swift_TransportException $e) {
                $this->_killCurrentTransport();
            }
        }
        if (count($this->_transports) == 0) {
            throw new Swift_TransportException(
                'All Transports in FailoverTransport failed, or no Transports available'
                );
        }
        return $sent;
    }
    protected function _getNextTransport()
    {
        if (!isset($this->_currentTransport)) {
            $this->_currentTransport = parent::_getNextTransport();
        }
        return $this->_currentTransport;
    }
    protected function _killCurrentTransport()
    {
        $this->_currentTransport = null;
        parent::_killCurrentTransport();
    }
}
