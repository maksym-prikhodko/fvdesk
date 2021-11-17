<?php
namespace Symfony\Component\VarDumper\Cloner;
use Symfony\Component\VarDumper\Exception\ThrowingCasterException;
abstract class AbstractCloner implements ClonerInterface
{
    public static $defaultCasters = array(
        'Symfony\Component\VarDumper\Caster\CutStub' => 'Symfony\Component\VarDumper\Caster\StubCaster::castStub',
        'Symfony\Component\VarDumper\Caster\ConstStub' => 'Symfony\Component\VarDumper\Caster\StubCaster::castStub',
        'Closure' => 'Symfony\Component\VarDumper\Caster\ReflectionCaster::castClosure',
        'Reflector' => 'Symfony\Component\VarDumper\Caster\ReflectionCaster::castReflector',
        'Doctrine\Common\Persistence\ObjectManager' => 'Symfony\Component\VarDumper\Caster\StubCaster::cutInternals',
        'Doctrine\Common\Proxy\Proxy' => 'Symfony\Component\VarDumper\Caster\DoctrineCaster::castCommonProxy',
        'Doctrine\ORM\Proxy\Proxy' => 'Symfony\Component\VarDumper\Caster\DoctrineCaster::castOrmProxy',
        'Doctrine\ORM\PersistentCollection' => 'Symfony\Component\VarDumper\Caster\DoctrineCaster::castPersistentCollection',
        'DOMException' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castException',
        'DOMStringList' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castLength',
        'DOMNameList' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castLength',
        'DOMImplementation' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castImplementation',
        'DOMImplementationList' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castLength',
        'DOMNode' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castNode',
        'DOMNameSpaceNode' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castNameSpaceNode',
        'DOMDocument' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castDocument',
        'DOMNodeList' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castLength',
        'DOMNamedNodeMap' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castLength',
        'DOMCharacterData' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castCharacterData',
        'DOMAttr' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castAttr',
        'DOMElement' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castElement',
        'DOMText' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castText',
        'DOMTypeinfo' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castTypeinfo',
        'DOMDomError' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castDomError',
        'DOMLocator' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castLocator',
        'DOMDocumentType' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castDocumentType',
        'DOMNotation' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castNotation',
        'DOMEntity' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castEntity',
        'DOMProcessingInstruction' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castProcessingInstruction',
        'DOMXPath' => 'Symfony\Component\VarDumper\Caster\DOMCaster::castXPath',
        'ErrorException' => 'Symfony\Component\VarDumper\Caster\ExceptionCaster::castErrorException',
        'Exception' => 'Symfony\Component\VarDumper\Caster\ExceptionCaster::castException',
        'Symfony\Component\DependencyInjection\ContainerInterface' => 'Symfony\Component\VarDumper\Caster\StubCaster::cutInternals',
        'Symfony\Component\VarDumper\Exception\ThrowingCasterException' => 'Symfony\Component\VarDumper\Caster\ExceptionCaster::castThrowingCasterException',
        'PDO' => 'Symfony\Component\VarDumper\Caster\PdoCaster::castPdo',
        'PDOStatement' => 'Symfony\Component\VarDumper\Caster\PdoCaster::castPdoStatement',
        'ArrayObject' => 'Symfony\Component\VarDumper\Caster\SplCaster::castArrayObject',
        'SplDoublyLinkedList' => 'Symfony\Component\VarDumper\Caster\SplCaster::castDoublyLinkedList',
        'SplFixedArray' => 'Symfony\Component\VarDumper\Caster\SplCaster::castFixedArray',
        'SplHeap' => 'Symfony\Component\VarDumper\Caster\SplCaster::castHeap',
        'SplObjectStorage' => 'Symfony\Component\VarDumper\Caster\SplCaster::castObjectStorage',
        'SplPriorityQueue' => 'Symfony\Component\VarDumper\Caster\SplCaster::castHeap',
        ':curl' => 'Symfony\Component\VarDumper\Caster\ResourceCaster::castCurl',
        ':dba' => 'Symfony\Component\VarDumper\Caster\ResourceCaster::castDba',
        ':dba persistent' => 'Symfony\Component\VarDumper\Caster\ResourceCaster::castDba',
        ':gd' => 'Symfony\Component\VarDumper\Caster\ResourceCaster::castGd',
        ':mysql link' => 'Symfony\Component\VarDumper\Caster\ResourceCaster::castMysqlLink',
        ':process' => 'Symfony\Component\VarDumper\Caster\ResourceCaster::castProcess',
        ':stream' => 'Symfony\Component\VarDumper\Caster\ResourceCaster::castStream',
        ':stream-context' => 'Symfony\Component\VarDumper\Caster\ResourceCaster::castStreamContext',
    );
    protected $maxItems = 2500;
    protected $maxString = -1;
    protected $useExt;
    private $casters = array();
    private $prevErrorHandler;
    private $classInfo = array();
    public function __construct(array $casters = null)
    {
        if (null === $casters) {
            $casters = static::$defaultCasters;
        }
        $this->addCasters($casters);
        $this->useExt = extension_loaded('symfony_debug');
    }
    public function addCasters(array $casters)
    {
        foreach ($casters as $type => $callback) {
            $this->casters[strtolower($type)][] = $callback;
        }
    }
    public function setMaxItems($maxItems)
    {
        $this->maxItems = (int) $maxItems;
    }
    public function setMaxString($maxString)
    {
        $this->maxString = (int) $maxString;
    }
    public function cloneVar($var)
    {
        $this->prevErrorHandler = set_error_handler(array($this, 'handleError'));
        try {
            if (!function_exists('iconv')) {
                $this->maxString = -1;
            }
            $data = $this->doClone($var);
        } catch (\Exception $e) {
        }
        restore_error_handler();
        $this->prevErrorHandler = null;
        if (isset($e)) {
            throw $e;
        }
        return new Data($data);
    }
    abstract protected function doClone($var);
    protected function castObject(Stub $stub, $isNested)
    {
        $obj = $stub->value;
        $class = $stub->class;
        if (isset($this->classInfo[$class])) {
            $classInfo = $this->classInfo[$class];
            $stub->class = $classInfo[0];
        } else {
            $classInfo = array(
                $class,
                method_exists($class, '__debugInfo'),
                new \ReflectionClass($class),
                array_reverse(array($class => $class) + class_parents($class) + class_implements($class)),
            );
            $this->classInfo[$class] = $classInfo;
        }
        if ($classInfo[1]) {
            $a = $this->callCaster(function ($obj) {return $obj->__debugInfo();}, $obj, array(), null, $isNested);
        } else {
            $a = (array) $obj;
        }
        if ($a) {
            $p = array_keys($a);
            foreach ($p as $i => $k) {
                if (!isset($k[0]) || ("\0" !== $k[0] && !$classInfo[2]->hasProperty($k))) {
                    $p[$i] = "\0+\0".$k;
                }
            }
            $a = array_combine($p, $a);
        }
        foreach ($classInfo[3] as $p) {
            if (!empty($this->casters[$p = strtolower($p)])) {
                foreach ($this->casters[$p] as $p) {
                    $a = $this->callCaster($p, $obj, $a, $stub, $isNested);
                }
            }
        }
        return $a;
    }
    protected function castResource(Stub $stub, $isNested)
    {
        $a = array();
        $res = $stub->value;
        $type = $stub->class;
        if (!empty($this->casters[':'.$type])) {
            foreach ($this->casters[':'.$type] as $c) {
                $a = $this->callCaster($c, $res, $a, $stub, $isNested);
            }
        }
        return $a;
    }
    private function callCaster($callback, $obj, $a, $stub, $isNested)
    {
        try {
            $cast = call_user_func($callback, $obj, $a, $stub, $isNested);
            if (is_array($cast)) {
                $a = $cast;
            }
        } catch (\Exception $e) {
            $a[(Stub::TYPE_OBJECT === $stub->type ? "\0~\0" : '').'⚠'] = new ThrowingCasterException($callback, $e);
        }
        return $a;
    }
    public function handleError($type, $msg, $file, $line, $context)
    {
        if (E_RECOVERABLE_ERROR === $type || E_USER_ERROR === $type) {
            throw new \ErrorException($msg, 0, $type, $file, $line);
        }
        if ($this->prevErrorHandler) {
            return call_user_func($this->prevErrorHandler, $type, $msg, $file, $line, $context);
        }
        return false;
    }
}
