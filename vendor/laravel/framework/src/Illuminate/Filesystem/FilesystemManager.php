<?php namespace Illuminate\Filesystem;
use Closure;
use Aws\S3\S3Client;
use OpenCloud\Rackspace;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\Rackspace\RackspaceAdapter;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\AwsS3v2\AwsS3Adapter as S3Adapter;
use Illuminate\Contracts\Filesystem\Factory as FactoryContract;
class FilesystemManager implements FactoryContract {
	protected $app;
	protected $disks = [];
	protected $customCreators = [];
	public function __construct($app)
	{
		$this->app = $app;
	}
	public function drive($name = null)
	{
		return $this->disk($name);
	}
	public function disk($name = null)
	{
		$name = $name ?: $this->getDefaultDriver();
		return $this->disks[$name] = $this->get($name);
	}
	protected function get($name)
	{
		return isset($this->disks[$name]) ? $this->disks[$name] : $this->resolve($name);
	}
	protected function resolve($name)
	{
		$config = $this->getConfig($name);
		if (isset($this->customCreators[$config['driver']]))
		{
			return $this->callCustomCreator($config);
		}
		return $this->{"create".ucfirst($config['driver'])."Driver"}($config);
	}
	protected function callCustomCreator(array $config)
	{
		$driver = $this->customCreators[$config['driver']]($this->app, $config);
		if ($driver instanceof FilesystemInterface)
		{
			return $this->adapt($driver);
		}
		return $driver;
	}
	public function createLocalDriver(array $config)
	{
		return $this->adapt(new Flysystem(new LocalAdapter($config['root'])));
	}
	public function createS3Driver(array $config)
	{
		$s3Config = array_only($config, ['key', 'region', 'secret', 'signature', 'base_url']);
		return $this->adapt(
			new Flysystem(new S3Adapter(S3Client::factory($s3Config), $config['bucket']))
		);
	}
	public function createRackspaceDriver(array $config)
	{
		$client = new Rackspace($config['endpoint'], [
			'username' => $config['username'], 'apiKey' => $config['key'],
		]);
		return $this->adapt(new Flysystem(
			new RackspaceAdapter($this->getRackspaceContainer($client, $config))
		));
	}
	protected function getRackspaceContainer(Rackspace $client, array $config)
	{
		$urlType = array_get($config, 'url_type');
		$store = $client->objectStoreService('cloudFiles', $config['region'], $urlType);
		return $store->getContainer($config['container']);
	}
	protected function adapt(FilesystemInterface $filesystem)
	{
		return new FilesystemAdapter($filesystem);
	}
	protected function getConfig($name)
	{
		return $this->app['config']["filesystems.disks.{$name}"];
	}
	public function getDefaultDriver()
	{
		return $this->app['config']['filesystems.default'];
	}
	public function extend($driver, Closure $callback)
	{
		$this->customCreators[$driver] = $callback;
		return $this;
	}
	public function __call($method, $parameters)
	{
		return call_user_func_array(array($this->disk(), $method), $parameters);
	}
}
