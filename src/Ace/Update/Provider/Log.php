<?php namespace Ace\Update\Provider;

use Monolog\Handler\StreamHandler;
use Silex\Application;
use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;
use Silex\ServiceProviderInterface;

/**
 * @author timrodger
 * Date: 07/06/15
 */
class Log implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['logger'] = new Logger('log');
        $app['logger']->pushHandler(new ErrorLogHandler());
        $app['logger']->pushHandler(new StreamHandler('/tmp/consume.log'));
    }

    public function boot(Application $app)
    {

    }

}
