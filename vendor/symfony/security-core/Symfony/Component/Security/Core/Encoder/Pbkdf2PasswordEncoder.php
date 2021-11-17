<?php
namespace Symfony\Component\Security\Core\Encoder;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
class Pbkdf2PasswordEncoder extends BasePasswordEncoder
{
    private $algorithm;
    private $encodeHashAsBase64;
    private $iterations;
    private $length;
    public function __construct($algorithm = 'sha512', $encodeHashAsBase64 = true, $iterations = 1000, $length = 40)
    {
        $this->algorithm = $algorithm;
        $this->encodeHashAsBase64 = $encodeHashAsBase64;
        $this->iterations = $iterations;
        $this->length = $length;
    }
    public function encodePassword($raw, $salt)
    {
        if ($this->isPasswordTooLong($raw)) {
            throw new BadCredentialsException('Invalid password.');
        }
        if (!in_array($this->algorithm, hash_algos(), true)) {
            throw new \LogicException(sprintf('The algorithm "%s" is not supported.', $this->algorithm));
        }
        if (function_exists('hash_pbkdf2')) {
            $digest = hash_pbkdf2($this->algorithm, $raw, $salt, $this->iterations, $this->length, true);
        } else {
            $digest = $this->hashPbkdf2($this->algorithm, $raw, $salt, $this->iterations, $this->length);
        }
        return $this->encodeHashAsBase64 ? base64_encode($digest) : bin2hex($digest);
    }
    public function isPasswordValid($encoded, $raw, $salt)
    {
        return !$this->isPasswordTooLong($raw) && $this->comparePasswords($encoded, $this->encodePassword($raw, $salt));
    }
    private function hashPbkdf2($algorithm, $password, $salt, $iterations, $length = 0)
    {
        $blocks = ceil($length / strlen(hash($algorithm, null, true)));
        $digest = '';
        for ($i = 1; $i <= $blocks; $i++) {
            $ib = $block = hash_hmac($algorithm, $salt.pack('N', $i), $password, true);
            for ($j = 1; $j < $iterations; $j++) {
                $ib ^= ($block = hash_hmac($algorithm, $block, $password, true));
            }
            $digest .= $ib;
        }
        return substr($digest, 0, $this->length);
    }
}
