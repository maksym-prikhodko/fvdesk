<?php
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Process\Process;
class IsolatedProcessContext implements Context, SnippetAcceptingContext
{
    private $lastOutput;
    public static function checkDependencies()
    {
        chdir(sys_get_temp_dir());
        if (!@`which expect`) {
            throw new \Exception('Smoke tests require the `expect` command line application');
        }
    }
    public function iHaveStartedDescribingTheClass($class)
    {
        $process = new Process($this->buildPhpSpecCmd() . ' describe '. escapeshellarg($class));
        $process->run();
        expect($process->getExitCode())->toBe(0);
    }
    public function iRunPhpspecAndAnswerWhenAskedIfIWantToGenerateTheCode($answer)
    {
        $process = new Process(
            "exec expect -c '\n" .
            "set timeout 10\n" .
            "spawn {$this->buildPhpSpecCmd()} run\n" .
            "expect \"Y/n\"\n" .
            "send \"$answer\n\"\n" .
            "expect \"Y/n\"\n" .
            "interact\n" .
            "'"
        );
        $process->run();
        $this->lastOutput = $process->getOutput();
        expect((bool)$process->getErrorOutput())->toBe(false);
    }
    protected function buildPhpSpecCmd()
    {
        return escapeshellcmd(__DIR__ . '/../../bin/phpspec');
    }
    public function theTestsShouldBeRerun()
    {
        expect(substr_count($this->lastOutput, 'for you?'))->toBe(2);
    }
}
