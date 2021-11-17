<?php
namespace SebastianBergmann\Environment;
class Runtime
{
    private static $binary;
    public function canCollectCodeCoverage()
    {
        return $this->isHHVM() || $this->hasXdebug();
    }
    public function getBinary()
    {
        if (self::$binary === null && $this->isHHVM()) {
            if ((self::$binary = getenv('PHP_BINARY')) === false) {
                self::$binary = PHP_BINARY;
            }
            self::$binary = escapeshellarg(self::$binary) . ' --php';
        }
        if (self::$binary === null && defined('PHP_BINARY')) {
            self::$binary = escapeshellarg(PHP_BINARY);
        }
        if (self::$binary === null) {
            if (PHP_SAPI == 'cli' && isset($_SERVER['_'])) {
                if (strpos($_SERVER['_'], 'phpunit') !== false) {
                    $file = file($_SERVER['_']);
                    if (strpos($file[0], ' ') !== false) {
                        $tmp = explode(' ', $file[0]);
                        self::$binary = escapeshellarg(trim($tmp[1]));
                    } else {
                        self::$binary = escapeshellarg(ltrim(trim($file[0]), '#!'));
                    }
                } elseif (strpos(basename($_SERVER['_']), 'php') !== false) {
                    self::$binary = escapeshellarg($_SERVER['_']);
                }
            }
        }
        if (self::$binary === null) {
            $possibleBinaryLocations = array(
                PHP_BINDIR . '/php',
                PHP_BINDIR . '/php-cli.exe',
                PHP_BINDIR . '/php.exe'
            );
            foreach ($possibleBinaryLocations as $binary) {
                if (is_readable($binary)) {
                    self::$binary = escapeshellarg($binary);
                    break;
                }
            }
        }
        if (self::$binary === null) {
            self::$binary = 'php';
        }
        return self::$binary;
    }
    public function getNameWithVersion()
    {
        return $this->getName() . ' ' . $this->getVersion();
    }
    public function getName()
    {
        if ($this->isHHVM()) {
            return 'HHVM';
        } else {
            return 'PHP';
        }
    }
    public function getVendorUrl()
    {
        if ($this->isHHVM()) {
            return 'http:
        } else {
            return 'http:
        }
    }
    public function getVersion()
    {
        if ($this->isHHVM()) {
            return HHVM_VERSION;
        } else {
            return PHP_VERSION;
        }
    }
    public function hasXdebug()
    {
        return $this->isPHP() && extension_loaded('xdebug');
    }
    public function isHHVM()
    {
        return defined('HHVM_VERSION');
    }
    public function isPHP()
    {
        return !$this->isHHVM();
    }
}
