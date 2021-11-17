<?php
namespace PhpSpec\Process\ReRunner;
class PassthruReRunner extends PhpExecutableReRunner
{
    public function isSupported()
    {
        return (php_sapi_name() == 'cli')
            && $this->getExecutablePath()
            && function_exists('passthru');
    }
    public function reRunSuite()
    {
        $args = $_SERVER['argv'];
        $command = escapeshellcmd($this->getExecutablePath()).' '.join(' ', array_map('escapeshellarg', $args));
        passthru($command, $exitCode);
        exit($exitCode);
    }
}
