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

    // check we can handle this event
    if ('composer' === $command['data']['dependency_manager']) {

        $app['logger']->notice("Updating " . $command['data']['url']);

        // update the repository specified in command
        $updater = $app['update_command_factory']->create(
            $command['data']['full_name'],
            $command['data']['token'],
            $command['data']['branch']
        );

        $updater->run();

        $app['queue-client']->publish(
            [
                'name' => 'repo-mon.repository.updated',
                'data' => [
                    'full_name' => $command['data']['full_name']
                ],
                'version' => '1.0.0'
            ]
        );

    } else {
        $app['logger']->notice('Ignoring ' . $command['data']['url'] . ' with ' . $command['data']['dependency_manager']);
    }
};

$app['queue-client']->addEventHandler('command.repository.update', $updateHandler);

$app['queue-client']->consume();
