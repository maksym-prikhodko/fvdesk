<?php
namespace Prophecy\Promise;
use Doctrine\Instantiator\Instantiator;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Exception\InvalidArgumentException;
use ReflectionClass;
class ThrowPromise implements PromiseInterface
{
    private $exception;
    private $instantiator;
    public function __construct($exception)
    {
        if (is_string($exception)) {
            if (!class_exists($exception)
             && 'Exception' !== $exception
             && !is_subclass_of($exception, 'Exception')) {
                throw new InvalidArgumentException(sprintf(
                    'Exception class or instance expected as argument to ThrowPromise, but got %s.',
                    gettype($exception)
                ));
            }
        } elseif (!$exception instanceof \Exception) {
            throw new InvalidArgumentException(sprintf(
                'Exception class or instance expected as argument to ThrowPromise, but got %s.',
                gettype($exception)
            ));
        }
        $this->exception = $exception;
    }
    public function execute(array $args, ObjectProphecy $object, MethodProphecy $method)
    {
        if (is_string($this->exception)) {
            $classname   = $this->exception;
            $reflection  = new ReflectionClass($classname);
            $constructor = $reflection->getConstructor();
            if ($constructor->isPublic() && 0 == $constructor->getNumberOfRequiredParameters()) {
                throw $reflection->newInstance();
            }
            if (!$this->instantiator) {
                $this->instantiator = new Instantiator();
            }
            throw $this->instantiator->instantiate($classname);
        }
        throw $this->exception;
    }
}
