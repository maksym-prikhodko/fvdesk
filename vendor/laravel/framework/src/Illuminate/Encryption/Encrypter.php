<?php namespace Illuminate\Encryption;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Symfony\Component\Security\Core\Util\StringUtils;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Illuminate\Contracts\Encryption\Encrypter as EncrypterContract;
class Encrypter implements EncrypterContract {
	protected $key;
	protected $cipher = MCRYPT_RIJNDAEL_128;
	protected $mode = MCRYPT_MODE_CBC;
	protected $block = 16;
	public function __construct($key)
	{
		$this->key = (string) $key;
	}
	public function encrypt($value)
	{
		$iv = mcrypt_create_iv($this->getIvSize(), $this->getRandomizer());
		$value = base64_encode($this->padAndMcrypt($value, $iv));
		$mac = $this->hash($iv = base64_encode($iv), $value);
		return base64_encode(json_encode(compact('iv', 'value', 'mac')));
	}
	protected function padAndMcrypt($value, $iv)
	{
		$value = $this->addPadding(serialize($value));
		return mcrypt_encrypt($this->cipher, $this->key, $value, $this->mode, $iv);
	}
	public function decrypt($payload)
	{
		$payload = $this->getJsonPayload($payload);
		$value = base64_decode($payload['value']);
		$iv = base64_decode($payload['iv']);
		return unserialize($this->stripPadding($this->mcryptDecrypt($value, $iv)));
	}
	protected function mcryptDecrypt($value, $iv)
	{
		try
		{
			return mcrypt_decrypt($this->cipher, $this->key, $value, $this->mode, $iv);
		}
		catch (Exception $e)
		{
			throw new DecryptException($e->getMessage());
		}
	}
	protected function getJsonPayload($payload)
	{
		$payload = json_decode(base64_decode($payload), true);
		if ( ! $payload || $this->invalidPayload($payload))
		{
			throw new DecryptException('Invalid data.');
		}
		if ( ! $this->validMac($payload))
		{
			throw new DecryptException('MAC is invalid.');
		}
		return $payload;
	}
	protected function validMac(array $payload)
	{
		$bytes = (new SecureRandom)->nextBytes(16);
		$calcMac = hash_hmac('sha256', $this->hash($payload['iv'], $payload['value']), $bytes, true);
		return StringUtils::equals(hash_hmac('sha256', $payload['mac'], $bytes, true), $calcMac);
	}
	protected function hash($iv, $value)
	{
		return hash_hmac('sha256', $iv.$value, $this->key);
	}
	protected function addPadding($value)
	{
		$pad = $this->block - (strlen($value) % $this->block);
		return $value.str_repeat(chr($pad), $pad);
	}
	protected function stripPadding($value)
	{
		$pad = ord($value[($len = strlen($value)) - 1]);
		return $this->paddingIsValid($pad, $value) ? substr($value, 0, $len - $pad) : $value;
	}
	protected function paddingIsValid($pad, $value)
	{
		$beforePad = strlen($value) - $pad;
		return substr($value, $beforePad) == str_repeat(substr($value, -1), $pad);
	}
	protected function invalidPayload($data)
	{
		return ! is_array($data) || ! isset($data['iv']) || ! isset($data['value']) || ! isset($data['mac']);
	}
	protected function getIvSize()
	{
		return mcrypt_get_iv_size($this->cipher, $this->mode);
	}
	protected function getRandomizer()
	{
		if (defined('MCRYPT_DEV_URANDOM')) return MCRYPT_DEV_URANDOM;
		if (defined('MCRYPT_DEV_RANDOM')) return MCRYPT_DEV_RANDOM;
		mt_srand();
		return MCRYPT_RAND;
	}
	public function setKey($key)
	{
		$this->key = (string) $key;
	}
	public function setCipher($cipher)
	{
		$this->cipher = $cipher;
		$this->updateBlockSize();
	}
	public function setMode($mode)
	{
		$this->mode = $mode;
		$this->updateBlockSize();
	}
	protected function updateBlockSize()
	{
		$this->block = mcrypt_get_iv_size($this->cipher, $this->mode);
	}
}
