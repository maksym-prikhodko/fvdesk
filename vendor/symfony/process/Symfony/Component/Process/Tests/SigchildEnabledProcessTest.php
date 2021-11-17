<?php
namespace Symfony\Component\Process\Tests;
class SigchildEnabledProcessTest extends AbstractProcessTest
{
    public function testProcessIsSignaledIfStopped()
    {
        parent::testProcessIsSignaledIfStopped();
    }
    public function testProcessWithTermSignal()
    {
        parent::testProcessWithTermSignal();
    }
    public function testProcessIsNotSignaled()
    {
        parent::testProcessIsNotSignaled();
    }
    public function testProcessWithoutTermSignal()
    {
        parent::testProcessWithoutTermSignal();
    }
    public function testGetPid()
    {
        parent::testGetPid();
    }
    public function testGetPidIsNullBeforeStart()
    {
        parent::testGetPidIsNullBeforeStart();
    }
    public function testGetPidIsNullAfterRun()
    {
        parent::testGetPidIsNullAfterRun();
    }
    public function testExitCodeText()
    {
        $process = $this->getProcess('qdfsmfkqsdfmqmsd');
        $process->run();
        $this->assertInternalType('string', $process->getExitCodeText());
    }
    public function testSignal()
    {
        parent::testSignal();
    }
    public function testProcessWithoutTermSignalIsNotSignaled()
    {
        parent::testProcessWithoutTermSignalIsNotSignaled();
    }
    public function testProcessThrowsExceptionWhenExternallySignaled()
    {
        $this->markTestSkipped('Retrieving Pid is not supported in sigchild environment');
    }
    public function testExitCodeIsAvailableAfterSignal()
    {
        $this->markTestSkipped('Signal is not supported in sigchild environment');
    }
    public function testStartAfterATimeout()
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('Restarting a timed-out process on Windows is not supported in sigchild environment');
        }
        parent::testStartAfterATimeout();
    }
    public function testStopWithTimeoutIsActuallyWorking()
    {
        $this->markTestSkipped('Stopping with signal is not supported in sigchild environment');
    }
    public function testRunProcessWithTimeout()
    {
        $this->markTestSkipped('Signal (required for timeout) is not supported in sigchild environment');
    }
    public function testCheckTimeoutOnStartedProcess()
    {
        $this->markTestSkipped('Signal (required for timeout) is not supported in sigchild environment');
    }
    protected function getProcess($commandline, $cwd = null, array $env = null, $input = null, $timeout = 60, array $options = array())
    {
        $process = new ProcessInSigchildEnvironment($commandline, $cwd, $env, $input, $timeout, $options);
        $process->setEnhanceSigchildCompatibility(true);
        return $process;
    }
}
