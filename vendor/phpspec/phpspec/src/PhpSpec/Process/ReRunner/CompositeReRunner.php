<?php
namespace PhpSpec\Process\ReRunner;
use PhpSpec\Process\ReRunner;
class CompositeReRunner implements ReRunner
{
    private $reRunner;
    public function __construct(array $reRunners)
    {
        foreach ($reRunners as $reRunner) {
            if ($reRunner->isSupported()) {
                $this->reRunner = $reRunner;
                break;
            }
        }
    }
    public function reRunSuite()
    {
        $this->reRunner->reRunSuite();
    }
}
