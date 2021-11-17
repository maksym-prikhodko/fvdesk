<?php
namespace Symfony\Component\Security\Core\Util;
use Psr\Log\LoggerInterface;
final class SecureRandom implements SecureRandomInterface
{
    private $logger;
    private $useOpenSsl;
    private $seed;
    private $seedUpdated;
    private $seedLastUpdatedAt;
    private $seedFile;
    public function __construct($seedFile = null, LoggerInterface $logger = null)
    {
        $this->seedFile = $seedFile;
        $this->logger = $logger;
        if ('\\' === DIRECTORY_SEPARATOR && PHP_VERSION_ID < 50304) {
            $this->useOpenSsl = false;
        } elseif (!function_exists('openssl_random_pseudo_bytes')) {
            if (null !== $this->logger) {
                $this->logger->notice('It is recommended that you enable the "openssl" extension for random number generation.');
            }
            $this->useOpenSsl = false;
        } else {
            $this->useOpenSsl = true;
        }
    }
    public function nextBytes($nbBytes)
    {
        if ($this->useOpenSsl) {
            $bytes = openssl_random_pseudo_bytes($nbBytes, $strong);
            if (false !== $bytes && true === $strong) {
                return $bytes;
            }
            if (null !== $this->logger) {
                $this->logger->info('OpenSSL did not produce a secure random number.');
            }
        }
        if (null === $this->seed) {
            if (null === $this->seedFile) {
                throw new \RuntimeException('You need to specify a file path to store the seed.');
            }
            if (is_file($this->seedFile)) {
                list($this->seed, $this->seedLastUpdatedAt) = $this->readSeed();
            } else {
                $this->seed = uniqid(mt_rand(), true);
                $this->updateSeed();
            }
        }
        $bytes = '';
        while (strlen($bytes) < $nbBytes) {
            static $incr = 1;
            $bytes .= hash('sha512', $incr++.$this->seed.uniqid(mt_rand(), true).$nbBytes, true);
            $this->seed = base64_encode(hash('sha512', $this->seed.$bytes.$nbBytes, true));
            $this->updateSeed();
        }
        return substr($bytes, 0, $nbBytes);
    }
    private function readSeed()
    {
        return json_decode(file_get_contents($this->seedFile));
    }
    private function updateSeed()
    {
        if (!$this->seedUpdated && $this->seedLastUpdatedAt < time() - mt_rand(1, 10)) {
            file_put_contents($this->seedFile, json_encode(array($this->seed, microtime(true))));
        }
        $this->seedUpdated = true;
    }
}
