<?php
namespace PhpSpec\Matcher;
use ArrayAccess;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\Formatter\Presenter\PresenterInterface;
class ArrayKeyValueMatcher extends BasicMatcher
{
    private $presenter;
    public function __construct(PresenterInterface $presenter)
    {
        $this->presenter = $presenter;
    }
    public function supports($name, $subject, array $arguments)
    {
        return 'haveKeyWithValue' === $name
        && 2 == count($arguments)
        && (is_array($subject) || $subject instanceof ArrayAccess)
            ;
    }
    protected function matches($subject, array $arguments)
    {
        $key = $arguments[0];
        $value  = $arguments[1];
        if ($subject instanceof ArrayAccess) {
            return $subject->offsetExists($key) && $subject->offsetGet($key) === $value;
        }
        return (isset($subject[$key]) || array_key_exists($arguments[0], $subject) && $subject[$key] === $value);
    }
    protected function getFailureException($name, $subject, array $arguments)
    {
        $key = $arguments[0];
        $expectedValue = $arguments[1];
        $actualValue = $subject[$key];
        if (!$this->offsetExists($key, $subject)) {
            return new FailureException(sprintf('Expected %s to have value %s for %s key, but no key was set.',
                $this->presenter->presentValue($subject),
                $this->presenter->presentValue($expectedValue),
                $this->presenter->presentString($key)
            ));
        }
        return new FailureException(sprintf(
            'Expected %s to have value %s for %s key, but found %s.',
            $this->presenter->presentValue($subject),
            $this->presenter->presentValue($expectedValue),
            $this->presenter->presentString($key),
            $this->presenter->presentValue($actualValue)
        ));
    }
    protected function getNegativeFailureException($name, $subject, array $arguments)
    {
        return new FailureException(sprintf(
            'Expected %s not to have %s key, but it does.',
            $this->presenter->presentValue($subject),
            $this->presenter->presentString($arguments[0])
        ));
    }
    private function offsetExists($key, $subject)
    {
        return ($subject instanceof ArrayAccess && $subject->offsetExists($key)) || array_key_exists($key, $subject);
    }
}
