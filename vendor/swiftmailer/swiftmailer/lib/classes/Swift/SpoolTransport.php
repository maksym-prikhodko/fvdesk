<?php
class Swift_SpoolTransport extends Swift_Transport_SpoolTransport
{
    public function __construct(Swift_Spool $spool)
    {
        $arguments = Swift_DependencyContainer::getInstance()
            ->createDependenciesFor('transport.spool');
        $arguments[] = $spool;
        call_user_func_array(
            array($this, 'Swift_Transport_SpoolTransport::__construct'),
            $arguments
        );
    }
    public static function newInstance(Swift_Spool $spool)
    {
        return new self($spool);
    }
}
