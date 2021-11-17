<?php
namespace Symfony\Component\Process\Tests;
class SigchildDisabledProcessTest extends AbstractProcessTest
{
    public function testGetExitCode()
    {
        parent::testGetExitCode();
    }
    public function testGetExitCodeIsNullOnStart()
    {
        parent::testGetExitCodeIsNullOnStart();
    }
    public function testGetExitCodeIsNullOnWhenStartingAgain()
    {
        parent::testGetExitCodeIsNullOnWhenStartingAgain();
    }
    public function testExitCodeCommandFailed()
    {
        parent::testExitCodeCommandFailed();
    }
    public function testMustRun()
    {
        parent::testMustRun();
    }
    public function testSuccessfulMustRunHasCorrectExitCode()
    {
        parent::testSuccessfulMustRunHasCorrectExitCode();
    }
    public function testMustRunThrowsException()
    {
        parent::testMustRunThrowsException();
    }
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
    public function testCheckTimeoutOnStartedProcess()
    {
        parent::testCheckTimeoutOnStartedProcess();
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
        $process->getExitCodeText();
    }
    public function testExitCodeTextIsNullWhenExitCodeIsNull()
    {
        parent::testExitCodeTextIsNullWhenExitCodeIsNull();
    }
    public function testIsSuccessful()
    {
        parent::testIsSuccessful();
    }
    public function testIsSuccessfulOnlyAfterTerminated()
    {
        parent::testIsSuccessfulOnlyAfterTerminated();
    }
    public function testIsNotSuccessful()
    {
        parent::testIsNotSuccessful();
    }
    public function testTTYCommandExitCode()
    {
        parent::testTTYCommandExitCode();
    }
    public function testSignal()
    {
        parent::testSignal();
    }
    public function testProcessWithoutTermSignalIsNotSignaled()
    {
        parent::testProcessWithoutTermSignalIsNotSignaled();
    }
    public function testStopWithTimeoutIsActuallyWorking()
    {
        $this->markTestSkipped('Stopping with signal is not supported in sigchild environment');
    }
    public function testProcessThrowsExceptionWhenExternallySignaled()
    {
        $this->markTestSkipped('Retrieving Pid is not supported in sigchild environment');
    }
    public function testExitCodeIsAvailableAfterSignal()
    {
        $this->markTestSkipped('Signal is not supported in sigchild environment');
    }
    public function testRunProcessWithTimeout()
    {
        $this->markTestSkipped('Signal (required for timeout) is not supported in sigchild environment');
    }
    public function provideStartMethods()
    {
        return array(
            array('start', 'Symfony\Component\Process\Exception\LogicException', 'Output has been disabled, enable it to allow the use of a callback.'),
            array('run', 'Symfony\Component\Process\Exception\LogicException', 'Output has been disabled, enable it to allow the use of a callback.'),
            array('mustRun', 'Symfony\Component\Process\Exception\RuntimeException', 'This PHP has been compiled with --enable-sigchild. You must use setEnhanceSigchildCompatibility() to use this method.'),
        );
    }
    protected function getProcess($commandline, $cwd = null, array $env = null, $input = null, $timeout = 60, array $options = array())
    {
        $process = new ProcessInSigchildEnvironment($commandline, $cwd, $env, $input, $timeout, $options);
        $process->setEnhanceSigchildCompatibility(false);
        return $process;
    }
}
