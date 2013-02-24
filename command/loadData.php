<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new \Cilex\Application('Cilex');

$app->register(new CilexPredis\PredisExtension(), array(
    'predis.class_path'    => __DIR__ . '/../vendor/predis/predis/lib',
    'predis.server'  => array(
        'host' => '127.0.0.1',
        'port' => 6379
    ),
    'predis.config'  => array(
        'prefix' => 'predis__'
    )
));

$app->command(new \Cilex\Command\GreetCommand());
$app->command(new \Henk\Command\ImportSvnCommand());
$app->command(new \Henk\Command\ImportJiraIssues());
$app->command(new \Henk\Command\ImportTwitterCommand());

$app->run();