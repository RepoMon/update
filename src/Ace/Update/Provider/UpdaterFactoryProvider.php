<?php namespace Ace\Update\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Ace\Update\Command\UpdateCommandFactory;

/**
 * @author timrodger
 * Date: 07/06/15
 */
class UpdaterFactoryProvider implements ServiceProviderInterface
{
    public function register(Application $app){}

    public function boot(Application $app)
    {
        $app['update_command_factory'] = new UpdateCommandFactory(
            $app['config']->getRepoDir(),
            $app['logger']
        );
    }

}
