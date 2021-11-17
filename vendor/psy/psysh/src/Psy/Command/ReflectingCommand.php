<?php
namespace Psy\Command;
use Psy\Context;
use Psy\ContextAware;
use Psy\Exception\RuntimeException;
use Psy\Util\Mirror;
abstract class ReflectingCommand extends Command implements ContextAware
{
    const CLASS_OR_FUNC   = '/^[\\\\\w]+$/';
    const INSTANCE        = '/^\$(\w+)$/';
    const CLASS_MEMBER    = '/^([\\\\\w]+)::(\w+)$/';
    const CLASS_STATIC    = '/^([\\\\\w]+)::\$(\w+)$/';
    const INSTANCE_MEMBER = '/^\$(\w+)(::|->)(\w+)$/';
    const INSTANCE_STATIC = '/^\$(\w+)::\$(\w+)$/';
    protected $context;
    public function setContext(Context $context)
    {
        $this->context = $context;
    }
    protected function getTarget($valueName, $classOnly = false)
    {
        $valueName = trim($valueName);
        $matches   = array();
        switch (true) {
            case preg_match(self::CLASS_OR_FUNC, $valueName, $matches):
                return array($this->resolveName($matches[0], true), null, 0);
            case preg_match(self::INSTANCE, $valueName, $matches):
                return array($this->resolveInstance($matches[1]), null, 0);
            case (!$classOnly && preg_match(self::CLASS_MEMBER, $valueName, $matches)):
                return array($this->resolveName($matches[1]), $matches[2], Mirror::CONSTANT | Mirror::METHOD);
            case (!$classOnly && preg_match(self::CLASS_STATIC, $valueName, $matches)):
                return array($this->resolveName($matches[1]), $matches[2], Mirror::STATIC_PROPERTY | Mirror::PROPERTY);
            case (!$classOnly && preg_match(self::INSTANCE_MEMBER, $valueName, $matches)):
                if ($matches[2] === '->') {
                    $kind = Mirror::METHOD | Mirror::PROPERTY;
                } else {
                    $kind = Mirror::CONSTANT | Mirror::METHOD;
                }
                return array($this->resolveInstance($matches[1]), $matches[3], $kind);
            case (!$classOnly && preg_match(self::INSTANCE_STATIC, $valueName, $matches)):
                return array($this->resolveInstance($matches[1]), $matches[2], Mirror::STATIC_PROPERTY);
            default:
                throw new RuntimeException('Unknown target: ' . $valueName);
        }
    }
    protected function resolveName($name, $includeFunctions = false)
    {
        if (substr($name, 0, 1) === '\\') {
            return $name;
        }
        if ($namespace = $this->getApplication()->getNamespace()) {
            $fullName = $namespace . '\\' . $name;
            if (class_exists($fullName) || interface_exists($fullName) || ($includeFunctions && function_exists($fullName))) {
                return $fullName;
            }
        }
        return $name;
    }
    protected function getTargetAndReflector($valueName, $classOnly = false)
    {
        list($value, $member, $kind) = $this->getTarget($valueName, $classOnly);
        return array($value, Mirror::get($value, $member, $kind));
    }
    protected function resolveInstance($name)
    {
        $value = $this->getScopeVariable($name);
        if (!is_object($value)) {
            throw new RuntimeException('Unable to inspect a non-object');
        }
        return $value;
    }
    protected function getScopeVariable($name)
    {
        return $this->context->get($name);
    }
    protected function getScopeVariables()
    {
        return $this->context->getAll();
    }
}
