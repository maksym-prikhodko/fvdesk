<?php
namespace PhpParser;
use PhpParser\Builder;
use PhpParser\Node\Stmt\Use_;
class BuilderFactory
{
    protected function _namespace($name) {
        return new Builder\Namespace_($name);
    }
    protected function _class($name) {
        return new Builder\Class_($name);
    }
    protected function _interface($name) {
        return new Builder\Interface_($name);
    }
    protected function _trait($name) {
        return new Builder\Trait_($name);
    }
    public function method($name) {
        return new Builder\Method($name);
    }
    public function param($name) {
        return new Builder\Param($name);
    }
    public function property($name) {
        return new Builder\Property($name);
    }
    protected function _function($name) {
        return new Builder\Function_($name);
    }
    protected function _use($name) {
        return new Builder\Use_($name, Use_::TYPE_NORMAL);
    }
    public function __call($name, array $args) {
        if (method_exists($this, '_' . $name)) {
            return call_user_func_array(array($this, '_' . $name), $args);
        }
        throw new \LogicException(sprintf('Method "%s" does not exist', $name));
    }
}
