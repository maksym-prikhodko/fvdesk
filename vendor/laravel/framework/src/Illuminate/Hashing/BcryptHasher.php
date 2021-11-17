<?php namespace Illuminate\Hashing;
use RuntimeException;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
class BcryptHasher implements HasherContract {
	protected $rounds = 10;
	public function make($value, array $options = array())
	{
		$cost = isset($options['rounds']) ? $options['rounds'] : $this->rounds;
		$hash = password_hash($value, PASSWORD_BCRYPT, array('cost' => $cost));
		if ($hash === false)
		{
			throw new RuntimeException("Bcrypt hashing not supported.");
		}
		return $hash;
	}
	public function check($value, $hashedValue, array $options = array())
	{
		return password_verify($value, $hashedValue);
	}
	public function needsRehash($hashedValue, array $options = array())
	{
		$cost = isset($options['rounds']) ? $options['rounds'] : $this->rounds;
		return password_needs_rehash($hashedValue, PASSWORD_BCRYPT, array('cost' => $cost));
	}
	public function setRounds($rounds)
	{
		$this->rounds = (int) $rounds;
		return $this;
	}
}
