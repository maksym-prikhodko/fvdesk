<?php
namespace Symfony\Component\Security\Core\Encoder;
class EncoderFactory implements EncoderFactoryInterface
{
    private $encoders;
    public function __construct(array $encoders)
    {
        $this->encoders = $encoders;
    }
    public function getEncoder($user)
    {
        $encoderKey = null;
        if ($user instanceof EncoderAwareInterface && (null !== $encoderName = $user->getEncoderName())) {
            if (!array_key_exists($encoderName, $this->encoders)) {
                throw new \RuntimeException(sprintf('The encoder "%s" was not configured.', $encoderName));
            }
            $encoderKey = $encoderName;
        } else {
            foreach ($this->encoders as $class => $encoder) {
                if ((is_object($user) && $user instanceof $class) || (!is_object($user) && (is_subclass_of($user, $class) || $user == $class))) {
                    $encoderKey = $class;
                    break;
                }
            }
        }
        if (null === $encoderKey) {
            throw new \RuntimeException(sprintf('No encoder has been configured for account "%s".', is_object($user) ? get_class($user) : $user));
        }
        if (!$this->encoders[$encoderKey] instanceof PasswordEncoderInterface) {
            $this->encoders[$encoderKey] = $this->createEncoder($this->encoders[$encoderKey]);
        }
        return $this->encoders[$encoderKey];
    }
    private function createEncoder(array $config)
    {
        if (!isset($config['class'])) {
            throw new \InvalidArgumentException(sprintf('"class" must be set in %s.', json_encode($config)));
        }
        if (!isset($config['arguments'])) {
            throw new \InvalidArgumentException(sprintf('"arguments" must be set in %s.', json_encode($config)));
        }
        $reflection = new \ReflectionClass($config['class']);
        return $reflection->newInstanceArgs($config['arguments']);
    }
}
