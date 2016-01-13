<?php

use Silex\Application;

use Ace\Update\Provider\Config as ConfigProvider;
use Ace\Update\Provider\Log as LogProvider;
use Ace\Update\Provider\ErrorHandler as ErrorHandlerProvider;
use Ace\Update\Provider\UpdateCommandFactoryProvider;
use Ace\Update\Provider\QueueClientProvider;

require_once __DIR__ . '/vendor/autoload.php';

$app = new Application();

$dir = '/tmp/repositories';

$app->register(new ErrorHandlerProvider());
$app->register(new ConfigProvider($dir));
$app->register(new LogProvider());
$app->register(new UpdateCommandFactoryProvider());
$app->register(new QueueClientProvider());

return $app;
