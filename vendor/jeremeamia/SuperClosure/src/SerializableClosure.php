<?php namespace SuperClosure;
use SuperClosure\Exception\ClosureUnserializationException;
class SerializableClosure implements \Serializable
{
    private $closure;
    private $serializer;
    private $data;
    public function __construct(
        \Closure $closure,
        SerializerInterface $serializer = null
    ) {
        $this->closure = $closure;
        $this->serializer = $serializer ?: new Serializer;
    }
    public function getClosure()
    {
        return $this->closure;
    }
    public function __invoke()
    {
        return call_user_func_array($this->closure, func_get_args());
    }
    public function serialize()
    {
        try {
            $this->data = $this->data ?: $this->serializer->getData($this->closure, true);
            return serialize($this->data);
        } catch (\Exception $e) {
            trigger_error(
                'Serialization of closure failed: ' . $e->getMessage(),
                E_USER_NOTICE
            );
            return null;
        }
    }
    public function unserialize($serialized)
    {
        $this->data = unserialize($serialized);
        $this->reconstructClosure();
        if (!$this->closure instanceof \Closure) {
            throw new ClosureUnserializationException(
                'The closure is corrupted and cannot be unserialized.'
            );
        }
        if (!$this->data['isStatic']) {
            $this->closure = $this->closure->bindTo(
                $this->data['binding'],
                $this->data['scope']
            );
        }
    }
    private function reconstructClosure()
    {
        extract($this->data['context'], EXTR_OVERWRITE);
        if ($_fn = array_search(Serializer::RECURSION, $this->data['context'], true)) {
            @eval("\${$_fn} = {$this->data['code']};");
            $this->closure = $$_fn;
        } else {
            @eval("\$this->closure = {$this->data['code']};");
        }
    }
    public function __debugInfo()
    {
        return $this->data ?: $this->serializer->getData($this->closure, true);
    }
}
