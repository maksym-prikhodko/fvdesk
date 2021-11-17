<?php
class PHPUnit_Util_Blacklist
{
    public static $blacklistedClassNames = array(
        'File_Iterator' => 1,
        'PHP_CodeCoverage' => 1,
        'PHP_Invoker' => 1,
        'PHP_Timer' => 1,
        'PHP_Token' => 1,
        'PHPUnit_Framework_TestCase' => 2,
        'PHPUnit_Extensions_Database_TestCase' => 2,
        'PHPUnit_Framework_MockObject_Generator' => 2,
        'PHPUnit_Extensions_SeleniumTestCase' => 2,
        'PHPUnit_Extensions_Story_TestCase' => 2,
        'Text_Template' => 1,
        'Symfony\Component\Yaml\Yaml' => 1,
        'SebastianBergmann\Diff\Diff' => 1,
        'SebastianBergmann\Environment\Runtime' => 1,
        'SebastianBergmann\Comparator\Comparator' => 1,
        'SebastianBergmann\Exporter\Exporter' => 1,
        'SebastianBergmann\GlobalState\Snapshot' => 1,
        'SebastianBergmann\RecursionContext\Context' => 1,
        'SebastianBergmann\Version' => 1,
        'Composer\Autoload\ClassLoader' => 1,
        'Doctrine\Instantiator\Instantiator' => 1,
        'phpDocumentor\Reflection\DocBlock' => 1,
        'Prophecy\Prophet' => 1
    );
    private static $directories;
    public function getBlacklistedDirectories()
    {
        $this->initialize();
        return self::$directories;
    }
    public function isBlacklisted($file)
    {
        if (defined('PHPUNIT_TESTSUITE')) {
            return false;
        }
        $this->initialize();
        foreach (self::$directories as $directory) {
            if (strpos($file, $directory) === 0) {
                return true;
            }
        }
        return false;
    }
    private function initialize()
    {
        if (self::$directories === null) {
            self::$directories = array();
            foreach (self::$blacklistedClassNames as $className => $parent) {
                if (!class_exists($className)) {
                    continue;
                }
                $reflector = new ReflectionClass($className);
                $directory = $reflector->getFileName();
                for ($i = 0; $i < $parent; $i++) {
                    $directory = dirname($directory);
                }
                self::$directories[] = $directory;
            }
            if (DIRECTORY_SEPARATOR === '\\') {
                self::$directories[] = sys_get_temp_dir() . '\\PHP';
            }
        }
    }
}
