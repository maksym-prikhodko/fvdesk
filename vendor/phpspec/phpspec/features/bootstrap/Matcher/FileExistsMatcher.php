<?php
namespace Matcher;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\Matcher\MatcherInterface;
class FileExistsMatcher implements MatcherInterface
{
    public function supports($name, $subject, array $arguments)
    {
        return ('exist' == $name && is_string($subject));
    }
    public function positiveMatch($name, $subject, array $arguments)
    {
        if (!file_exists($subject)) {
            throw new FailureException(sprintf(
                "File did not exist at path '%s'",
                $subject
            ));
        }
    }
    public function negativeMatch($name, $subject, array $arguments)
    {
        if (file_exists($subject)) {
            throw new FailureException(sprintf(
                "File unexpectedly exists at path '%s'",
                $subject
            ));
        }
    }
    public function getPriority()
    {
        return 0;
    }
}
