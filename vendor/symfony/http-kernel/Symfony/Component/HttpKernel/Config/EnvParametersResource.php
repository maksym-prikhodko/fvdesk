<?php
namespace Symfony\Component\HttpKernel\Config;
use Symfony\Component\Config\Resource\ResourceInterface;
class EnvParametersResource implements ResourceInterface, \Serializable
{
    private $prefix;
    private $variables;
    public function __construct($prefix)
    {
        $this->prefix = $prefix;
        $this->variables = $this->findVariables();
    }
    public function __toString()
    {
        return serialize($this->getResource());
    }
    public function getResource()
    {
        return array('prefix' => $this->prefix, 'variables' => $this->variables);
    }
    public function isFresh($timestamp)
    {
        return $this->findVariables() === $this->variables;
    }
    public function serialize()
    {
        return serialize(array('prefix' => $this->prefix, 'variables' => $this->variables));
    }
    public function unserialize($serialized)
    {
        $unserialized = unserialize($serialized);
        $this->prefix = $unserialized['prefix'];
        $this->variables = $unserialized['variables'];
    }
    private function findVariables()
    {
        $variables = array();
        foreach ($_SERVER as $key => $value) {
            if (0 === strpos($key, $this->prefix)) {
                $variables[$key] = $value;
            }
        }
        ksort($variables);
        return $variables;
    }
}
