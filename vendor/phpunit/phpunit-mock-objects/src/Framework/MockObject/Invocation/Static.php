<?php
use SebastianBergmann\Exporter\Exporter;
class PHPUnit_Framework_MockObject_Invocation_Static implements PHPUnit_Framework_MockObject_Invocation, PHPUnit_Framework_SelfDescribing
{
    protected static $uncloneableExtensions = array(
      'mysqli' => TRUE,
      'SQLite' => TRUE,
      'sqlite3' => TRUE,
      'tidy' => TRUE,
      'xmlwriter' => TRUE,
      'xsl' => TRUE
    );
    protected static $uncloneableClasses = array(
      'Closure',
      'COMPersistHelper',
      'IteratorIterator',
      'RecursiveIteratorIterator',
      'SplFileObject',
      'PDORow',
      'ZipArchive'
    );
    public $className;
    public $methodName;
    public $parameters;
    public function __construct($className, $methodName, array $parameters, $cloneObjects = FALSE)
    {
        $this->className  = $className;
        $this->methodName = $methodName;
        $this->parameters = $parameters;
        if (!$cloneObjects) {
            return;
        }
        foreach ($this->parameters as $key => $value) {
            if (is_object($value)) {
                $this->parameters[$key] = $this->cloneObject($value);
            }
        }
    }
    public function toString()
    {
        $exporter = new Exporter;
        return sprintf(
          "%s::%s(%s)",
          $this->className,
          $this->methodName,
          join(
            ', ',
            array_map(
              array($exporter, 'shortenedExport'),
              $this->parameters
            )
          )
        );
    }
    protected function cloneObject($original)
    {
        $cloneable = NULL;
        $object    = new ReflectionObject($original);
        if ($object->isInternal() &&
            isset(self::$uncloneableExtensions[$object->getExtensionName()])) {
            $cloneable = FALSE;
        }
        if ($cloneable === NULL) {
            foreach (self::$uncloneableClasses as $class) {
                if ($original instanceof $class) {
                    $cloneable = FALSE;
                    break;
                }
            }
        }
        if ($cloneable === NULL && method_exists($object, 'isCloneable')) {
            $cloneable = $object->isCloneable();
        }
        if ($cloneable === NULL && $object->hasMethod('__clone')) {
            $method    = $object->getMethod('__clone');
            $cloneable = $method->isPublic();
        }
        if ($cloneable === NULL) {
            $cloneable = TRUE;
        }
        if ($cloneable) {
            try {
                return clone $original;
            } catch (Exception $e) {
                return $original;
            }
        } else {
            return $original;
        }
    }
}
