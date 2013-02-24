<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

// Services
$app->register(new SilexPredis\PredisExtension(), array(
    'predis.class_path'    => __DIR__ . '/../vendor/predis/predis/lib',
    'predis.server'  => array(
        'host' => '127.0.0.1',
        'port' => 6379
    ),
    'predis.config'  => array(
        'prefix' => 'predis__'
    )
));

// Controller resolving overwrite
$app['resolver'] = $app->share(function() use ($app) {
    return new \Henk\ControllerResolver($app, $app['logger']);
});

// Controllers
$app['display.controller'] = $app->share(function() use ($app) {
    return new \Henk\Web\DisplayController($app['predis']);
});

// Templating
$app->register(new \Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => array(__DIR__.'/../src/Henk/View')
));
$app['twig']->addExtension(new \Henk\View\LookupTwigExtension());

// Routing
$app->get('/', 'display.controller:listAction');

$app->run();