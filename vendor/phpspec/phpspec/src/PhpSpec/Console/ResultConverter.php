<?php
namespace PhpSpec\Console;
use PhpSpec\Event\ExampleEvent;
class ResultConverter
{
    public function convert($result)
    {
        switch ($result) {
            case ExampleEvent::PASSED:
            case ExampleEvent::SKIPPED:
                return 0;
        }
        return 1;
    }
}
