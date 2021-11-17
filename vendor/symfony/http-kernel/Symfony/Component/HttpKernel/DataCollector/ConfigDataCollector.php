<?php
namespace Symfony\Component\HttpKernel\DataCollector;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class ConfigDataCollector extends DataCollector
{
    private $kernel;
    private $name;
    private $version;
    public function __construct($name = null, $version = null)
    {
        $this->name = $name;
        $this->version = $version;
    }
    public function setKernel(KernelInterface $kernel = null)
    {
        $this->kernel = $kernel;
    }
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = array(
            'app_name' => $this->name,
            'app_version' => $this->version,
            'token' => $response->headers->get('X-Debug-Token'),
            'symfony_version' => Kernel::VERSION,
            'name' => isset($this->kernel) ? $this->kernel->getName() : 'n/a',
            'env' => isset($this->kernel) ? $this->kernel->getEnvironment() : 'n/a',
            'debug' => isset($this->kernel) ? $this->kernel->isDebug() : 'n/a',
            'php_version' => PHP_VERSION,
            'xdebug_enabled' => extension_loaded('xdebug'),
            'eaccel_enabled' => extension_loaded('eaccelerator') && ini_get('eaccelerator.enable'),
            'apc_enabled' => extension_loaded('apc') && ini_get('apc.enabled'),
            'xcache_enabled' => extension_loaded('xcache') && ini_get('xcache.cacher'),
            'wincache_enabled' => extension_loaded('wincache') && ini_get('wincache.ocenabled'),
            'zend_opcache_enabled' => extension_loaded('Zend OPcache') && ini_get('opcache.enable'),
            'bundles' => array(),
            'sapi_name' => php_sapi_name(),
        );
        if (isset($this->kernel)) {
            foreach ($this->kernel->getBundles() as $name => $bundle) {
                $this->data['bundles'][$name] = $bundle->getPath();
            }
        }
    }
    public function getApplicationName()
    {
        return $this->data['app_name'];
    }
    public function getApplicationVersion()
    {
        return $this->data['app_version'];
    }
    public function getToken()
    {
        return $this->data['token'];
    }
    public function getSymfonyVersion()
    {
        return $this->data['symfony_version'];
    }
    public function getPhpVersion()
    {
        return $this->data['php_version'];
    }
    public function getAppName()
    {
        return $this->data['name'];
    }
    public function getEnv()
    {
        return $this->data['env'];
    }
    public function isDebug()
    {
        return $this->data['debug'];
    }
    public function hasXDebug()
    {
        return $this->data['xdebug_enabled'];
    }
    public function hasEAccelerator()
    {
        return $this->data['eaccel_enabled'];
    }
    public function hasApc()
    {
        return $this->data['apc_enabled'];
    }
    public function hasZendOpcache()
    {
        return $this->data['zend_opcache_enabled'];
    }
    public function hasXCache()
    {
        return $this->data['xcache_enabled'];
    }
    public function hasWinCache()
    {
        return $this->data['wincache_enabled'];
    }
    public function hasAccelerator()
    {
        return $this->hasApc() || $this->hasZendOpcache() || $this->hasEAccelerator() || $this->hasXCache() || $this->hasWinCache();
    }
    public function getBundles()
    {
        return $this->data['bundles'];
    }
    public function getSapiName()
    {
        return $this->data['sapi_name'];
    }
    public function getName()
    {
        return 'config';
    }
}
