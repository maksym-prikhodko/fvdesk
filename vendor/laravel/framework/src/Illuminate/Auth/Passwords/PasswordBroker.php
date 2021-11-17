<?php namespace Illuminate\Auth\Passwords;
use Closure;
use UnexpectedValueException;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Contracts\Auth\PasswordBroker as PasswordBrokerContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
class PasswordBroker implements PasswordBrokerContract {
	protected $tokens;
	protected $users;
	protected $mailer;
	protected $emailView;
	protected $passwordValidator;
	public function __construct(TokenRepositoryInterface $tokens,
                                UserProvider $users,
                                MailerContract $mailer,
                                $emailView)
	{
		$this->users = $users;
		$this->mailer = $mailer;
		$this->tokens = $tokens;
		$this->emailView = $emailView;
	}
	public function sendResetLink(array $credentials, Closure $callback = null)
	{
		$user = $this->getUser($credentials);
		if (is_null($user))
		{
			return PasswordBrokerContract::INVALID_USER;
		}
		$token = $this->tokens->create($user);
		$this->emailResetLink($user, $token, $callback);
		return PasswordBrokerContract::RESET_LINK_SENT;
	}
	public function emailResetLink(CanResetPasswordContract $user, $token, Closure $callback = null)
	{
		$view = $this->emailView;
		return $this->mailer->send($view, compact('token', 'user'), function($m) use ($user, $token, $callback)
		{
			$m->to($user->getEmailForPasswordReset());
			if ( ! is_null($callback)) call_user_func($callback, $m, $user, $token);
		});
	}
	public function reset(array $credentials, Closure $callback)
	{
		$user = $this->validateReset($credentials);
		if ( ! $user instanceof CanResetPasswordContract)
		{
			return $user;
		}
		$pass = $credentials['password'];
		call_user_func($callback, $user, $pass);
		$this->tokens->delete($credentials['token']);
		return PasswordBrokerContract::PASSWORD_RESET;
	}
	protected function validateReset(array $credentials)
	{
		if (is_null($user = $this->getUser($credentials)))
		{
			return PasswordBrokerContract::INVALID_USER;
		}
		if ( ! $this->validateNewPassword($credentials))
		{
			return PasswordBrokerContract::INVALID_PASSWORD;
		}
		if ( ! $this->tokens->exists($user, $credentials['token']))
		{
			return PasswordBrokerContract::INVALID_TOKEN;
		}
		return $user;
	}
	public function validator(Closure $callback)
	{
		$this->passwordValidator = $callback;
	}
	public function validateNewPassword(array $credentials)
	{
		list($password, $confirm) = [
			$credentials['password'], $credentials['password_confirmation'],
		];
		if (isset($this->passwordValidator))
		{
			return call_user_func(
				$this->passwordValidator, $credentials) && $password === $confirm;
		}
		return $this->validatePasswordWithDefaults($credentials);
	}
	protected function validatePasswordWithDefaults(array $credentials)
	{
		list($password, $confirm) = [
			$credentials['password'], $credentials['password_confirmation'],
		];
		return $password === $confirm && mb_strlen($password) >= 6;
	}
	public function getUser(array $credentials)
	{
		$credentials = array_except($credentials, ['token']);
		$user = $this->users->retrieveByCredentials($credentials);
		if ($user && ! $user instanceof CanResetPasswordContract)
		{
			throw new UnexpectedValueException("User must implement CanResetPassword interface.");
		}
		return $user;
	}
	protected function getRepository()
	{
		return $this->tokens;
	}
}
