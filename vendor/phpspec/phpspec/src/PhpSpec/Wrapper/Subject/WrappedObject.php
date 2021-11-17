<?php
namespace PhpSpec\Wrapper\Subject;
use PhpSpec\Exception\Fracture\FactoryDoesNotReturnObjectException;
use PhpSpec\Formatter\Presenter\PresenterInterface;
use PhpSpec\Wrapper\Unwrapper;
use PhpSpec\Exception\Wrapper\SubjectException;
class WrappedObject
{
    private $instance;
    private $presenter;
    private $classname;
    private $factoryMethod;
    private $arguments = array();
    private $isInstantiated = false;
    public function __construct($instance, PresenterInterface $presenter)
    {
        $this->instance = $instance;
        $this->presenter = $presenter;
        if (is_object($this->instance)) {
            $this->classname = get_class($this->instance);
            $this->isInstantiated = true;
        }
    }
    public function beAnInstanceOf($classname, array $arguments = array())
    {
        if (!is_string($classname)) {
            throw new SubjectException(sprintf(
                'Behavior subject classname should be a string, %s given.',
                $this->presenter->presentValue($classname)
            ));
        }
        $this->classname      = $classname;
        $unwrapper            = new Unwrapper();
        $this->arguments      = $unwrapper->unwrapAll($arguments);
        $this->isInstantiated = false;
        $this->factoryMethod  = null;
    }
    public function beConstructedWith($args)
    {
        if (null === $this->classname) {
            throw new SubjectException(sprintf(
                'You can not set object arguments. Behavior subject is %s.',
                $this->presenter->presentValue(null)
            ));
        }
        if ($this->isInstantiated()) {
            throw new SubjectException('You can not change object construction method when it is already instantiated');
        }
        $this->beAnInstanceOf($this->classname, $args);
    }
    public function beConstructedThrough($factoryMethod, array $arguments = array())
    {
        if (is_string($factoryMethod) &&
            false === strpos($factoryMethod, '::') &&
            method_exists($this->classname, $factoryMethod)
        ) {
            $factoryMethod = array($this->classname, $factoryMethod);
        }
        if ($this->isInstantiated()) {
            throw new SubjectException('You can not change object construction method when it is already instantiated');
        }
        $this->factoryMethod = $factoryMethod;
        $unwrapper           = new Unwrapper();
        $this->arguments     = $unwrapper->unwrapAll($arguments);
    }
    public function getFactoryMethod()
    {
        return $this->factoryMethod;
    }
    public function isInstantiated()
    {
        return $this->isInstantiated;
    }
    public function setInstantiated($instantiated)
    {
        $this->isInstantiated = $instantiated;
    }
    public function getClassName()
    {
        return $this->classname;
    }
    public function setClassName($classname)
    {
        $this->classname = $classname;
    }
    public function getArguments()
    {
        return $this->arguments;
    }
    public function getInstance()
    {
        return $this->instance;
    }
    public function setInstance($instance)
    {
        $this->instance = $instance;
    }
    public function instantiate()
    {
        if ($this->isInstantiated()) {
            return $this->instance;
        }
        if ($this->factoryMethod) {
            $this->instance = $this->instantiateFromCallback($this->factoryMethod);
        } else {
            $reflection = new \ReflectionClass($this->classname);
            $this->instance = empty($this->arguments) ?
                $reflection->newInstance() :
                $reflection->newInstanceArgs($this->arguments);
        }
        $this->isInstantiated = true;
        return $this->instance;
    }
    private function instantiateFromCallback($factoryCallable)
    {
        $instance = call_user_func_array($factoryCallable, $this->arguments);
        if (!is_object($instance)) {
            throw new FactoryDoesNotReturnObjectException(sprintf(
                'The method %s::%s did not return an object, returned %s instead',
                $this->factoryMethod[0],
                $this->factoryMethod[1],
                gettype($instance)
            ));
        }
        return $instance;
    }
}
