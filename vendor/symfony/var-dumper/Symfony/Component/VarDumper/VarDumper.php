<?php
namespace Symfony\Component\VarDumper;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
require_once __DIR__.'/Resources/functions/dump.php';
class VarDumper
{
    private static $handler;
    public static function dump($var)
    {
        if (null === self::$handler) {
            $cloner = new VarCloner();
            $dumper = 'cli' === PHP_SAPI ? new CliDumper() : new HtmlDumper();
            self::$handler = function ($var) use ($cloner, $dumper) {
                $dumper->dump($cloner->cloneVar($var));
            };
        }
        return call_user_func(self::$handler, $var);
    }
    public static function setHandler($callable)
    {
        if (null !== $callable && !is_callable($callable, true)) {
            throw new \InvalidArgumentException('Invalid PHP callback.');
        }
        $prevHandler = self::$handler;
        self::$handler = $callable;
        return $prevHandler;
    }
}
