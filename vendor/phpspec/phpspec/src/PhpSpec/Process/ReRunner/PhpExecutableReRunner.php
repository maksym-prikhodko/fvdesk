<?php
namespace PhpSpec\Process\ReRunner;
use PhpSpec\Process\ReRunner;
use Symfony\Component\Process\PhpExecutableFinder;
abstract class PhpExecutableReRunner implements PlatformSpecificReRunner
{
    private $executableFinder;
    private $executablePath;
    public function __construct(PhpExecutableFinder $executableFinder)
    {
        $this->executableFinder = $executableFinder;
    }
    protected function getExecutablePath()
    {
        if (null === $this->executablePath) {
            $this->executablePath = $this->executableFinder->find();
        }
        return $this->executablePath;
    }
}
