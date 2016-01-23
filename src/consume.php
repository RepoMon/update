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

    try {
        $app['logger']->notice("Updating " . $command['data']['url']);

        // update the repository specified in command
        $updater = $app['updater_factory']->create(
            $command['data']['dependency_manager'],
            $command['data']['full_name'],
            $command['data']['token']
        );

        $app['logger']->notice(get_class($updater));

        $updater->run($command['data']['branch'], $app['config']->getTargetBranchName());

        $app['queue-client']->publish(
            [
                'name' => 'repo-mon.repository.updated',
                'data' => [
                    'full_name' => $command['data']['full_name']
                ],
                'version' => '1.0.0'
            ]
        );

    } catch (Exception $ex) {
        $app['logger']->error('Failed to update ' . $command['data']['url'] . ' with ' . $command['data']['dependency_manager'] . ' ' . $ex->getMessage());
    }

    // tidy up whatever's been created
    $updater->complete();
};

$app['queue-client']->addEventHandler('command.repository.update', $updateHandler);

$app['queue-client']->consume();
