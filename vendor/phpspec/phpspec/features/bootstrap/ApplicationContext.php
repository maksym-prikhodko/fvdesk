<?php
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Fake\Prompter;
use Fake\ReRunner;
use Matcher\ApplicationOutputMatcher;
use Matcher\ExitStatusMatcher;
use Matcher\ValidJUnitXmlMatcher;
use PhpSpec\Console\Application;
use PhpSpec\Matcher\MatchersProviderInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Tester\ApplicationTester;
class ApplicationContext implements Context, MatchersProviderInterface
{
    private $application;
    private $lastExitCode;
    private $tester;
    private $prompter;
    private $reRunner;
    public function setupApplication()
    {
        $this->application = new Application('2.1-dev');
        $this->application->setAutoExit(false);
        $this->tester = new ApplicationTester($this->application);
        $this->setupReRunner();
        $this->setupPrompter();
    }
    private function setupPrompter()
    {
        $this->prompter = new Prompter();
        $this->application->getContainer()->set('console.prompter', $this->prompter);
    }
    private function setupReRunner()
    {
        $this->reRunner = new ReRunner;
        $this->application->getContainer()->set('process.rerunner.platformspecific', $this->reRunner);
    }
    public function iDescribeTheClass($class)
    {
        $arguments = array(
            'command' => 'describe',
            'class' => $class
        );
        expect($this->tester->run($arguments, array('interactive' => false)))->toBe(0);
    }
    public function iRunPhpspec($formatter = null, $option = null, $interactive=null)
    {
        $arguments = array (
            'command' => 'run'
        );
        if ($formatter) {
            $arguments['--format'] = $formatter;
        }
        $this->addOptionToArguments($option, $arguments);
        $this->lastExitCode = $this->tester->run($arguments, array('interactive' => (bool)$interactive));
    }
    public function iRunPhpspecAndAnswerWhenAskedIfIWantToGenerateTheCode($answer, $option=null)
    {
        $arguments = array (
            'command' => 'run'
        );
        $this->addOptionToArguments($option, $arguments);
        $this->prompter->setAnswer($answer=='y');
        $this->lastExitCode = $this->tester->run($arguments, array('interactive' => true));
    }
    private function addOptionToArguments($option, array &$arguments)
    {
        if ($option) {
            if (preg_match('/(?P<option>[a-z-]+)=(?P<value>[a-z.]+)/', $option, $matches)) {
                $arguments[$matches['option']] = $matches['value'];
            } else {
                $arguments['--' . trim($option, '"')] = true;
            }
        }
    }
    public function iShouldSee($output)
    {
        expect($this->tester)->toHaveOutput((string)$output);
    }
    public function iShouldBePromptedForCodeGeneration()
    {
        expect($this->prompter)->toHaveBeenAsked();
    }
    public function iShouldNotBePromptedForCodeGeneration()
    {
        expect($this->prompter)->toNotHaveBeenAsked();
    }
    public function theSuiteShouldPass()
    {
        expect($this->lastExitCode)->toBeLike(0);
    }
    public function exampleShouldHaveBeenSkipped($number)
    {
        expect($this->tester)->toHaveOutput("($number skipped)");
    }
    public function examplesShouldHaveBeenRun($number)
    {
        expect($this->tester)->toHaveOutput("$number examples");
    }
    public function theExitCodeShouldBe($code)
    {
        expect($this->lastExitCode)->toBeLike($code);
    }
    public function iShouldSeeValidJunitOutput()
    {
        expect($this->tester)->toHaveOutputValidJunitXml();
    }
    public function theTestsShouldBeRerun()
    {
        expect($this->reRunner)->toHaveBeenRerun();
    }
    public function theTestsShouldNotBeRerun()
    {
        expect($this->reRunner)->toNotHaveBeenRerun();
    }
    public function iShouldBePromptedWith(PyStringNode $question)
    {
        expect($this->prompter)->toHaveBeenAsked((string)$question);
    }
    public function getMatchers()
    {
        return array(
            new ApplicationOutputMatcher(),
            new ValidJUnitXmlMatcher()
        );
    }
}
