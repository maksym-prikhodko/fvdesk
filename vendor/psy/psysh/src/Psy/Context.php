<?php
namespace Psy;
class Context
{
    private static $specialVars = array('_', '_e', '__psysh__');
    private $scopeVariables = array();
    private $lastException;
    private $returnValue;
    public function get($name)
    {
        switch ($name) {
            case '_':
                return $this->returnValue;
            case '_e':
                if (!isset($this->lastException)) {
                    throw new \InvalidArgumentException('Unknown variable: $' . $name);
                }
                return $this->lastException;
            default:
                if (!array_key_exists($name, $this->scopeVariables)) {
                    throw new \InvalidArgumentException('Unknown variable: $' . $name);
                }
                return $this->scopeVariables[$name];
        }
    }
    public function getAll()
    {
        $vars = $this->scopeVariables;
        $vars['_'] = $this->returnValue;
        if (isset($this->lastException)) {
            $vars['_e'] = $this->lastException;
        }
        return $vars;
    }
    public function setAll(array $vars)
    {
        foreach (self::$specialVars as $key) {
            unset($vars[$key]);
        }
        $this->scopeVariables = $vars;
    }
    public function setReturnValue($value)
    {
        $this->returnValue = $value;
    }
    public function getReturnValue()
    {
        return $this->returnValue;
    }
    public function setLastException(\Exception $e)
    {
        $this->lastException = $e;
    }
    public function getLastException()
    {
        if (!isset($this->lastException)) {
            throw new \InvalidArgumentException('No most-recent exception');
        }
        return $this->lastException;
    }
}
