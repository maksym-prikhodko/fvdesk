<?php
class Issue1340Test extends PHPUnit_Framework_TestCase
{
    private static function get4KB()
    {
        return str_repeat('1', 4096 + 1);
    }
    public function testLargeStderrOutputDoesNotBlock()
    {
        error_log("\n" . __FUNCTION__ . ": stderr:" . self::get4KB() . "\n");
        $this->assertTrue(true);
    }
    public function testLargeStderrOutputDoesNotBlockInIsolation()
    {
        error_log("\n" . __FUNCTION__ . ": stderr:" . self::get4KB() . "\n");
        $this->assertTrue(true);
    }
    public function testPhpNoticeIsCaught()
    {
        $bar = $foo['foo'];
    }
    public function testPhpNoticeWithStderrOutputIsAnError()
    {
        register_shutdown_function(__CLASS__ . '::onShutdown');
        $bar = $foo['foo'];
    }
    public function testFatalErrorDoesNotPass()
    {
        register_shutdown_function(__CLASS__ . '::onShutdown');
        $undefined = 'undefined_function';
        $undefined();
    }
    public static function onShutdown()
    {
        echo "\nshutdown: stdout:", self::get4KB(), "\n";
        error_log("\nshutdown: stderr:" . self::get4KB());
    }
}
