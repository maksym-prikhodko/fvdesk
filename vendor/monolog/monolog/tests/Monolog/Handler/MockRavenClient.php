<?php
namespace Monolog\Handler;
use Raven_Client;
class MockRavenClient extends Raven_Client
{
    public function capture($data, $stack, $vars = null)
    {
        $this->lastData = $data;
        $this->lastStack = $stack;
    }
    public $lastData;
    public $lastStack;
}
