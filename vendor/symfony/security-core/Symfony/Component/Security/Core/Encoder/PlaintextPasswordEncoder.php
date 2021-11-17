<?php
namespace Symfony\Component\Security\Core\Encoder;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
class PlaintextPasswordEncoder extends BasePasswordEncoder
{
    private $ignorePasswordCase;
    public function __construct($ignorePasswordCase = false)
    {
        $this->ignorePasswordCase = $ignorePasswordCase;
    }
    public function encodePassword($raw, $salt)
    {
        if ($this->isPasswordTooLong($raw)) {
            throw new BadCredentialsException('Invalid password.');
        }
        return $this->mergePasswordAndSalt($raw, $salt);
    }
    public function isPasswordValid($encoded, $raw, $salt)
    {
        if ($this->isPasswordTooLong($raw)) {
            return false;
        }
        $pass2 = $this->mergePasswordAndSalt($raw, $salt);
        if (!$this->ignorePasswordCase) {
            return $this->comparePasswords($encoded, $pass2);
        }
        return $this->comparePasswords(strtolower($encoded), strtolower($pass2));
    }
}
