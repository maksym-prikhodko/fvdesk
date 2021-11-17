<?php
namespace PhpSpec\Matcher;
use PhpSpec\Formatter\Presenter\PresenterInterface;
use PhpSpec\Exception\Example\FailureException;
class ScalarMatcher implements MatcherInterface
{
    private $presenter;
    public function __construct(PresenterInterface $presenter)
    {
        $this->presenter = $presenter;
    }
    public function supports($name, $subject, array $arguments)
    {
        $checkerName = $this->getCheckerName($name);
        return $checkerName && function_exists($checkerName);
    }
    public function positiveMatch($name, $subject, array $arguments)
    {
        $checker = $this->getCheckerName($name);
        if (!call_user_func($checker, $subject)) {
            throw new FailureException(sprintf(
                '%s expected to return %s, but it did not.',
                $this->presenter->presentString(sprintf(
                    '%s(%s)',
                    $checker,
                    $this->presenter->presentValue($subject)
                )),
                $this->presenter->presentValue(true)
            ));
        }
    }
    public function negativeMatch($name, $subject, array $arguments)
    {
        $checker = $this->getCheckerName($name);
        if (call_user_func($checker, $subject)) {
            throw new FailureException(sprintf(
                '%s not expected to return %s, but it did.',
                $this->presenter->presentString(sprintf(
                    '%s(%s)',
                    $checker,
                    $this->presenter->presentValue($subject)
                )),
                $this->presenter->presentValue(true)
            ));
        }
    }
    public function getPriority()
    {
        return 50;
    }
    private function getCheckerName($name)
    {
        if (0 !== strpos($name, 'be')) {
            return false;
        }
        $expected = lcfirst(substr($name, 2));
        if ($expected == 'boolean') {
            return 'is_bool';
        }
        return 'is_'.$expected;
    }
}
