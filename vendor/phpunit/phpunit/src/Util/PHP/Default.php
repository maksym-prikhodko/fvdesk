<?php
use SebastianBergmann\Environment\Runtime;
class PHPUnit_Util_PHP_Default extends PHPUnit_Util_PHP
{
    public function runJob($job, array $settings = array())
    {
        $runtime = new Runtime;
        $process = proc_open(
            $runtime->getBinary() . $this->settingsToParameters($settings),
            array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w')
            ),
            $pipes
        );
        if (!is_resource($process)) {
            throw new PHPUnit_Framework_Exception(
                'Unable to spawn worker process'
            );
        }
        $this->process($pipes[0], $job);
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        proc_close($process);
        $this->cleanup();
        return array('stdout' => $stdout, 'stderr' => $stderr);
    }
    protected function process($pipe, $job)
    {
        fwrite($pipe, $job);
    }
    protected function cleanup()
    {
    }
}
