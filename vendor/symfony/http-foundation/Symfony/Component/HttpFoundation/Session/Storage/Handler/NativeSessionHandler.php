<?php
namespace Symfony\Component\HttpFoundation\Session\Storage\Handler;
if (PHP_VERSION_ID >= 50400) {
    class NativeSessionHandler extends \SessionHandler
    {
    }
} else {
    class NativeSessionHandler
    {
    }
}
