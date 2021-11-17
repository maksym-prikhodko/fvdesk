<?php
namespace PhpSpec\Process\ReRunner;
use PhpSpec\Process\ReRunner;
interface PlatformSpecificReRunner extends ReRunner
{
    public function isSupported();
}
