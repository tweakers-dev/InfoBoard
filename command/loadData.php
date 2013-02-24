<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new \Cilex\Application('Cilex');

// Configuration
$app->register(new \Cilex\Provider\ConfigServiceProvider(), array('config.path' => __DIR__.'/../config/config.json'));

// Services
$app->register(new CilexPredis\PredisExtension(), array(
    'predis.class_path'    => __DIR__ . '/../vendor/predis/predis/lib',
    'predis.server'  => array(
        'host' => $app['config']->redis->host,
        'port' => $app['config']->redis->port
    ),
    'predis.config'  => array(
        'prefix' => 'predis__'
    )
));

// Commands
$app->command(new \Cilex\Command\GreetCommand());
$app->command(new \Henk\Command\ImportSvnCommand());
$app->command(new \Henk\Command\ImportJiraIssues());
$app->command(new \Henk\Command\ImportTwitterCommand());

$app->run();