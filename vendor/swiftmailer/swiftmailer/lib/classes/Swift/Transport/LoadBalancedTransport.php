<?php
class Swift_Transport_LoadBalancedTransport implements Swift_Transport
{
    private $_deadTransports = array();
    protected $_transports = array();
    public function __construct()
    {
    }
    public function setTransports(array $transports)
    {
        $this->_transports = $transports;
        $this->_deadTransports = array();
    }
    public function getTransports()
    {
        return array_merge($this->_transports, $this->_deadTransports);
    }
    public function isStarted()
    {
        return count($this->_transports) > 0;
    }
    public function start()
    {
        $this->_transports = array_merge($this->_transports, $this->_deadTransports);
    }
    public function stop()
    {
        foreach ($this->_transports as $transport) {
            $transport->stop();
        }
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
                if ($sent = $transport->send($message, $failedRecipients)) {
                    break;
                }
            } catch (Swift_TransportException $e) {
                $this->_killCurrentTransport();
            }
        }
        if (count($this->_transports) == 0) {
            throw new Swift_TransportException(
                'All Transports in LoadBalancedTransport failed, or no Transports available'
                );
        }
        return $sent;
    }
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        foreach ($this->_transports as $transport) {
            $transport->registerPlugin($plugin);
        }
    }
    protected function _getNextTransport()
    {
        if ($next = array_shift($this->_transports)) {
            $this->_transports[] = $next;
        }
        return $next;
    }
    protected function _killCurrentTransport()
    {
        if ($transport = array_pop($this->_transports)) {
            try {
                $transport->stop();
            } catch (Exception $e) {
            }
            $this->_deadTransports[] = $transport;
        }
    }
}
