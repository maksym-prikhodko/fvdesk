<?php
namespace Matcher;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\Matcher\MatcherInterface;
class FileHasContentsMatcher implements MatcherInterface
{
    public function supports($name, $subject, array $arguments)
    {
        return ('haveContents' == $name && is_string($subject));
    }
    public function positiveMatch($name, $subject, array $arguments)
    {
        $path = $subject;
        $expectedContents = $arguments[0];
        if ($expectedContents != file_get_contents($path)) {
            throw new FailureException(sprintf(
                "File at '%s' did not contain expected contents.\nExpected: '%s'\nActual: '%s'",
                $path,
                $expectedContents,
                file_get_contents($path)
            ));
        }
    }
    public function negativeMatch($name, $subject, array $arguments)
    {
        throw new FailureException('Negative file contents matcher not implemented');
    }
    public function getPriority()
    {
        return 51;
    }
}
