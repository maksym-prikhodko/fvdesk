<?php
class PHP_CodeCoverage_Filter
{
    private $blacklistedFiles = array();
    private $whitelistedFiles = array();
    private $blacklistPrefilled = false;
    public static $blacklistClassNames = array(
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
    public function addDirectoryToBlacklist($directory, $suffix = '.php', $prefix = '')
    {
        $facade = new File_Iterator_Facade;
        $files  = $facade->getFilesAsArray($directory, $suffix, $prefix);
        foreach ($files as $file) {
            $this->addFileToBlacklist($file);
        }
    }
    public function addFileToBlacklist($filename)
    {
        $this->blacklistedFiles[realpath($filename)] = true;
    }
    public function addFilesToBlacklist(array $files)
    {
        foreach ($files as $file) {
            $this->addFileToBlacklist($file);
        }
    }
    public function removeDirectoryFromBlacklist($directory, $suffix = '.php', $prefix = '')
    {
        $facade = new File_Iterator_Facade;
        $files  = $facade->getFilesAsArray($directory, $suffix, $prefix);
        foreach ($files as $file) {
            $this->removeFileFromBlacklist($file);
        }
    }
    public function removeFileFromBlacklist($filename)
    {
        $filename = realpath($filename);
        if (isset($this->blacklistedFiles[$filename])) {
            unset($this->blacklistedFiles[$filename]);
        }
    }
    public function addDirectoryToWhitelist($directory, $suffix = '.php', $prefix = '')
    {
        $facade = new File_Iterator_Facade;
        $files  = $facade->getFilesAsArray($directory, $suffix, $prefix);
        foreach ($files as $file) {
            $this->addFileToWhitelist($file);
        }
    }
    public function addFileToWhitelist($filename)
    {
        $this->whitelistedFiles[realpath($filename)] = true;
    }
    public function addFilesToWhitelist(array $files)
    {
        foreach ($files as $file) {
            $this->addFileToWhitelist($file);
        }
    }
    public function removeDirectoryFromWhitelist($directory, $suffix = '.php', $prefix = '')
    {
        $facade = new File_Iterator_Facade;
        $files  = $facade->getFilesAsArray($directory, $suffix, $prefix);
        foreach ($files as $file) {
            $this->removeFileFromWhitelist($file);
        }
    }
    public function removeFileFromWhitelist($filename)
    {
        $filename = realpath($filename);
        if (isset($this->whitelistedFiles[$filename])) {
            unset($this->whitelistedFiles[$filename]);
        }
    }
    public function isFile($filename)
    {
        if ($filename == '-' ||
            strpos($filename, 'vfs:
            strpos($filename, 'xdebug:
            strpos($filename, 'eval()\'d code') !== false ||
            strpos($filename, 'runtime-created function') !== false ||
            strpos($filename, 'runkit created function') !== false ||
            strpos($filename, 'assert code') !== false ||
            strpos($filename, 'regexp code') !== false) {
            return false;
        }
        return file_exists($filename);
    }
    public function isFiltered($filename)
    {
        if (!$this->isFile($filename)) {
            return true;
        }
        $filename = realpath($filename);
        if (!empty($this->whitelistedFiles)) {
            return !isset($this->whitelistedFiles[$filename]);
        }
        if (!$this->blacklistPrefilled) {
            $this->prefillBlacklist();
        }
        return isset($this->blacklistedFiles[$filename]);
    }
    public function getBlacklist()
    {
        return array_keys($this->blacklistedFiles);
    }
    public function getWhitelist()
    {
        return array_keys($this->whitelistedFiles);
    }
    public function hasWhitelist()
    {
        return !empty($this->whitelistedFiles);
    }
    private function prefillBlacklist()
    {
        if (defined('__PHPUNIT_PHAR__')) {
            $this->addFileToBlacklist(__PHPUNIT_PHAR__);
        }
        foreach (self::$blacklistClassNames as $className => $parent) {
            $this->addDirectoryContainingClassToBlacklist($className, $parent);
        }
        $this->blacklistPrefilled = true;
    }
    private function addDirectoryContainingClassToBlacklist($className, $parent = 1)
    {
        if (!class_exists($className)) {
            return;
        }
        $reflector = new ReflectionClass($className);
        $directory = $reflector->getFileName();
        for ($i = 0; $i < $parent; $i++) {
            $directory = dirname($directory);
        }
        $this->addDirectoryToBlacklist($directory);
    }
    public function getBlacklistedFiles()
    {
        return $this->blacklistedFiles;
    }
    public function setBlacklistedFiles($blacklistedFiles)
    {
        $this->blacklistedFiles = $blacklistedFiles;
    }
    public function getWhitelistedFiles()
    {
        return $this->whitelistedFiles;
    }
    public function setWhitelistedFiles($whitelistedFiles)
    {
        $this->whitelistedFiles = $whitelistedFiles;
    }
}
