<?php
namespace Symfony\Component\HttpKernel\Log;
use Psr\Log\NullLogger as PsrNullLogger;
class NullLogger extends PsrNullLogger implements LoggerInterface
{
    public function emerg($message, array $context = array())
    {
    }
    public function crit($message, array $context = array())
    {
    }
    public function err($message, array $context = array())
    {
    }
    public function warn($message, array $context = array())
    {
    }
}
