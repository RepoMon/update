<?php namespace Ace\Update\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Ace\Update\Domain\UpdaterFactory;

/**
 * @author timrodger
 * Date: 07/06/15
 */
class UpdaterFactoryProvider implements ServiceProviderInterface
{
    public function register(Application $app){}

    public function boot(Application $app)
    {
        $app['updater_factory'] = new UpdaterFactory(
            $app['config']->getRepoDir(),
            $app['logger'],
            $app['config']->getRemoteApiHost()
        );
    }

}
