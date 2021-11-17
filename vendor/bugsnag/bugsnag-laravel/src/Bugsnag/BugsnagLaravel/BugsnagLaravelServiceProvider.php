<?php namespace Bugsnag\BugsnagLaravel;
use Illuminate\Support\ServiceProvider;
class BugsnagLaravelServiceProvider extends ServiceProvider
{
    protected $defer = false;
    public function boot()
    {
        $app = $this->app;
        if (version_compare($app::VERSION, '5.0') < 0) {
            $this->package('bugsnag/bugsnag-laravel', 'bugsnag');
            $app->error(function (\Exception $exception) use ($app) {
                if ('Symfony\Component\Debug\Exception\FatalErrorException'
                    !== get_class($exception)
                ) {
                    $app['bugsnag']->notifyException($exception, null, "error");
                }
            });
            $app->fatal(function ($exception) use ($app) {
                $app['bugsnag']->notifyException($exception, null, "error");
            });
        }
    }
    public function register()
    {
        $this->app->singleton('bugsnag', function ($app) {
            $config = $app['config']['bugsnag'] ?: $app['config']['bugsnag::config'];
            $client = new \Bugsnag_Client($config['api_key']);
            $client->setStripPath(base_path());
            $client->setProjectRoot(app_path());
            $client->setAutoNotify(false);
            $client->setBatchSending(false);
            $client->setReleaseStage($app->environment());
            $client->setNotifier(array(
                'name'    => 'Bugsnag Laravel',
                'version' => '1.4.2',
                'url'     => 'https:
            ));
            if (isset($config['notify_release_stages']) && is_array($config['notify_release_stages'])) {
                $client->setNotifyReleaseStages($config['notify_release_stages']);
            }
            if (isset($config['endpoint'])) {
                $client->setEndpoint($config['endpoint']);
            }
            if (isset($config['filters']) && is_array($config['filters'])) {
                $client->setFilters($config['filters']);
            }
            if (isset($config['proxy']) && is_array($config['proxy'])) {
                $client->setProxySettings($config['proxy']);
            }
            try {
                if ($app['auth']->check()) {
                    $user = $app['auth']->user();
                    $client->setUser(array('id' => $user->getAuthIdentifier()));
                }
            } catch (\Exception $e) {
            }
            return $client;
        });
    }
    public function provides()
    {
        return array("bugsnag");
    }
}
