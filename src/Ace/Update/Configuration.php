<?php namespace Ace\Update;

/*
 * @author tim rodger
 * Date: 29/03/15
 */
class Configuration
{
    /**
     * @var string
     */
    private $repo_dir;

    /**
     * @param $repo_dir
     */
    public function __construct($repo_dir)
    {
        $this->repo_dir = $repo_dir;
    }

    public function getServiceName()
    {
        return 'Repository Update v4.0.0';
    }

    /**
     * @return string
     */
    public function getRepoDir()
    {
        return $this->repo_dir;
    }

    public function getStoreDsn()
    {
        // should contain a string like this 'tcp://172.17.0.154:6379'
        return getenv('REDIS_PORT');
    }

    /**
     * @return string
     */
    public function getDbUser()
    {
        return 'root';
    }

    /**
     * @return string
     */
    public function getDbPassword()
    {
        // env vars not available on publish?
        return '1234';
        //return getenv('MYSQL_ROOT_PASSWORD');
    }

    public function getDbHost()
    {
        return 'mysql';
    }

    public function getDbName()
    {
        return 'repositories';
    }

    /**
     * @return string
     */
    public function getRabbitHost()
    {
        return getenv('RABBITMQ_PORT_5672_TCP_ADDR');
    }

    /**
     * @return string
     */
    public function getRabbitPort()
    {
        return getenv('RABBITMQ_PORT_5672_TCP_PORT');
    }

    /**
     * @return string
     */
    public function getRabbitChannelName()
    {
        // use an env var for the channel name too
        return 'repo-mon.main';
    }

    /**
     * @return string
     */
    public function getTokenService()
    {
        return 'http://token';
    }
}
