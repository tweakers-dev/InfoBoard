<?php


namespace CilexPredis;

use Cilex\Application;
use Cilex\ServiceProviderInterface;

use Predis\Client,
    Predis\Option\ClientOptions,
    Predis\DispatcherLoop,
    Predis\Connection\ConnectionParameters;

class PredisExtension implements ServiceProviderInterface
{
    public function boot(Application $app)
    {

    }

    public function register(Application $app)
    {
        $app['predis'] = $app->share(function () use ($app) {
            $server = isset($app['predis.server']) ? $app['predis.server'] : array();
            $config = isset($app['predis.config']) ? $app['predis.config'] : array();

            return new Client(new ConnectionParameters($server), new ClientOptions($config));
        });
    }
}
