<?php
namespace Symfony\Component\Security\Core\Encoder;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
class BCryptPasswordEncoder extends BasePasswordEncoder
{
    private $cost;
    public function __construct($cost)
    {
        if (!function_exists('password_hash')) {
            throw new \RuntimeException('To use the BCrypt encoder, you need to upgrade to PHP 5.5 or install the "ircmaxell/password-compat" via Composer.');
        }
        $cost = (int) $cost;
        if ($cost < 4 || $cost > 31) {
            throw new \InvalidArgumentException('Cost must be in the range of 4-31.');
        }
        $this->cost = $cost;
    }
    public function encodePassword($raw, $salt)
    {
        if ($this->isPasswordTooLong($raw)) {
            throw new BadCredentialsException('Invalid password.');
        }
        $options = array('cost' => $this->cost);
        if ($salt) {
            $options['salt'] = $salt;
        }
        return password_hash($raw, PASSWORD_BCRYPT, $options);
    }
    public function isPasswordValid($encoded, $raw, $salt)
    {
        return !$this->isPasswordTooLong($raw) && password_verify($raw, $encoded);
    }
}
