<?php namespace Illuminate\Auth;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
class DatabaseUserProvider implements UserProvider {
	protected $conn;
	protected $hasher;
	protected $table;
	public function __construct(ConnectionInterface $conn, HasherContract $hasher, $table)
	{
		$this->conn = $conn;
		$this->table = $table;
		$this->hasher = $hasher;
	}
	public function retrieveById($identifier)
	{
		$user = $this->conn->table($this->table)->find($identifier);
		return $this->getGenericUser($user);
	}
	public function retrieveByToken($identifier, $token)
	{
		$user = $this->conn->table($this->table)
                                ->where('id', $identifier)
                                ->where('remember_token', $token)
                                ->first();
		return $this->getGenericUser($user);
	}
	public function updateRememberToken(UserContract $user, $token)
	{
		$this->conn->table($this->table)
                            ->where('id', $user->getAuthIdentifier())
                            ->update(['remember_token' => $token]);
	}
	public function retrieveByCredentials(array $credentials)
	{
		$query = $this->conn->table($this->table);
		foreach ($credentials as $key => $value)
		{
			if ( ! str_contains($key, 'password'))
			{
				$query->where($key, $value);
			}
		}
		$user = $query->first();
		return $this->getGenericUser($user);
	}
	protected function getGenericUser($user)
	{
		if ($user !== null)
		{
			return new GenericUser((array) $user);
		}
	}
	public function validateCredentials(UserContract $user, array $credentials)
	{
		$plain = $credentials['password'];
		return $this->hasher->check($plain, $user->getAuthPassword());
	}
}
