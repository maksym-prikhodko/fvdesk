<?php
use SebastianBergmann\Environment\Runtime;
class PHPUnit_Util_PHP_Windows extends PHPUnit_Util_PHP_Default
{
    private $tempFile;
    public function runJob($job, array $settings = array())
    {
        $runtime = new Runtime;
        if (false === $stdout_handle = tmpfile()) {
            throw new PHPUnit_Framework_Exception(
                'A temporary file could not be created; verify that your TEMP environment variable is writable'
            );
        }
        $process = proc_open(
            $runtime->getBinary() . $this->settingsToParameters($settings),
            array(
            0 => array('pipe', 'r'),
            1 => $stdout_handle,
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
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        proc_close($process);
        rewind($stdout_handle);
        $stdout = stream_get_contents($stdout_handle);
        fclose($stdout_handle);
        $this->cleanup();
        return array('stdout' => $stdout, 'stderr' => $stderr);
    }
    protected function process($pipe, $job)
    {
        if (!($this->tempFile = tempnam(sys_get_temp_dir(), 'PHPUnit')) ||
            file_put_contents($this->tempFile, $job) === false) {
            throw new PHPUnit_Framework_Exception(
                'Unable to write temporary file'
            );
        }
        fwrite(
            $pipe,
            "<?php require_once " . var_export($this->tempFile, true) .  "; ?>"
        );
    }
    protected function cleanup()
    {
        unlink($this->tempFile);
    }
}
