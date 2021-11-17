<?php
class Swift_FailoverTransport extends Swift_Transport_FailoverTransport
{
    public function __construct($transports = array())
    {
        call_user_func_array(
            array($this, 'Swift_Transport_FailoverTransport::__construct'),
            Swift_DependencyContainer::getInstance()
                ->createDependenciesFor('transport.failover')
            );
        $this->setTransports($transports);
    }
    public static function newInstance($transports = array())
    {
        return new self($transports);
    }
}
