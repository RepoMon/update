<?php

$app = require_once __DIR__ .'/app.php';
$app->boot();

$app['logger']->notice(sprintf("rabbit host: %s port: %s channel: %s\n",
    $app['config']->getRabbitHost(),
    $app['config']->getRabbitPort(),
    $app['config']->getRabbitChannelName()
    )
);

$updateHandler = function($command) use ($app) {

    $app['logger']->notice(print_r($command, 1));

    // update the repository specified in command
    $command = $app['update_command_factory']->create(
        $command['data']['url'],
        $command['data']['language'],
        $command['data']['dependency_manager'],
        $command['data']['token']
    );

    $command->execute([]);
};


$app['queue-client']->addEventHandler('command.repository.update', $updateHandler);

$app['queue-client']->consume();
