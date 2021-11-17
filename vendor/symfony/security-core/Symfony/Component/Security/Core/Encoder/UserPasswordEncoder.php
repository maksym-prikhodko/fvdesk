<?php
namespace Symfony\Component\Security\Core\Encoder;
use Symfony\Component\Security\Core\User\UserInterface;
class UserPasswordEncoder implements UserPasswordEncoderInterface
{
    private $encoderFactory;
    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }
    public function encodePassword(UserInterface $user, $plainPassword)
    {
        $encoder = $this->encoderFactory->getEncoder($user);
        return $encoder->encodePassword($plainPassword, $user->getSalt());
    }
    public function isPasswordValid(UserInterface $user, $raw)
    {
        $encoder = $this->encoderFactory->getEncoder($user);
        return $encoder->isPasswordValid($user->getPassword(), $raw, $user->getSalt());
    }
}
