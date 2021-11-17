<?php
namespace Matcher;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\Matcher\MatcherInterface;
use Symfony\Component\Console\Tester\ApplicationTester;
class ApplicationOutputMatcher implements MatcherInterface
{
    public function supports($name, $subject, array $arguments)
    {
        return ($name == 'haveOutput' && $subject instanceof ApplicationTester);
    }
    public function positiveMatch($name, $subject, array $arguments)
    {
        $expected = $this->normalize($arguments[0]);
        $actual = $this->normalize($subject->getDisplay());
        if (strpos($actual, $expected) === false) {
            throw new FailureException(sprintf(
                "Application output did not contain expected '%s'. Actual output:\n'%s'" ,
                $expected,
                $subject->getDisplay()
            ));
        }
    }
    private function normalize($string)
    {
        $string = preg_replace('/\([0-9]+ms\)/', '', $string);
        return $string;
    }
    public function negativeMatch($name, $subject, array $arguments)
    {
        throw new FailureException('Negative application output matcher not implemented');
    }
    public function getPriority()
    {
        return 51;
    }
}
