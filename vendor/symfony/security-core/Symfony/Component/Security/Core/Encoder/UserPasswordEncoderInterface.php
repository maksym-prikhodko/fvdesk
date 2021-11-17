<?php
namespace Symfony\Component\Security\Core\Encoder;
use Symfony\Component\Security\Core\User\UserInterface;
interface UserPasswordEncoderInterface
{
    public function encodePassword(UserInterface $user, $plainPassword);
    public function isPasswordValid(UserInterface $user, $raw);
}
