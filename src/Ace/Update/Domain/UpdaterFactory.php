<?php namespace Ace\Update\Domain;

use Github\Client;
use Monolog\Logger;
use Exception;

/**
 * @author timrodger
 * Date: 26/07/15
 */
class UpdaterFactory
{
    /**
     * @var string
     */
    private $repository_dir;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $git_hub_host;

    /**
     * @param $repository_dir
     * @param Logger $logger
     */
    public function __construct($repository_dir, Logger $logger, $git_hub_host)
    {
        $this->repository_dir = $repository_dir;
        $this->logger = $logger;
        $this->git_hub_host = $git_hub_host;
    }

    /**
     * @param $dependency_manager
     * @param $token
     * @return Updater
     */
    public function create($dependency_manager, $full_name, $token)
    {
        $this->logger->debug(__METHOD__);

        switch ($dependency_manager) {
            case 'composer':
                $manager = new ComposerDependencyManager();
                break;
            case 'npm':
                $manager = new NpmDependencyManager();
                break;
            default:
                throw new Exception("$dependency_manager is not supported");
                // throw exception
        }

        $client = new Client();

        $this->logger->debug(sprintf("Client::setEnterpriseUrl() %s", $this->git_hub_host));

        if (!empty($this->git_hub_host)) {
            // seems there's a bug here?
            //$client->setEnterpriseUrl($this->git_hub_host);
        }

        $git_hub_repo = new GitHubRepository($client, $full_name, $token, $this->logger);
        $git_hub_repo->authenticate();

        $temp_dir = $this->repository_dir . '/' . rand();
        $file_system = new FileSystem($temp_dir);
        $file_system->makeDirectory();

        return new Updater($git_hub_repo, $manager, $file_system, $this->logger);
    }
}
