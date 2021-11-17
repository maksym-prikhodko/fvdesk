<?php
namespace Symfony\Component\Process\Tests;
use Symfony\Component\Process\Process;
class ProcessInSigchildEnvironment extends Process
{
    protected function isSigchildEnabled()
    {
        return true;
    }
}
