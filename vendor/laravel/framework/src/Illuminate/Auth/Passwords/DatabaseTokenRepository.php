<?php namespace Illuminate\Auth\Passwords;
use Carbon\Carbon;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
class DatabaseTokenRepository implements TokenRepositoryInterface {
	protected $connection;
	protected $table;
	protected $hashKey;
	protected $expires;
	public function __construct(ConnectionInterface $connection, $table, $hashKey, $expires = 60)
	{
		$this->table = $table;
		$this->hashKey = $hashKey;
		$this->expires = $expires * 60;
		$this->connection = $connection;
	}
	public function create(CanResetPasswordContract $user)
	{
		$email = $user->getEmailForPasswordReset();
		$this->deleteExisting($user);
		$token = $this->createNewToken($user);
		$this->getTable()->insert($this->getPayload($email, $token));
		return $token;
	}
	protected function deleteExisting(CanResetPasswordContract $user)
	{
		return $this->getTable()->where('email', $user->getEmailForPasswordReset())->delete();
	}
	protected function getPayload($email, $token)
	{
		return ['email' => $email, 'token' => $token, 'created_at' => new Carbon];
	}
	public function exists(CanResetPasswordContract $user, $token)
	{
		$email = $user->getEmailForPasswordReset();
		$token = (array) $this->getTable()->where('email', $email)->where('token', $token)->first();
		return $token && ! $this->tokenExpired($token);
	}
	protected function tokenExpired($token)
	{
		$createdPlusHour = strtotime($token['created_at']) + $this->expires;
		return $createdPlusHour < $this->getCurrentTime();
	}
	protected function getCurrentTime()
	{
		return time();
	}
	public function delete($token)
	{
		$this->getTable()->where('token', $token)->delete();
	}
	public function deleteExpired()
	{
		$expired = Carbon::now()->subSeconds($this->expires);
		$this->getTable()->where('created_at', '<', $expired)->delete();
	}
	public function createNewToken(CanResetPasswordContract $user)
	{
		return hash_hmac('sha256', str_random(40), $this->hashKey);
	}
	protected function getTable()
	{
		return $this->connection->table($this->table);
	}
	public function getConnection()
	{
		return $this->connection;
	}
}
