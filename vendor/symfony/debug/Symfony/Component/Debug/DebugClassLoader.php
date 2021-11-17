<?php
namespace Symfony\Component\Debug;
class DebugClassLoader
{
    private $classLoader;
    private $isFinder;
    private $wasFinder;
    private static $caseCheck;
    public function __construct($classLoader)
    {
        $this->wasFinder = is_object($classLoader) && method_exists($classLoader, 'findFile');
        if ($this->wasFinder) {
            $this->classLoader = array($classLoader, 'loadClass');
            $this->isFinder = true;
        } else {
            $this->classLoader = $classLoader;
            $this->isFinder = is_array($classLoader) && method_exists($classLoader[0], 'findFile');
        }
        if (!isset(self::$caseCheck)) {
            self::$caseCheck = false !== stripos(PHP_OS, 'win') ? (false !== stripos(PHP_OS, 'darwin') ? 2 : 1) : 0;
        }
    }
    public function getClassLoader()
    {
        return $this->wasFinder ? $this->classLoader[0] : $this->classLoader;
    }
    public static function enable()
    {
        class_exists('Symfony\Component\Debug\ErrorHandler');
        class_exists('Psr\Log\LogLevel');
        if (!is_array($functions = spl_autoload_functions())) {
            return;
        }
        foreach ($functions as $function) {
            spl_autoload_unregister($function);
        }
        foreach ($functions as $function) {
            if (!is_array($function) || !$function[0] instanceof self) {
                $function = array(new static($function), 'loadClass');
            }
            spl_autoload_register($function);
        }
    }
    public static function disable()
    {
        if (!is_array($functions = spl_autoload_functions())) {
            return;
        }
        foreach ($functions as $function) {
            spl_autoload_unregister($function);
        }
        foreach ($functions as $function) {
            if (is_array($function) && $function[0] instanceof self) {
                $function = $function[0]->getClassLoader();
            }
            spl_autoload_register($function);
        }
    }
    public function findFile($class)
    {
        if ($this->wasFinder) {
            return $this->classLoader[0]->findFile($class);
        }
    }
    public function loadClass($class)
    {
        ErrorHandler::stackErrors();
        try {
            if ($this->isFinder) {
                if ($file = $this->classLoader[0]->findFile($class)) {
                    require $file;
                }
            } else {
                call_user_func($this->classLoader, $class);
                $file = false;
            }
        } catch (\Exception $e) {
            ErrorHandler::unstackErrors();
            throw $e;
        }
        ErrorHandler::unstackErrors();
        $exists = class_exists($class, false) || interface_exists($class, false) || (function_exists('trait_exists') && trait_exists($class, false));
        if ('\\' === $class[0]) {
            $class = substr($class, 1);
        }
        if ($exists) {
            $refl = new \ReflectionClass($class);
            $name = $refl->getName();
            if ($name !== $class && 0 === strcasecmp($name, $class)) {
                throw new \RuntimeException(sprintf('Case mismatch between loaded and declared class names: %s vs %s', $class, $name));
            }
        }
        if ($file) {
            if (!$exists) {
                if (false !== strpos($class, '/')) {
                    throw new \RuntimeException(sprintf('Trying to autoload a class with an invalid name "%s". Be careful that the namespace separator is "\" in PHP, not "/".', $class));
                }
                throw new \RuntimeException(sprintf('The autoloader expected class "%s" to be defined in file "%s". The file was found but the class was not in it, the class name or namespace probably has a typo.', $class, $file));
            }
            if (self::$caseCheck && preg_match('#([/\\\\][a-zA-Z_\x7F-\xFF][a-zA-Z0-9_\x7F-\xFF]*)+\.(php|hh)$#D', $file, $tail)) {
                $tail = $tail[0];
                $real = $refl->getFilename();
                if (2 === self::$caseCheck) {
                    $cwd = getcwd();
                    $basename = strrpos($real, '/');
                    chdir(substr($real, 0, $basename));
                    $basename = substr($real, $basename + 1);
                    if (!in_array($basename, glob($basename.'*', GLOB_NOSORT), true)) {
                        $real = getcwd().'/';
                        $h = opendir('.');
                        while (false !== $f = readdir($h)) {
                            if (0 === strcasecmp($f, $basename)) {
                                $real .= $f;
                                break;
                            }
                        }
                        closedir($h);
                    }
                    chdir($cwd);
                }
                if (0 === substr_compare($real, $tail, -strlen($tail), strlen($tail), true)
                  && 0 !== substr_compare($real, $tail, -strlen($tail), strlen($tail), false)
                ) {
                    throw new \RuntimeException(sprintf('Case mismatch between class and source file names: %s vs %s', $class, $real));
                }
            }
            return true;
        }
    }
}
