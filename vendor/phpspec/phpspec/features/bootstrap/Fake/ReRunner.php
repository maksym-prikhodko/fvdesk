<?php
namespace Fake;
use PhpSpec\Process\ReRunner as BaseReRunner;
class ReRunner implements BaseReRunner
{
    private $hasBeenReRun = false;
    public function isSupported()
    {
        return true;
    }
    public function reRunSuite()
    {
        $this->hasBeenReRun = true;
    }
    public function hasBeenReRun()
    {
        return $this->hasBeenReRun;
    }
}
