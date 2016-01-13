<?php namespace Ace\Update\Provider;

use Ace\Update\Queue\QueueClientFactory;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * @author timrodger
 * Date: 23/06/15
 */
class QueueClientProvider implements ServiceProviderInterface
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {

    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
        $factory = new QueueClientFactory($app['config']);
        $app['queue-client'] = $factory->create();
    }
}
