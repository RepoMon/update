<?php namespace Ace\Update\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * @author timrodger
 * Date: 07/06/15
 */
class UpdateCommandFactoryProvider implements ServiceProviderInterface
{
    public function register(Application $app){}

    public function boot(Application $app)
    {
        $app['update_command_factory'] = new \Ace\Update\Command\UpdateCommandFactory(
            $app['config']->getRepoDir()
        );
    }

}
