<?php namespace SuperClosure;
use SuperClosure\Analyzer\AstAnalyzer as DefaultAnalyzer;
use SuperClosure\Analyzer\ClosureAnalyzer;
use SuperClosure\Exception\ClosureUnserializationException;
class Serializer implements SerializerInterface
{
    const RECURSION = "{{RECURSION}}";
    private static $dataToKeep = [
        'code'     => true,
        'context'  => true,
        'binding'  => true,
        'scope'    => true,
        'isStatic' => true,
    ];
    private $analyzer;
    private $signingKey;
    public function __construct(
        ClosureAnalyzer $analyzer = null,
        $signingKey = null
    ) {
        $this->analyzer = $analyzer ?: new DefaultAnalyzer;
        $this->signingKey = $signingKey;
    }
    public function serialize(\Closure $closure)
    {
        $serialized = serialize(new SerializableClosure($closure, $this));
        if ($this->signingKey) {
            $signature = $this->calculateSignature($serialized);
            $serialized = '%' . base64_encode($signature) . $serialized;
        }
        return $serialized;
    }
    public function unserialize($serialized)
    {
        $signature = null;
        if ($serialized[0] === '%') {
            $signature = base64_decode(substr($serialized, 1, 44));
            $serialized = substr($serialized, 45);
        }
        if ($this->signingKey) {
            $this->verifySignature($signature, $serialized);
        }
        $unserialized = unserialize($serialized);
        return $unserialized->getClosure();
    }
    public function getData(\Closure $closure, $forSerialization = false)
    {
        $data = $this->analyzer->analyze($closure);
        if ($forSerialization) {
            if (!$data['hasThis']) {
                $data['binding'] = null;
            }
            $data = array_intersect_key($data, self::$dataToKeep);
            foreach ($data['context'] as &$value) {
                if ($value instanceof \Closure) {
                    $value = ($value === $closure)
                        ? self::RECURSION
                        : new SerializableClosure($value, $this);
                }
            }
        }
        return $data;
    }
    public static function wrapClosures(&$data, SerializerInterface $serializer)
    {
        if ($data instanceof \Closure) {
            $reflection = new \ReflectionFunction($data);
            if ($binding = $reflection->getClosureThis()) {
                self::wrapClosures($binding, $serializer);
                $scope = $reflection->getClosureScopeClass();
                $scope = $scope ? $scope->getName() : 'static';
                $data = $data->bindTo($binding, $scope);
            }
            $data = new SerializableClosure($data, $serializer);
        } elseif (is_array($data) || $data instanceof \stdClass || $data instanceof \Traversable) {
            foreach ($data as &$value) {
                self::wrapClosures($value, $serializer);
            }
        } elseif (is_object($data) && !$data instanceof \Serializable) {
            $reflection = new \ReflectionObject($data);
            if (!$reflection->hasMethod('__sleep')) {
                foreach ($reflection->getProperties() as $property) {
                    if ($property->isPrivate() || $property->isProtected()) {
                        $property->setAccessible(true);
                    }
                    $value = $property->getValue($data);
                    self::wrapClosures($value, $serializer);
                    $property->setValue($data, $value);
                }
            }
        }
    }
    private function calculateSignature($data)
    {
        return hash_hmac('sha256', $data, $this->signingKey, true);
    }
    private function verifySignature($signature, $data)
    {
        static $hashEqualsFnExists = false;
        if (!$hashEqualsFnExists) {
            require __DIR__ . '/hash_equals.php';
            $hashEqualsFnExists = true;
        }
        if (!hash_equals($signature, $this->calculateSignature($data))) {
            throw new ClosureUnserializationException('The signature of the'
                . ' closure\'s data is invalid, which means the serialized '
                . 'closure has been modified and is unsafe to unserialize.'
            );
        }
    }
}
