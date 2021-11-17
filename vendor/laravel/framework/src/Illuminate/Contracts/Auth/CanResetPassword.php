<?php namespace Illuminate\Contracts\Auth;
interface CanResetPassword {
	public function getEmailForPasswordReset();
}
