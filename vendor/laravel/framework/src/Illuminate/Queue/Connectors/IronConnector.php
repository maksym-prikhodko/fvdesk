<?php namespace Illuminate\Queue\Connectors;
use IronMQ;
use Illuminate\Http\Request;
use Illuminate\Queue\IronQueue;
use Illuminate\Contracts\Encryption\Encrypter as EncrypterContract;
class IronConnector implements ConnectorInterface {
	protected $crypt;
	protected $request;
	public function __construct(EncrypterContract $crypt, Request $request)
	{
		$this->crypt = $crypt;
		$this->request = $request;
	}
	public function connect(array $config)
	{
		$ironConfig = array('token' => $config['token'], 'project_id' => $config['project']);
		if (isset($config['host'])) $ironConfig['host'] = $config['host'];
		$iron = new IronMQ($ironConfig);
		if (isset($config['ssl_verifypeer']))
		{
			$iron->ssl_verifypeer = $config['ssl_verifypeer'];
		}
		return new IronQueue($iron, $this->request, $config['queue'], $config['encrypt']);
	}
}
