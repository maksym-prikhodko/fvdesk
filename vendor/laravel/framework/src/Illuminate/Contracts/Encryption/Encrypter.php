<?php namespace Illuminate\Contracts\Encryption;
interface Encrypter {
	public function encrypt($value);
	public function decrypt($payload);
	public function setMode($mode);
	public function setCipher($cipher);
}
