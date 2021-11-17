<?php
namespace PhpSpec\Process\ReRunner;
class PcntlReRunner extends PhpExecutableReRunner
{
    public function isSupported()
    {
        return (php_sapi_name() == 'cli')
            && $this->getExecutablePath()
            && function_exists('pcntl_exec')
            && !defined('HHVM_VERSION');
    }
    public function reRunSuite()
    {
        $args = $_SERVER['argv'];
        pcntl_exec($this->getExecutablePath(), $args);
    }
}
