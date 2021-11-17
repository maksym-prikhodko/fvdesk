<?php
namespace Symfony\Component\Console\Tests\Logger;
use Psr\Log\Test\LoggerInterfaceTest;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Tests\Fixtures\DummyOutput;
use Symfony\Component\Console\Output\OutputInterface;
class ConsoleLoggerTest extends LoggerInterfaceTest
{
    protected $output;
    public function getLogger()
    {
        $this->output = new DummyOutput(OutputInterface::VERBOSITY_VERBOSE);
        return new ConsoleLogger($this->output, array(
            LogLevel::EMERGENCY => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::ALERT => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::CRITICAL => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::ERROR => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::WARNING => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG => OutputInterface::VERBOSITY_NORMAL,
        ));
    }
    public function getLogs()
    {
        return $this->output->getLogs();
    }
}
