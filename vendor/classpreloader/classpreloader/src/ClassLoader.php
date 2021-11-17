<?php
namespace ClassPreloader;
require_once __DIR__ . '/ClassNode.php';
require_once __DIR__ . '/ClassList.php';
class ClassLoader
{
    public $classList;
    public function __construct()
    {
        $this->classList = new ClassList();
    }
    public function __destruct()
    {
        $this->unregister();
    }
    public static function getIncludes($func)
    {
        $loader = new static();
        call_user_func($func, $loader);
        $loader->unregister();
        $config = new Config();
        foreach ($loader->getFilenames() as $file) {
            $config->addFile($file);
        }
        return $config;
    }
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'), true, true);
    }
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }
    public function loadClass($class)
    {
        foreach (spl_autoload_functions() as $func) {
            if (is_array($func) && $func[0] === $this) {
                continue;
            }
            $this->classList->push($class);
            if (call_user_func($func, $class)) {
                break;
            }
        }
        $this->classList->next();
        return true;
    }
    public function getFilenames()
    {
        $files = array();
        foreach ($this->classList->getClasses() as $class) {
            try {
                $r = new \ReflectionClass($class);
                foreach ($r->getInterfaces() as $inf) {
                    $name = $inf->getFileName();
                    if ($name && !in_array($name, $files)) {
                        $files[] = $name;
                    }
                }
                if (!in_array($r->getFileName(), $files)) {
                    $files[] = $r->getFileName();
                }
            } catch (\ReflectionException $e) {
            }
        }
        return $files;
    }
}
