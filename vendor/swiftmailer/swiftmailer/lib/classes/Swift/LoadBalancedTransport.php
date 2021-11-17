<?php
class Swift_LoadBalancedTransport extends Swift_Transport_LoadBalancedTransport
{
    public function __construct($transports = array())
    {
        call_user_func_array(
            array($this, 'Swift_Transport_LoadBalancedTransport::__construct'),
            Swift_DependencyContainer::getInstance()
                ->createDependenciesFor('transport.loadbalanced')
            );
        $this->setTransports($transports);
    }
    public static function newInstance($transports = array())
    {
        return new self($transports);
    }
}
