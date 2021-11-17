<?php
class PHPUnit_Runner_Version
{
    private static $pharVersion;
    private static $version;
    public static function id()
    {
        if (self::$pharVersion !== null) {
            return self::$pharVersion;
        }
        if (self::$version === null) {
            $version = new SebastianBergmann\Version('4.6.6', dirname(dirname(__DIR__)));
            self::$version = $version->getVersion();
        }
        return self::$version;
    }
    public static function getVersionString()
    {
        return 'PHPUnit ' . self::id() . ' by Sebastian Bergmann and contributors.';
    }
    public static function getReleaseChannel()
    {
        if (strpos(self::$pharVersion, 'alpha') !== false) {
            return '-alpha';
        }
        if (strpos(self::$pharVersion, 'beta') !== false) {
            return '-beta';
        }
        return '';
    }
}
