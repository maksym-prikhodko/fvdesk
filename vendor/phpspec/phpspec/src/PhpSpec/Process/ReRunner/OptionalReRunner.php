<?php
namespace PhpSpec\Process\ReRunner;
use PhpSpec\Console\IO;
use PhpSpec\Process\ReRunner;
class OptionalReRunner implements ReRunner
{
    private $io;
    private $decoratedRerunner;
    public function __construct(ReRunner $decoratedRerunner, IO $io)
    {
        $this->io = $io;
        $this->decoratedRerunner = $decoratedRerunner;
    }
    public function reRunSuite()
    {
        if ($this->io->isRerunEnabled()) {
            $this->decoratedRerunner->reRunSuite();
        }
    }
}
