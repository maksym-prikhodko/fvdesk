<?php
use Illuminate\Contracts\Encryption\Encrypter as EncrypterContract;
class IlluminateQueueClosure {
	protected $crypt;
	public function __construct(EncrypterContract $crypt)
	{
		$this->crypt = $crypt;
	}
	public function fire($job, $data)
	{
		$closure = unserialize($this->crypt->decrypt($data['closure']));
		$closure($job);
	}
}
