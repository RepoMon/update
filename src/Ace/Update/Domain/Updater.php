<?php namespace Ace\Update\Domain;

use Ace\Update\Exception\FileNotFoundException;
use Github\Api\GitData\References;
use Github\Api\Repo;
use Github\Api\Search;
use Github\Client;
use Monolog\Logger;
use Exception;

/**
 * @author timrodger
 * Date: 23/11/15
 */
class Updater
{
    /**
     * @var Client
     */
    private $repository;

    /**
     * @var DependencyManagerInterface
     */
    private $dependency_manager;

    /**
     * @var FileSystem
     */
    private $file_system;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param GitHubRepository $repository
     * @param DependencyManagerInterface $dependency_manager
     * @param FileSystem $file_system
     * @param Logger $logger
     */
    public function __construct(
        GitHubRepository $repository,
        DependencyManagerInterface $dependency_manager,
        FileSystem $file_system,
        Logger $logger)
    {
        $this->repository = $repository;
        $this->dependency_manager = $dependency_manager;
        $this->file_system = $file_system;
        $this->logger = $logger;
    }

    /**
     * @param $from_branch
     * @param $to_branch
     * @return bool
     */
    public function run($from_branch, $to_branch)
    {
        $this->logger->info(__METHOD__);

        $config_file = $this->repository->findFileInfo($this->dependency_manager->getConfigFileName());
        if (is_null($config_file)){
            throw new FileNotFoundException("$config_file file not found");
        }

        // copy config file locally
        $config_contents = $this->repository->getFileContents($config_file['path'], $from_branch);
        $this->file_system->write($config_file['path'], $config_contents);


        $lock_file = $this->repository->findFileInfo($this->dependency_manager->getLockFileName());
        if (!is_null($lock_file)) {
            // copy lock file locally
            $lock_contents = $this->repository->getFileContents($lock_file['path'], $from_branch);
            $this->file_system->write($lock_file['path'], $lock_contents);
        }

        $new_contents = $this->dependency_manager->exec($this->file_system->getDirectory());

        if (!is_null($new_contents)) {

            $this->repository->createBranch($from_branch, $to_branch);

            // what if branch already exists? use it or stop here?
            $this->repository->writeFile($lock_file['path'], $lock_file['sha'], $new_contents, $to_branch);

            return true;

        } else {
            $this->logger->info('No changes made');
            return false;
        }
    }
}
