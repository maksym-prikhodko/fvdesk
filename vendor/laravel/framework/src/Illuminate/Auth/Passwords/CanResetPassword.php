<?php namespace Illuminate\Auth\Passwords;
trait CanResetPassword {
	public function getEmailForPasswordReset()
	{
		return $this->email;
	}
}
