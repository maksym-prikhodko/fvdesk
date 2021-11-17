<?php
namespace Symfony\Component\Debug;
class Debug
{
    private static $enabled = false;
    public static function enable($errorReportingLevel = null, $displayErrors = true)
    {
        if (static::$enabled) {
            return;
        }
        static::$enabled = true;
        if (null !== $errorReportingLevel) {
            error_reporting($errorReportingLevel);
        } else {
            error_reporting(-1);
        }
        if ('cli' !== php_sapi_name()) {
            ini_set('display_errors', 0);
            ExceptionHandler::register();
        } elseif ($displayErrors && (!ini_get('log_errors') || ini_get('error_log'))) {
            ini_set('display_errors', 1);
        }
        $handler = ErrorHandler::register();
        if (!$displayErrors) {
            $handler->throwAt(0, true);
        }
        DebugClassLoader::enable();
    }
}
